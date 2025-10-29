<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuatChieuController;
use App\Http\Controllers\GheController;
use App\Http\Controllers\PhongChieuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;


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

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', function () { return view('auth.register'); })->name('register.form');
    Route::get('/login', function () { return view('auth.login'); })->name('login.form');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ========================
// ✅ Nhóm Route Admin (admin + staff)
// ========================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Quản lý người dùng
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        // Quản lý thùng rác (soft delete)
        Route::get('/trash', [UserController::class, 'trash'])->name('trash');
        Route::post('/restore/{id}', [UserController::class, 'restore'])->name('restore');
    });

    // Admin can manage everything
    Route::resource('suat-chieu', SuatChieuController::class);
    Route::patch('suat-chieu/{suatChieu}/status', [SuatChieuController::class, 'updateStatus'])->name('suat-chieu.update-status');
    Route::post('suat-chieu/{suatChieu}/duplicate', [SuatChieuController::class, 'duplicate'])->name('suat-chieu.duplicate');
    Route::get('suat-chieu-by-movie-date', [SuatChieuController::class, 'getByMovieAndDate'])->name('suat-chieu.by-movie-date');

    // Room management (replaces seat management)
    Route::resource('phong-chieu', PhongChieuController::class);
    Route::patch('phong-chieu/{phongChieu}/status', [PhongChieuController::class, 'updateStatus'])->name('phong-chieu.update-status');
    Route::get('phong-chieu/{phongChieu}/seats', [PhongChieuController::class, 'getByRoom'])->name('phong-chieu.seats');
    Route::post('phong-chieu/{phongChieu}/generate-seats', [PhongChieuController::class, 'generateSeats'])->name('phong-chieu.generate-seats');
    Route::get('phong-chieu/{phongChieu}/manage-seats', [PhongChieuController::class, 'manageSeats'])->name('phong-chieu.manage-seats');
    Route::post('phong-chieu/{phongChieu}/seats', [PhongChieuController::class, 'storeSeat'])->name('phong-chieu.seats.store');
    Route::put('phong-chieu/{phongChieu}/seats/{ghe}', [PhongChieuController::class, 'updateSeat'])->name('phong-chieu.seats.update');
    Route::delete('phong-chieu/{phongChieu}/seats/{ghe}', [PhongChieuController::class, 'destroySeat'])->name('phong-chieu.seats.destroy');
    Route::patch('seats/{ghe}/status', [PhongChieuController::class, 'updateSeatStatus'])->name('seats.update-status');
    Route::patch('seats/{ghe}/type', [PhongChieuController::class, 'updateSeatType'])->name('seats.update-type');

    // Legacy seat routes (for backward compatibility)
    Route::resource('ghe', GheController::class);
    Route::patch('ghe/{ghe}/status', [GheController::class, 'updateStatus'])->name('ghe.update-status');
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->name('ghe.by-room');
    Route::post('ghe/generate', [GheController::class, 'generateSeats'])->name('ghe.generate');
});
 // Reports routes - only for admin
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
            Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('/top-movies', [ReportController::class, 'topMovies'])->name('top-movies');
            Route::get('/top-customers', [ReportController::class, 'topCustomers'])->name('top-customers');
        });
    });
    
    // Staff và Admin đều có thể xem danh sách và chi tiết khuyến mãi
    Route::middleware(['auth', 'role:admin,staff'])->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/khuyenmai', [AdminKhuyenMaiController::class, 'index'])->name('khuyenmai.index');
        Route::get('/khuyenmai/{khuyenmai}', [AdminKhuyenMaiController::class, 'show'])->name('khuyenmai.show');
    });

Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Staff can only view suat chieu (read-only)
    Route::get('suat-chieu', [SuatChieuController::class, 'index'])->name('suat-chieu.index');
    Route::get('suat-chieu/{suatChieu}', [SuatChieuController::class, 'show'])->name('suat-chieu.show');

    // Staff can view rooms
    Route::get('phong-chieu', [PhongChieuController::class, 'index'])->name('phong-chieu.index');
    Route::get('phong-chieu/{phongChieu}', [PhongChieuController::class, 'show'])->name('phong-chieu.show');
    Route::get('phong-chieu/{phongChieu}/seats', [PhongChieuController::class, 'getByRoom'])->name('phong-chieu.seats');

    // Legacy seat routes (for backward compatibility)
    Route::get('ghe', [GheController::class, 'index'])->name('ghe.index');
    Route::get('ghe/{ghe}', [GheController::class, 'show'])->name('ghe.show');
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->name('ghe.by-room');
});

// Test route to check current URL
use Illuminate\Support\Facades\Auth;
Route::get('/test-current-url', function () {
    $user = Auth::user();
    return response()->json([
        'current_url' => request()->url(),
        'current_path' => request()->path(),
        'is_staff' => request()->is('staff/*'),
        'is_admin' => request()->is('admin/*'),
        'route_name' => request()->route()->getName(),
        'user_role' => $user ? optional($user->vaiTro)->ten : 'Not authenticated'
    ]);
});
