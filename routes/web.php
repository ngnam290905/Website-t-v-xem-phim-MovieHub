<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;

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
        
        // Movie management routes
        Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
        Route::get('/movies/search', [MovieController::class, 'search'])->name('movies.search');
         Route::get('/movies/create', [MovieController::class, 'create'])->name('movies.create');
        Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
    });
    
    // Admin-only movie management routes
   
    Route::post('/movies', [MovieController::class, 'store'])->name('movies.store');
    Route::get('/movies/{movie}/edit', [MovieController::class, 'edit'])->name('movies.edit');
    Route::put('/movies/{movie}', [MovieController::class, 'update'])->name('movies.update');
    Route::delete('/movies/{movie}', [MovieController::class, 'destroy'])->name('movies.destroy');
    Route::patch('/movies/{movie}/toggle-status', [MovieController::class, 'toggleStatus'])->name('movies.toggle-status');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', function () { return view('auth.register'); })->name('register.form');
    Route::get('/login', function () { return view('auth.login'); })->name('login.form');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

