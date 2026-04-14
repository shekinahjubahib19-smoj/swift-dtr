<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $key = 'register|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many registration attempts. Please try again later.']);
        }

        // Honeypot (bots will fill this)
        if ($request->filled('website')) {
            RateLimiter::hit($key);
            return back()->withErrors(['email' => 'Invalid submission.']);
        }

        // Optional Cloudflare Turnstile verification if both sitekey and secret are configured
        if (env('TURNSTILE_SECRET') && env('TURNSTILE_SITEKEY')) {
            $token = $request->input('cf-turnstile-response');
            if (! $token) {
                RateLimiter::hit($key);
                return back()->withErrors(['captcha' => 'Captcha verification failed.']);
            }
            $resp = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => env('TURNSTILE_SECRET'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);
            $body = $resp->json();
            if (! ($body['success'] ?? false)) {
                RateLimiter::hit($key);
                return back()->withErrors(['captcha' => 'Captcha verification failed.']);
            }
        }

        // Build validation rules; only include phone rules if the column exists
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'method' => 'nullable|in:email,sms',
        ];
        $phoneColumnExists = Schema::hasColumn('users', 'phone');
        if ($phoneColumnExists) {
            // accept phone if column exists; allow flexible formats (will normalize)
            $rules['phone'] = 'nullable|string|max:20';
        }

        $request->validate($rules);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];
        if ($phoneColumnExists && $request->filled('phone')) {
            // normalize phone to digits with leading + if present
            $p = preg_replace('/[^0-9+]/', '', $request->phone);
            $userData['phone'] = $p;
        }
        // Allow duplicate phone numbers for SMS verification (do not enforce uniqueness here)

        try {
            DB::beginTransaction();

            // Try creating the user. If DB lacks `phone` column unexpectedly, retry without phone value.
            try {
                $user = User::create($userData);
            } catch (\PDOException $pdoEx) {
                // handle sqlite "no column named phone" or similar errors by removing phone and retrying
                $msg = $pdoEx->getMessage();
                if (stripos($msg, 'no column named phone') !== false || stripos($msg, 'has no column named phone') !== false) {
                    Log::warning('Phone column missing during registration; retrying without phone.');
                    unset($userData['phone']);
                    $user = User::create($userData);
                } else {
                    throw $pdoEx;
                }
            }

            // Create a verification token and send via email or SMS
            $method = ($request->input('method') === 'sms' && $phoneColumnExists && !empty($user->phone)) ? 'sms' : 'email';
            $otp = random_int(100000, 999999);
            DB::table('verification_tokens')->insert([
                'user_id' => $user->id,
                'token' => (string)$otp,
                'method' => $method,
                'expires_at' => now()->addMinutes(15),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            RateLimiter::clear($key);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Registration failed: '.$e->getMessage());
            RateLimiter::hit($key);
            return back()->withErrors(['email' => 'Registration failed. Please try again.']);
        }

        if ($method === 'email') {
            try {
                Mail::raw("Your verification code is: $otp", function($m) use ($user){
                    $m->to($user->email)->subject('Your verification code');
                });
            } catch (\Throwable $e) {
                Log::error('Email send failed: '.$e->getMessage());
            }
        } else {
            $to = $user->phone ?? null;
            if ($to) {
                $p = preg_replace('/[^0-9+]/', '', $to);
                if (strpos($p, '+') !== 0) {
                    if (strpos($p, '0') === 0) {
                        $p = '+63' . substr($p, 1);
                    } elseif (strlen($p) === 10 && strpos($p, '9') === 0) {
                        $p = '+63' . $p;
                    } else {
                        $p = '+' . $p;
                    }
                }

                $twSid = env('TWILIO_SID');
                $twToken = env('TWILIO_AUTH_TOKEN');
                $twFrom = env('TWILIO_FROM');

                if ($twSid && $twToken && $twFrom) {
                    try {
                        $resp = Http::asForm()->withBasicAuth($twSid, $twToken)
                            ->post("https://api.twilio.com/2010-04-01/Accounts/{$twSid}/Messages.json", [
                                'From' => $twFrom,
                                'To' => $p,
                                'Body' => "Your verification code is: {$otp}",
                            ]);
                        if (! $resp->successful()) {
                            Log::error('Twilio send failed: '.$resp->status().' '.$resp->body());
                            Log::info("OTP for {$p}: {$otp}");
                        }
                    } catch (\Throwable $e) {
                        Log::error('SMS send error: '.$e->getMessage());
                        Log::info("OTP for {$p}: {$otp}");
                    }
                } else {
                    Log::info("SMS OTP for {$p}: $otp (Twilio not configured)");
                }
            } else {
                Log::warning('No phone provided for SMS OTP; OTP: '.$otp);
            }
        }

        // Do not auto-login. Redirect user to the login page with instruction to verify after login.
        return redirect('/login')->with('success', 'Account created. A verification code was sent; please login and verify your account.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            // intended() is great—it sends them back to where they
            // were trying to go before being asked to login.
            return redirect()->intended('dtr-management');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showVerifyForm()
    {
        return view('auth.verify');
    }

    public function verify(Request $request)
    {
        $user = Auth::user();
        if (! $user) return redirect('/login');

        $request->validate([ 'code' => 'required|digits:6' ]);

        $record = DB::table('verification_tokens')
            ->where('user_id', $user->id)
            ->where('token', $request->code)
            ->where('expires_at', '>=', now())
            ->latest('created_at')
            ->first();

        if (! $record) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        // Re-fetch the user as an Eloquent model to ensure we can persist changes
        $userModel = User::find($user->id);
        if (! $userModel) {
            return redirect('/login');
        }

        if ($record->method === 'email') {
            $userModel->email_verified_at = now();
        } else {
            $userModel->phone_verified_at = now();
        }
        $userModel->save();

        // remove used tokens
        DB::table('verification_tokens')->where('id', $record->id)->delete();

        return redirect()->route('dtr.manage')->with('success', 'Account verified.');
    }

    public function resendVerification(Request $request)
    {
        $user = Auth::user();
        if (! $user) return redirect('/login');

        // find last token for user
        $last = DB::table('verification_tokens')->where('user_id', $user->id)->latest('created_at')->first();
        if ($last) {
            $lastCreated = Carbon::parse($last->created_at);
            if ($lastCreated->gt(now()->subMinutes(3))) {
                $wait = 3 - $lastCreated->diffInMinutes(now());
                return back()->withErrors(['resend' => 'Please wait a few minutes before requesting a new code.']);
            }
        }

        $method = ($last && $last->method === 'sms' && $user->phone) ? 'sms' : 'email';
        $otp = random_int(100000, 999999);
        DB::table('verification_tokens')->insert([
            'user_id' => $user->id,
            'token' => (string)$otp,
            'method' => $method,
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($method === 'email') {
            try {
                Mail::raw("Your verification code is: $otp", function($m) use ($user){
                    $m->to($user->email)->subject('Your verification code');
                });
            } catch (\Throwable $e) {
                Log::error('Email send failed: '.$e->getMessage());
            }
        } else {
            $to = $user->phone ?? null;
            if ($to) {
                $p = preg_replace('/[^0-9+]/', '', $to);
                if (strpos($p, '+') !== 0) {
                    if (strpos($p, '0') === 0) {
                        $p = '+63' . substr($p, 1);
                    } elseif (strlen($p) === 10 && strpos($p, '9') === 0) {
                        $p = '+63' . $p;
                    } else {
                        $p = '+' . $p;
                    }
                }

                $twSid = env('TWILIO_SID');
                $twToken = env('TWILIO_AUTH_TOKEN');
                $twFrom = env('TWILIO_FROM');

                if ($twSid && $twToken && $twFrom) {
                    try {
                        $resp = Http::asForm()->withBasicAuth($twSid, $twToken)
                            ->post("https://api.twilio.com/2010-04-01/Accounts/{$twSid}/Messages.json", [
                                'From' => $twFrom,
                                'To' => $p,
                                'Body' => "Your verification code is: {$otp}",
                            ]);
                        if (! $resp->successful()) {
                            Log::error('Twilio send failed: '.$resp->status().' '.$resp->body());
                            Log::info("OTP for {$p}: {$otp}");
                        }
                    } catch (\Throwable $e) {
                        Log::error('SMS send error: '.$e->getMessage());
                        Log::info("OTP for {$p}: {$otp}");
                    }
                } else {
                    Log::info("SMS OTP for {$p}: $otp (Twilio not configured)");
                }
            } else {
                Log::warning('No phone provided for SMS OTP; OTP: '.$otp);
            }
        }

        return back()->with('success', 'Verification code resent.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}