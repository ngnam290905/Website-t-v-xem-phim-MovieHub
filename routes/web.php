<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuanLyDatVeController;

//
// TRANG NGƯỜI DÙNG
//
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dat-ve/{id?}', function ($id = 1) {
    return view('booking', ['id' => $id]);
})->name('booking');

//
// XÁC THỰC NGƯỜI DÙNG
//
Route::middleware('guest')->group(function () {
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register.form');

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login.form');

    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


//
// KHU VỰC QUẢN TRỊ (Admin / Staff)
//
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard: cả admin và staff đều có thể vào
    Route::get('/', [AdminController::class, 'dashboard'])
        ->middleware('role:admin,staff')
        ->name('dashboard');

    // ========================
    // QUẢN LÝ ĐẶT VÉ
    // ========================
    Route::prefix('bookings')->name('bookings.')->group(function () {

        // Cả admin & staff đều được xem danh sách vé
        Route::get('/', [QuanLyDatVeController::class, 'index'])
            ->middleware('role:admin,staff')
            ->name('index');

        // Xem chi tiết vé (cả admin & staff)
        Route::get('/{id}', [QuanLyDatVeController::class, 'show'])
            ->middleware('role:admin,staff')
            ->name('show');

        // Chỉ admin được chỉnh sửa, cập nhật, hủy vé
        Route::middleware('role:admin')->group(function () {
            Route::get('/{id}/edit', [QuanLyDatVeController::class, 'edit'])->name('edit');
            Route::post('/{id}/update', [QuanLyDatVeController::class, 'update'])->name('update');
            Route::post('/{id}/cancel', [QuanLyDatVeController::class, 'cancel'])->name('cancel');
            Route::post('/{id}/confirm', [QuanLyDatVeController::class, 'confirm'])->name('confirm');
        });
    });
});
