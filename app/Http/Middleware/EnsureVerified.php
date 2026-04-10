<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && empty($user->email_verified_at) && empty($user->phone_verified_at)) {
            return redirect()->route('verify.form')->with('error', 'Please verify your account before continuing.');
        }

        return $next($request);
    }
}
