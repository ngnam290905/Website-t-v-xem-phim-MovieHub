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
        Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
    });
    
    // Admin-only movie management routes
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/movies/create', [MovieController::class, 'create'])->name('movies.create');
        Route::post('/movies', [MovieController::class, 'store'])->name('movies.store');
        Route::get('/movies/{movie}/edit', [MovieController::class, 'edit'])->name('movies.edit');
        Route::put('/movies/{movie}', [MovieController::class, 'update'])->name('movies.update');
        Route::delete('/movies/{movie}', [MovieController::class, 'destroy'])->name('movies.destroy');
        Route::patch('/movies/{movie}/toggle-status', [MovieController::class, 'toggleStatus'])->name('movies.toggle-status');
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

// Test route để debug middleware
Route::get('/test-middleware', function() {
    if (!auth()->check()) {
        return 'Chưa đăng nhập';
    }
    
    $user = auth()->user();
    $role = $user->vaiTro->ten ?? 'No role';
    
    return "User: {$user->ho_ten}, Role: {$role}, Can access create: " . (in_array($role, ['admin']) ? 'Yes' : 'No');
})->middleware(['auth', 'role:admin'])->name('test.middleware');

// Test route đơn giản không có middleware
Route::get('/test-simple', function() {
    return 'Test route hoạt động!';
})->name('test.simple');

// Test route cho create movie không có middleware
Route::get('/test-create', function() {
    return view('admin.movies.create');
})->name('test.create');

// Test route để đăng nhập admin
Route::get('/test-login', function() {
    $user = \App\Models\NguoiDung::where('email', 'admin@example.com')->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect()->route('admin.movies.index');
    }
    return 'Không tìm thấy tài khoản admin';
})->name('test.login');

// Test route để kiểm tra create không có middleware
Route::get('/test-create-direct', function() {
    return view('admin.movies.create');
})->name('test.create.direct');

// Test route để kiểm tra và sửa admin user
Route::get('/fix-admin', function() {
    $user = \App\Models\NguoiDung::where('email', 'admin@example.com')->first();
    if ($user) {
        $adminRole = \App\Models\VaiTro::where('ten', 'admin')->first();
        if ($adminRole) {
            $user->id_vai_tro = $adminRole->id;
            $user->save();
            return "Đã cập nhật role admin cho user: " . $user->ho_ten;
        } else {
            return "Không tìm thấy role admin";
        }
    } else {
        return "Không tìm thấy user admin@example.com";
    }
})->name('fix.admin');

// Test route để kiểm tra user hiện tại
Route::get('/check-user', function() {
    if (auth()->check()) {
        $user = auth()->user();
        return "User: " . $user->ho_ten . " - Email: " . $user->email . " - Role: " . ($user->vaiTro ? $user->vaiTro->ten : 'No role');
    } else {
        return "Chưa đăng nhập";
    }
})->name('check.user');

// Route hoàn toàn mới để truy cập create form (không có middleware)
Route::get('/add-movie', function() {
    return view('admin.movies.create');
})->name('add.movie');

// Route hoàn toàn mới để store movie (không có middleware)
Route::post('/save-movie', [MovieController::class, 'store'])->name('save.movie');
