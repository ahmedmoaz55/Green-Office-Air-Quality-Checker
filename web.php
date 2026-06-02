<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\AuthController;

// --- PUBLIC AUTH ROUTES (Only visible to guests) ---
Route::middleware('guest')->group(function () {
    // Show the views
    Route::get('/login', function () { return view('login'); })->name('login');
    Route::get('/register', function () { return view('register'); })->name('register');
    
    // Handle the form submissions
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// --- PROTECTED ROUTES (Only visible to logged-in users) ---
Route::middleware('auth')->group(function () {
    // Main Dashboard
    Route::get('/', [SensorController::class, 'index']);
    
    // The API route for your simulator (It's better to keep this unprotected for your simple JS simulator script to hit it easily during the demo)
    Route::post('/sensor-data', [SensorController::class, 'store'])->withoutMiddleware('auth');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});