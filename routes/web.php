<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dat-ve/{id?}', function ($id = 1) {
    return view('booking', ['id' => $id]);
})->name('booking');

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['auth', 'role:admin,staff'])->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    });
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', function () { return view('auth.register'); })->name('register.form');
    Route::get('/login', function () { return view('auth.login'); })->name('login.form');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
