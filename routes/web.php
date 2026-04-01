<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DtrController;

// --- Public Routes ---
Route::get('/', function () {
    return view('welcome');
})->name('landing');

// Guest only routes (Login/Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () { return view('auth.login'); })->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', function () { return view('auth.register'); })->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// --- Protected Routes (Must be Logged In) ---
Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

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

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});