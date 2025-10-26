<?php

use App\Http\Controllers\AdminKhuyenMaiController;
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
    Route::middleware(['auth', 'role:admin'])->group(function () {
        // Admin có toàn quyền: tạo, sửa, xóa khuyến mãi
        Route::resource('khuyenmai', AdminKhuyenMaiController::class)->except(['index', 'show']);
    });
    
    // Staff và Admin đều có thể xem danh sách và chi tiết khuyến mãi
    Route::middleware(['auth', 'role:admin,staff'])->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/khuyenmai', [AdminKhuyenMaiController::class, 'index'])->name('khuyenmai.index');
        // Route::get('/khuyenmai/{khuyenmai}', [AdminKhuyenMaiController::class, 'show'])->name('khuyenmai.show')
        Route::get('/khuyenmai/{khuyenmai}', [AdminKhuyenMaiController::class, 'show'])->name('khuyenmai.show');
        ;
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
