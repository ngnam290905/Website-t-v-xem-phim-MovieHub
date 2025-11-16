<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuatChieuController;
use App\Http\Controllers\GheController;
use App\Http\Controllers\PhongChieuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminKhuyenMaiController;
use App\Http\Controllers\QuanLyDatVeController;
use App\Http\Controllers\ComboController;
use App\Http\Controllers\HomeController;


// Main routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Client Movie Routes
Route::prefix('phim')->name('movies.')->group(function () {
    // Static routes first
    Route::get('/', [MovieController::class, 'list'])->name('index');
    Route::get('/dang-chieu', [MovieController::class, 'nowShowing'])->name('now-showing');
    Route::get('/sap-chieu', [MovieController::class, 'comingSoon'])->name('coming-soon');
    Route::get('/phim-hot', [MovieController::class, 'hotMovies'])->name('hot');
    Route::get('/gio-chieu', [MovieController::class, 'showtimes'])->name('showtimes');
    
    // Dynamic routes with parameters should come after static routes
    Route::get('/the-loai/{genre}', [MovieController::class, 'byGenre'])->name('by-genre');
    
    // Catch-all route for movie details should be last
    Route::get('/{movie}', [MovieController::class, 'show'])->name('show');
});

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


// Admin routes - cả Admin và Staff
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Quản lý phim (Admin & Staff view-only except staff may be restricted by policy)
    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', [MovieController::class, 'adminIndex'])->name('index');
        Route::get('/search', [MovieController::class, 'adminIndex'])->name('search');
        Route::get('/create', [MovieController::class, 'create'])->middleware('role:admin')->name('create');
        Route::post('/', [MovieController::class, 'store'])->middleware('role:admin')->name('store');
        Route::get('/{movie}', [MovieController::class, 'show'])->name('show');
        Route::get('/{movie}/edit', [MovieController::class, 'edit'])->middleware('role:admin')->name('edit');
        Route::put('/{movie}', [MovieController::class, 'update'])->middleware('role:admin')->name('update');
        Route::delete('/{movie}', [MovieController::class, 'destroy'])->middleware('role:admin')->name('destroy');
        Route::patch('/{movie}/toggle-status', [MovieController::class, 'toggleStatus'])->middleware('role:admin')->name('toggle-status');
    });

    // Quản lý người dùng
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        // Show đặt TRƯỚC các route có {id}/edit để tránh nuốt 'create'
        Route::get('/{id}', [UserController::class, 'show'])->whereNumber('id')->name('show');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('/trash', [UserController::class, 'trash'])->name('trash');
        Route::post('/restore/{id}', [UserController::class, 'restore'])->name('restore');
    });

    // Quản lý suất chiếu
    // Chỉ Admin: CRUD & thao tác thay đổi trạng thái/nhân bản (đặt trước show để tránh nuốt '/create')
    Route::middleware('role:admin')->group(function () {
        Route::get('suat-chieu/auto', [SuatChieuController::class, 'auto'])->name('suat-chieu.auto');
        Route::get('suat-chieu/create', [SuatChieuController::class, 'create'])->name('suat-chieu.create');
        Route::post('suat-chieu', [SuatChieuController::class, 'store'])->name('suat-chieu.store');
        Route::get('suat-chieu/{suatChieu}/edit', [SuatChieuController::class, 'edit'])->name('suat-chieu.edit');
        Route::put('suat-chieu/{suatChieu}', [SuatChieuController::class, 'update'])->name('suat-chieu.update');
        Route::delete('suat-chieu/{suatChieu}', [SuatChieuController::class, 'destroy'])->name('suat-chieu.destroy');
        Route::patch('suat-chieu/{suatChieu}/status', [SuatChieuController::class, 'updateStatus'])->name('suat-chieu.update-status');
        Route::post('suat-chieu/{suatChieu}/duplicate', [SuatChieuController::class, 'duplicate'])->name('suat-chieu.duplicate');
    });
    // Staff & Admin: chỉ xem danh sách/chi tiết
    Route::resource('suat-chieu', SuatChieuController::class)->only(['index', 'show']);
    Route::get('suat-chieu-by-movie-date', [SuatChieuController::class, 'getByMovieAndDate'])->name('suat-chieu.by-movie-date');

    // Quản lý phòng chiếu
    // Staff & Admin: chỉ xem danh sách
    Route::get('phong-chieu', [PhongChieuController::class, 'index'])->name('phong-chieu.index');
    Route::get('phong-chieu/{phongChieu}/seats', [PhongChieuController::class, 'getByRoom'])->name('phong-chieu.seats');
    // Chỉ Admin: CRUD & seat management (đặt trước show để tránh nuốt '/create')
    Route::middleware('role:admin')->group(function () {
        Route::get('phong-chieu/create', [PhongChieuController::class, 'create'])->name('phong-chieu.create');
        Route::post('phong-chieu', [PhongChieuController::class, 'store'])->name('phong-chieu.store');
        Route::get('phong-chieu/{phongChieu}/edit', [PhongChieuController::class, 'edit'])->name('phong-chieu.edit');
        Route::put('phong-chieu/{phongChieu}', [PhongChieuController::class, 'update'])->name('phong-chieu.update');
        Route::delete('phong-chieu/{phongChieu}', [PhongChieuController::class, 'destroy'])->name('phong-chieu.destroy');
        Route::patch('phong-chieu/{phongChieu}/status', [PhongChieuController::class, 'updateStatus'])->name('phong-chieu.update-status');
        Route::get('phong-chieu/{phongChieu}/can-modify', [PhongChieuController::class, 'canModify'])->name('phong-chieu.can-modify');
        Route::post('phong-chieu/{phongChieu}/generate-seats', [PhongChieuController::class, 'generateSeats'])->name('phong-chieu.generate-seats');
        Route::get('phong-chieu/{phongChieu}/manage-seats', [PhongChieuController::class, 'manageSeats'])->name('phong-chieu.manage-seats');
        Route::post('phong-chieu/{phongChieu}/seats', [PhongChieuController::class, 'storeSeat'])->name('phong-chieu.seats.store');
        Route::put('phong-chieu/{phongChieu}/seats/{ghe}', [PhongChieuController::class, 'updateSeat'])->name('phong-chieu.seats.update');
        Route::delete('phong-chieu/{phongChieu}/seats/{ghe}', [PhongChieuController::class, 'destroySeat'])->name('phong-chieu.seats.destroy');
        Route::patch('seats/{ghe}/status', [PhongChieuController::class, 'updateSeatStatus'])->name('seats.update-status');
        Route::patch('seats/{ghe}/type', [PhongChieuController::class, 'updateSeatType'])->name('seats.update-type');
        Route::post('phong-chieu/{phongChieu}/seats/bulk', [PhongChieuController::class, 'bulkSeats'])->name('phong-chieu.seats.bulk');
    });
    // Staff & Admin: chi tiết (ràng buộc là số để tránh nuốt '/create')
    Route::get('phong-chieu/{phongChieu}', [PhongChieuController::class, 'show'])->whereNumber('phongChieu')->name('phong-chieu.show');

    // Quản lý ghế (legacy)
    // Staff & Admin: chỉ xem danh sách
    Route::get('ghe', [GheController::class, 'index'])->name('ghe.index');
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->name('ghe.by-room');
    // Chỉ Admin: CRUD & thao tác
    Route::middleware('role:admin')->group(function () {
        Route::get('ghe/create', [GheController::class, 'create'])->name('ghe.create');
        Route::post('ghe', [GheController::class, 'store'])->name('ghe.store');
        Route::get('ghe/{ghe}/edit', [GheController::class, 'edit'])->name('ghe.edit');
        Route::put('ghe/{ghe}', [GheController::class, 'update'])->name('ghe.update');
        Route::delete('ghe/{ghe}', [GheController::class, 'destroy'])->name('ghe.destroy');
        Route::patch('ghe/{ghe}/status', [GheController::class, 'updateStatus'])->name('ghe.update-status');
        Route::post('ghe/generate', [GheController::class, 'generateSeats'])->name('ghe.generate');
        Route::post('ghe/bulk', [GheController::class, 'bulk'])->name('ghe.bulk');
    });
    // Staff & Admin: chi tiết (ràng buộc số để không nuốt '/create')
    Route::get('ghe/{ghe}', [GheController::class, 'show'])->whereNumber('ghe')->name('ghe.show');

    // Quản lý đặt vé
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [QuanLyDatVeController::class, 'index'])->name('index');
        Route::get('/{id}', [QuanLyDatVeController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [QuanLyDatVeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [QuanLyDatVeController::class, 'update'])->name('update');
        Route::post('/{id}/cancel', [QuanLyDatVeController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/confirm', [QuanLyDatVeController::class, 'confirm'])->name('confirm');

        // API cho UI chỉnh sửa vé
        Route::get('/{id}/available-showtimes', [QuanLyDatVeController::class, 'availableShowtimes'])->name('available-showtimes');
    });

    // API lấy bản đồ ghế theo suất chiếu
    Route::get('showtimes/{suatChieu}/seats', [QuanLyDatVeController::class, 'seatsByShowtime'])->name('showtimes.seats');

    // KHUYẾN MẠI
    // Đặt nhóm Admin (tạo/sửa/xóa) TRƯỚC để tránh 'show' nuốt '/create'
    Route::middleware('role:admin')->group(function () {
        Route::get('/khuyenmai/create', [AdminKhuyenMaiController::class, 'create'])->name('khuyenmai.create');
        Route::post('/khuyenmai', [AdminKhuyenMaiController::class, 'store'])->name('khuyenmai.store');
        Route::get('/khuyenmai/{khuyenmai}/edit', [AdminKhuyenMaiController::class, 'edit'])->name('khuyenmai.edit');
        Route::put('/khuyenmai/{khuyenmai}', [AdminKhuyenMaiController::class, 'update'])->name('khuyenmai.update');
        Route::delete('/khuyenmai/{khuyenmai}', [AdminKhuyenMaiController::class, 'destroy'])->name('khuyenmai.destroy');
        Route::post('/khuyenmai/seed-tiers', [AdminKhuyenMaiController::class, 'seedTiers'])->name('khuyenmai.seed-tiers');
    });

    // CẢ ADMIN & STAFF ĐƯỢC XEM
    Route::get('/khuyenmai', [AdminKhuyenMaiController::class, 'index'])->name('khuyenmai.index');
    Route::get('/khuyenmai/{khuyenmai}', [AdminKhuyenMaiController::class, 'show'])
        ->whereNumber('khuyenmai')
        ->name('khuyenmai.show');

    // Quản lý Combo
    // Admin & Staff: xem danh sách
    Route::get('combos', [ComboController::class, 'index'])->name('combos.index');
    // Chỉ Admin: CRUD (đặt trước show để tránh nuốt '/create')
    Route::middleware('role:admin')->group(function () {
        Route::get('combos/create', [ComboController::class, 'create'])->name('combos.create');
        Route::post('combos', [ComboController::class, 'store'])->name('combos.store');
        Route::get('combos/{combo}/edit', [ComboController::class, 'edit'])->name('combos.edit');
        Route::put('combos/{combo}', [ComboController::class, 'update'])->name('combos.update');
        Route::delete('combos/{combo}', [ComboController::class, 'destroy'])->name('combos.destroy');
    });
    // Admin & Staff: chi tiết (ràng buộc là số để tránh nuốt '/create')
    Route::get('combos/{combo}', [ComboController::class, 'show'])->whereNumber('combo')->name('combos.show');
});

// BÁO CÁO - CHỈ ADMIN
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/top-movies', [ReportController::class, 'topMovies'])->name('top-movies');
        Route::get('/top-customers', [ReportController::class, 'topCustomers'])->name('top-customers');
    });
});

// ==================== STAFF ROUTES (chỉ xem) ====================
Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('suat-chieu', [SuatChieuController::class, 'index'])->name('suat-chieu.index');
    Route::get('suat-chieu/{suatChieu}', [SuatChieuController::class, 'show'])->name('suat-chieu.show');

    Route::get('phong-chieu', [PhongChieuController::class, 'index'])->name('phong-chieu.index');
    Route::get('phong-chieu/{phongChieu}', [PhongChieuController::class, 'show'])->name('phong-chieu.show');
    Route::get('phong-chieu/{phongChieu}/seats', [PhongChieuController::class, 'getByRoom'])->name('phong-chieu.seats');

    Route::get('ghe', [GheController::class, 'index'])->name('ghe.index');
    Route::get('ghe/{ghe}', [GheController::class, 'show'])->name('ghe.show');
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->name('ghe.by-room');
});

// Test route
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
