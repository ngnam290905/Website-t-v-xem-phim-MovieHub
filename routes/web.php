<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\SuatChieuController;
use App\Http\Controllers\GheController;

use App\Http\Controllers\AuthController;


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

// Admin routes - Only admin can access
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Admin can manage everything
    Route::resource('suat-chieu', SuatChieuController::class);
    Route::patch('suat-chieu/{suatChieu}/status', [SuatChieuController::class, 'updateStatus'])->name('suat-chieu.update-status');
    Route::get('suat-chieu-by-movie-date', [SuatChieuController::class, 'getByMovieAndDate'])->name('suat-chieu.by-movie-date');
    
    Route::resource('ghe', GheController::class);
    Route::patch('ghe/{ghe}/status', [GheController::class, 'updateStatus'])->name('ghe.update-status');
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->name('ghe.by-room');
    Route::post('ghe/generate', [GheController::class, 'generateSeats'])->name('ghe.generate');
});

// Staff routes - Staff can only view suat chieu and ghe
Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Staff can only view (index, show) suat chieu
    Route::get('suat-chieu', [SuatChieuController::class, 'index'])->name('suat-chieu.index');
    Route::get('suat-chieu/{suatChieu}', [SuatChieuController::class, 'show'])->name('suat-chieu.show');
    Route::get('suat-chieu-by-movie-date', [SuatChieuController::class, 'getByMovieAndDate'])->name('suat-chieu.by-movie-date');
    
    // Staff can only view (index, show) ghe
    Route::get('ghe', [GheController::class, 'index'])->name('ghe.index');
    Route::get('ghe/{ghe}', [GheController::class, 'show'])->name('ghe.show');
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->name('ghe.by-room');
});

// Test route to check current URL
Route::get('/test-current-url', function () {
    return response()->json([
        'current_url' => request()->url(),
        'current_path' => request()->path(),
        'is_staff' => request()->is('staff/*'),
        'is_admin' => request()->is('admin/*'),
        'route_name' => request()->route()->getName(),
        'user_role' => auth()->check() ? optional(auth()->user()->vaiTro)->ten : 'Not authenticated'
    ]);
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', function () { return view('auth.register'); })->name('register.form');
    Route::get('/login', function () { return view('auth.login'); })->name('login.form');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
