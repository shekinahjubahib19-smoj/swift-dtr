<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DtrController;

// --- Public Routes ---
Route::get('/', function () {
    return view('welcome');
})->name('landing');

// Public signup route (always shows registration form even if authenticated)
Route::get('/signup', function () { return view('auth.register'); })->name('signup');

// Guest only routes (Login/Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () { return view('auth.login'); })->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', function () { return view('auth.register'); })->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Routes for authenticated users (verification allowed)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Verification routes (accessible while unverified)
    Route::get('/verify', [AuthController::class, 'showVerifyForm'])->name('verify.form');
    Route::post('/verify', [AuthController::class, 'verify'])->name('verify.submit');
    Route::post('/verify/resend', [AuthController::class, 'resendVerification'])->name('verify.resend');
});

// --- Protected Routes (Must be Logged In AND Verified) ---
Route::middleware(['auth', \App\Http\Middleware\EnsureVerified::class])->group(function () {
    
    // Keep the route name `dashboard` but change URL path to `/punchTime`
    // Dashboard page (app landing)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Punch Time page (time clock)
    Route::get('/punchTime', function () {
        return view('punchTime');
    })->name('punch.time');

    // 1. DTR Record View
    Route::get('/dtr-record', function () {
        return view('dtr_record');
    })->name('dtr.record');

    // 2. Clock In/Out Action (FIXES THE ERROR)
    Route::post('/dtr-clock', [DtrController::class, 'clockAction'])->name('dtr.clock');

    Route::get('/dtr-management', function () {
        return view('dtr_management');
    })->name('dtr.manage');

    // Handles the "Save Configurations" button
    Route::post('/dtr-setup', [DtrController::class, 'storeSettings'])->name('dtr.setup');
    // Save monthly totals
    Route::post('/dtr/save-monthly-total', [DtrController::class, 'saveMonthlyTotal'])->name('dtr.saveMonthlyTotal');

    // DTR schedule preview
    Route::get('/dtr-schedule', [DtrController::class, 'schedule'])->name('dtr.schedule');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // (verification routes are available to authenticated-but-unverified users)
});