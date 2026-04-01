<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DtrController; // <-- 1. Add this import
/*
|--------------------------------------------------------------------------
| Public Routes (Anyone can see these)
|--------------------------------------------------------------------------
*/

// 1. The Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('landing');

// 2. The Login Page (Viewing the form)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// This HANDLES the login data (Add this now!)
Route::post('/login', [AuthController::class, 'login']);

// 3. The Registration Page (Viewing the form)
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

/*
|--------------------------------------------------------------------------
| Action Routes (These handle the Form Submissions)
|--------------------------------------------------------------------------
*/

// Handle Register Submission
Route::post('/register', [AuthController::class, 'register']);

// Handle Login Submission (We will build the 'login' method next)
// Route::post('/login', [AuthController::class, 'login']);

// Handle Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes (Must be Logged In)
|--------------------------------------------------------------------------
*/

// The Dashboard (Only accessible if authenticated)
Route::middleware('auth')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/dtr-management', function () {
        return view('dtr_management');
    })->name('dtr.manage');

    // 2. Add this route to handle the Save button
    Route::post('/dtr-setup', [DtrController::class, 'storeSettings'])->name('dtr.setup');
});

