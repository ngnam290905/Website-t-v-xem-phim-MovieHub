<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuatChieuController;
use App\Http\Controllers\GheController;

// Main routes
Route::get('/', [MovieController::class, 'index'])->name('home');
Route::get('/phim/{id}', [MovieController::class, 'show'])->name('movie-detail');

// API routes for AJAX calls
Route::get('/api/movies', [MovieController::class, 'getMovies'])->name('api.movies');
Route::get('/api/featured-movies', [MovieController::class, 'getFeaturedMovies'])->name('api.featured-movies');
Route::get('/api/search', [MovieController::class, 'search'])->name('api.search');
Route::get('/api/suat-chieu/{movieId}', [MovieController::class, 'getSuatChieu'])->name('api.suat-chieu');
Route::get('/api/phong-chieu', [MovieController::class, 'getPhongChieu'])->name('api.phong-chieu');

// Booking routes
Route::get('/dat-ve/{id?}', function ($id = 1) {
    return view('booking', ['id' => $id]);
})->name('booking');

Route::get('/dat-ve-dong/{id?}', function ($id = 1) {
    return view('booking-dynamic', ['id' => $id]);
})->name('booking-dynamic');

// Mini game route
Route::get('/mini-game', function () {
    return view('mini-game');
})->name('mini-game');

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Suat chieu management
    Route::resource('suat-chieu', SuatChieuController::class);
    Route::patch('suat-chieu/{suatChieu}/status', [SuatChieuController::class, 'updateStatus'])->name('suat-chieu.update-status');
    Route::get('suat-chieu-by-movie-date', [SuatChieuController::class, 'getByMovieAndDate'])->name('suat-chieu.by-movie-date');
    
    // Ghe management
    Route::resource('ghe', GheController::class);
    Route::patch('ghe/{ghe}/status', [GheController::class, 'updateStatus'])->name('ghe.update-status');
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->name('ghe.by-room');
    Route::post('ghe/generate', [GheController::class, 'generateSeats'])->name('ghe.generate');
});
