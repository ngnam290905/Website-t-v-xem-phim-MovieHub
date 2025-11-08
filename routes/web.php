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

use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ThanhVienController;



// Main routes
Route::get('/', [MovieController::class, 'index'])->name('home');
Route::get('/phim/{movie}', [MovieController::class, 'show'])->name('movie-detail');

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


// User profile routes
Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile');
    Route::get('/edit-profile', [UserProfileController::class, 'edit'])->name('edit-profile');
    Route::put('/update-profile', [UserProfileController::class, 'update'])->name('update-profile');
    Route::get('/change-password', [UserProfileController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [UserProfileController::class, 'changePassword'])->name('change-password');
    Route::get('/booking-history', [UserProfileController::class, 'bookingHistory'])->name('booking-history');
    Route::post('/cancel-booking/{id}', [UserProfileController::class, 'cancelBooking'])->name('cancel-booking');
});

// Thành viên routes
Route::middleware('auth')->prefix('thanh-vien')->name('thanh-vien.')->group(function () {
    Route::get('/dang-ky', [ThanhVienController::class, 'showRegistrationForm'])->name('register-form');
    Route::post('/dang-ky', [ThanhVienController::class, 'register'])->name('register');
    Route::get('/thong-tin', [ThanhVienController::class, 'profile'])->name('profile');
});


// Admin routes - chỉ Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Quản lý phim (Admin & Staff view-only except staff may be restricted by policy)
    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', [MovieController::class, 'adminIndex'])->name('index');
        Route::get('/search', [MovieController::class, 'adminIndex'])->name('search');
        Route::get('/create', [MovieController::class, 'create'])->name('create');
        Route::post('/', [MovieController::class, 'store'])->name('store');
        Route::get('/{movie}', [MovieController::class, 'show'])->name('show');
        Route::get('/{movie}/edit', [MovieController::class, 'edit'])->name('edit');
        Route::put('/{movie}', [MovieController::class, 'update'])->name('update');
        Route::delete('/{movie}', [MovieController::class, 'destroy'])->name('destroy');
        Route::patch('/{movie}/toggle-status', [MovieController::class, 'toggleStatus'])->name('toggle-status');
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
    // Admin: CRUD & thao tác thay đổi trạng thái/nhân bản (đặt trước show để tránh nuốt '/create')
    Route::middleware('role:admin')->group(function () {
        Route::get('suat-chieu/auto', [SuatChieuController::class, 'auto'])->name('suat-chieu.auto');
        Route::get('suat-chieu/create', [SuatChieuController::class, 'create'])->name('suat-chieu.create');
        Route::post('suat-chieu', [SuatChieuController::class, 'store'])->name('suat-chieu.store');
        Route::post('suat-chieu/batch', [SuatChieuController::class, 'batchStore'])->name('suat-chieu.batch-store');
        Route::get('suat-chieu/{suatChieu}/edit', [SuatChieuController::class, 'edit'])->name('suat-chieu.edit');
        Route::put('suat-chieu/{suatChieu}', [SuatChieuController::class, 'update'])->name('suat-chieu.update');
        Route::delete('suat-chieu/{suatChieu}', [SuatChieuController::class, 'destroy'])->name('suat-chieu.destroy');
        Route::patch('suat-chieu/{suatChieu}/status', [SuatChieuController::class, 'updateStatus'])->name('suat-chieu.update-status');
        Route::post('suat-chieu/{suatChieu}/duplicate', [SuatChieuController::class, 'duplicate'])->name('suat-chieu.duplicate');
    });
    // Admin: xem danh sách/chi tiết
    Route::resource('suat-chieu', SuatChieuController::class)->only(['index', 'show']);
    Route::get('suat-chieu-by-movie-date', [SuatChieuController::class, 'getByMovieAndDate'])->name('suat-chieu.by-movie-date');

    // Quản lý phòng chiếu
    // Admin: xem danh sách
    Route::get('phong-chieu', [PhongChieuController::class, 'index'])->name('phong-chieu.index');
    Route::get('phong-chieu/{phongChieu}/seats', [PhongChieuController::class, 'getByRoom'])->name('phong-chieu.seats');
    // Admin: CRUD & seat management (đặt trước show để tránh nuốt '/create')
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
    // Admin: chi tiết (ràng buộc là số để tránh nuốt '/create')
    Route::get('phong-chieu/{phongChieu}', [PhongChieuController::class, 'show'])->whereNumber('phongChieu')->name('phong-chieu.show');

    // Quản lý ghế (legacy)
    // Admin: xem danh sách
    Route::get('ghe', [GheController::class, 'index'])->name('ghe.index');
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->name('ghe.by-room');
    // Admin: CRUD & thao tác
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
    // Admin: chi tiết (ràng buộc số để không nuốt '/create')
    Route::get('ghe/{ghe}', [GheController::class, 'show'])->whereNumber('ghe')->name('ghe.show');

    // Quản lý đặt vé
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [QuanLyDatVeController::class, 'index'])->name('index');
        Route::get('/{id}', [QuanLyDatVeController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [QuanLyDatVeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [QuanLyDatVeController::class, 'update'])->name('update');
        Route::post('/{id}/cancel', [QuanLyDatVeController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/confirm', [QuanLyDatVeController::class, 'confirm'])->name('confirm');
        Route::post('/{id}/update-payment', [QuanLyDatVeController::class, 'updatePayment'])->name('update-payment');

        // API cho UI chỉnh sửa vé
        Route::get('/{id}/available-showtimes', [QuanLyDatVeController::class, 'availableShowtimes'])->name('available-showtimes');
    });

    // API lấy bản đồ ghế theo suất chiếu
    Route::get('showtimes/{suatChieu}/seats', [QuanLyDatVeController::class, 'seatsByShowtime'])->name('showtimes.seats');
    Route::get('admin/showtimes/{suatChieu}/seats', [QuanLyDatVeController::class, 'seatsByShowtime'])->name('admin.showtimes.seats');

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

    // Admin được xem
    Route::get('/khuyenmai', [AdminKhuyenMaiController::class, 'index'])->name('khuyenmai.index');
    Route::get('/khuyenmai/{khuyenmai}', [AdminKhuyenMaiController::class, 'show'])
        ->whereNumber('khuyenmai')
        ->name('khuyenmai.show');

    // Quản lý Combo
    // Admin: xem danh sách
    Route::get('combos', [ComboController::class, 'index'])->name('combos.index');
    // Admin: CRUD (đặt trước show để tránh nuốt '/create')
    Route::middleware('role:admin')->group(function () {
        Route::get('combos/create', [ComboController::class, 'create'])->name('combos.create');
        Route::post('combos', [ComboController::class, 'store'])->name('combos.store');
        Route::get('combos/{combo}/edit', [ComboController::class, 'edit'])->name('combos.edit');
        Route::put('combos/{combo}', [ComboController::class, 'update'])->name('combos.update');
        Route::delete('combos/{combo}', [ComboController::class, 'destroy'])->name('combos.destroy');
    });
    // Admin: chi tiết (ràng buộc là số để tránh nuốt '/create')
    Route::get('combos/{combo}', [ComboController::class, 'show'])
        ->whereNumber('combo')
        ->name('combos.show');
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

    // Quản lý phim (Staff chỉ xem)
    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', function() {
            $query = \App\Models\Phim::query();
            
            // Status filter
            if (request()->filled('status')) {
                $query->where('trang_thai', request()->string('status'));
            }
            
            // Search filter
            if (request()->filled('search')) {
                $s = trim(request()->string('search'));
                if ($s !== '') {
                    $query->where(function ($q) use ($s) {
                        $q->where('ten_phim', 'like', "%{$s}%")
                          ->orWhere('ten_goc', 'like', "%{$s}%")
                          ->orWhere('dao_dien', 'like', "%{$s}%")
                          ->orWhere('dien_vien', 'like', "%{$s}%")
                          ->orWhere('the_loai', 'like', "%{$s}%");
                    });
                }
            }
            
            // Additional filters
            if (request()->filled('dien_vien')) {
                $qActor = trim((string) request()->dien_vien);
                $query->where('dien_vien', 'like', "%{$qActor}%");
            }
            if (request()->filled('the_loai')) {
                $qGenre = trim((string) request()->the_loai);
                $query->where('the_loai', 'like', "%{$qGenre}%");
            }
            if (request()->filled('quoc_gia')) {
                $qCountry = trim((string) request()->quoc_gia);
                $query->where('quoc_gia', 'like', "%{$qCountry}%");
            }
            
            $movies = $query->orderByDesc('created_at')->paginate(12);
            
            // Quick stats
            $totalMovies = (int) \App\Models\Phim::count();
            $nowShowing = (int) \App\Models\Phim::where('trang_thai', 'dang_chieu')->count();
            $upcoming = (int) \App\Models\Phim::where('trang_thai', 'sap_chieu')->count();
            $ended = (int) \App\Models\Phim::where('trang_thai', 'ngung_chieu')->count();
            
            return view('staff.phim.index', compact('movies', 'totalMovies', 'nowShowing', 'upcoming', 'ended'));
        })->name('index');
        Route::get('/{movie}', function($movie) {
            $movie = \App\Models\Phim::findOrFail($movie);
            return view('staff.phim.show', compact('movie'));
        })->name('show');
    });

    // Quản lý người dùng (Staff chỉ xem)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', function() {
            $query = \App\Models\NguoiDung::query();
            
            // Search filter
            if (request()->filled('search')) {
                $s = trim(request()->search);
                if ($s !== '') {
                    $query->where(function ($q) use ($s) {
                        $q->where('ho_ten', 'like', "%{$s}%")
                          ->orWhere('email', 'like', "%{$s}%");
                    });
                }
            }
            
            $users = $query->orderByDesc('created_at')->paginate(12);
            
            // Quick stats - sử dụng query builder
            $totalUsers = (int) \App\Models\NguoiDung::count();
            $activeUsers = (int) \App\Models\NguoiDung::where('trang_thai', 1)->count();
            $inactiveUsers = (int) \App\Models\NguoiDung::where('trang_thai', 0)->count();
            $adminUsers = (int) \App\Models\NguoiDung::whereHas('vaiTro', function($q) { $q->where('ten', 'admin'); })->count();
            
            return view('staff.users.index', compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers', 'adminUsers'));
        })->name('index');
        Route::get('/{id}', function($id) {
            $user = \App\Models\NguoiDung::findOrFail($id);
            return view('staff.users.show', compact('user'));
        })->whereNumber('id')->name('show');
    });

    // Quản lý suất chiếu (Staff chỉ xem)
    Route::prefix('suat-chieu')->name('suat-chieu.')->group(function () {
        Route::get('/', function() {
            $query = \App\Models\SuatChieu::query();
            
            // Search filter
            if (request()->filled('search')) {
                $s = trim(request()->search);
                if ($s !== '') {
                    $query->where(function ($q) use ($s) {
                        $q->whereHas('phim', function($subQ) use ($s) {
                            $subQ->where('ten_phim', 'like', "%{$s}%");
                        });
                    });
                }
            }
            
            $suatChieus = $query->orderByDesc('thoi_gian_bat_dau')->paginate(12);
            
            // Quick stats
            $totalShowtimes = (int) \App\Models\SuatChieu::count();
            $todayShowtimes = (int) \App\Models\SuatChieu::whereDate('thoi_gian_bat_dau', \Carbon\Carbon::today())->count();
            $upcomingShowtimes = (int) \App\Models\SuatChieu::where('thoi_gian_bat_dau', '>', \Carbon\Carbon::now())->count();
            $pastShowtimes = (int) \App\Models\SuatChieu::where('thoi_gian_bat_dau', '<', \Carbon\Carbon::now())->count();
            
            return view('staff.suat-chieu.index', compact('suatChieus', 'totalShowtimes', 'todayShowtimes', 'upcomingShowtimes', 'pastShowtimes'));
        })->name('index');
        Route::get('/{suatChieu}', function($suatChieu) {
            $suatChieu = \App\Models\SuatChieu::findOrFail($suatChieu);
            return view('staff.suat-chieu.show', compact('suatChieu'));
        })->name('show');
    });

    // Quản lý phòng chiếu (Staff chỉ xem)
    Route::prefix('phong-chieu')->name('phong-chieu.')->group(function () {
        Route::get('/', function() {
            $query = \App\Models\PhongChieu::query();
            
            // Search filter
            if (request()->filled('search')) {
                $s = trim(request()->search);
                if ($s !== '') {
                    $query->where('ten_phong', 'like', "%{$s}%");
                }
            }
            
            $phongChieus = $query->orderByDesc('id')->paginate(12);
            
            // Quick stats - sử dụng các giá trị có thể có trong database
            $totalRooms = (int) \App\Models\PhongChieu::count();
            $activeRooms = (int) \App\Models\PhongChieu::where('status', 'active')->orWhere('status', 'hoạt động')->orWhere('status', 1)->count();
            $inactiveRooms = (int) \App\Models\PhongChieu::where('status', 'inactive')->orWhere('status', 'ngừng hoạt động')->orWhere('status', 0)->count();
            $totalSeats = (int) \App\Models\Ghe::count();
            
            return view('staff.phong-chieu.index', compact('phongChieus', 'totalRooms', 'activeRooms', 'inactiveRooms', 'totalSeats'));
        })->name('index');
        Route::get('/{phongChieu}', function($phongChieu) {
            $phongChieu = \App\Models\PhongChieu::findOrFail($phongChieu);
            return view('staff.phong-chieu.show', compact('phongChieu'));
        })->whereNumber('phongChieu')->name('show');
    });

    // Quản lý ghế (Staff chỉ xem)
    Route::prefix('ghe')->name('ghe.')->group(function () {
        Route::get('/', function() {
            $query = \App\Models\Ghe::query();
            
            // Search filter
            if (request()->filled('search')) {
                $s = trim(request()->search);
                if ($s !== '') {
                    $query->where(function ($q) use ($s) {
                        $q->where('so_ghe', 'like', "%{$s}%")
                          ->orWhereHas('phongChieu', function($subQ) use ($s) {
                              $subQ->where('ten_phong', 'like', "%{$s}%");
                          });
                    });
                }
            }
            
            $ghes = $query->orderByDesc('id')->paginate(12);
            
            // Quick stats - chỉ sử dụng các cột chắc chắn tồn tại
            $totalSeats = (int) \App\Models\Ghe::count();
            $normalSeats = 0; // Tạm thời đặt 0 vì không có cột loai_ghe
            $vipSeats = 0; // Tạm thời đặt 0 vì không có cột loai_ghe
            $availableSeats = 0; // Tạm thời đặt 0 vì không chắc cột trang_thai
            
            return view('staff.ghe.index', compact('ghes', 'totalSeats', 'normalSeats', 'vipSeats', 'availableSeats'));
        })->name('index');
        Route::get('/{ghe}', function($ghe) {
            $ghe = \App\Models\Ghe::findOrFail($ghe);
            return view('staff.ghe.show', compact('ghe'));
        })->whereNumber('ghe')->name('show');
    });

    // Quản lý combo (Staff chỉ xem)
    Route::prefix('combos')->name('combos.')->group(function () {
        Route::get('/', function() {
            $query = \App\Models\Combo::query();
            
            // Search filter
            if (request()->filled('search')) {
                $s = trim(request()->search);
                if ($s !== '') {
                    $query->where('ten_combo', 'like', "%{$s}%");
                }
            }
            
            $combos = $query->orderByDesc('created_at')->paginate(12);
            
            // Quick stats
            $totalCombos = (int) \App\Models\Combo::count();
            $activeCombos = (int) \App\Models\Combo::where('trang_thai', 'active')->count();
            $inactiveCombos = (int) \App\Models\Combo::where('trang_thai', 'inactive')->count();
            $avgPrice = \App\Models\Combo::avg('gia');
            
            return view('staff.combos.index', compact('combos', 'totalCombos', 'activeCombos', 'inactiveCombos', 'avgPrice'));
        })->name('index');
        Route::get('/{combo}', function($combo) {
            $combo = \App\Models\Combo::findOrFail($combo);
            return view('staff.combos.show', compact('combo'));
        })->whereNumber('combo')->name('show');
    });

    // Quản lý đặt vé (Staff chỉ xem)
    Route::prefix('dat-ve')->name('dat-ve.')->group(function () {
        Route::get('/', function() {
            $query = \App\Models\DatVe::query();
            
            // Search filter
            if (request()->filled('search')) {
                $s = trim(request()->search);
                if ($s !== '') {
                    $query->where(function ($q) use ($s) {
                        $q->whereHas('nguoiDung', function($subQ) use ($s) {
                            $subQ->where('ho_ten', 'like', "%{$s}%");
                        })
                        ->orWhereHas('suatChieu.phim', function($subQ) use ($s) {
                            $subQ->where('ten_phim', 'like', "%{$s}%");
                        });
                    });
                }
            }
            
            // Status filter
            if (request()->filled('status')) {
                $query->where('trang_thai', request()->string('status'));
            }
            
            $datVes = $query->orderByDesc('id')->paginate(12);
            
            // Quick stats - chỉ sử dụng các cột chắc chắn tồn tại
            $totalBookings = (int) \App\Models\DatVe::count();
            $paidBookings = (int) \App\Models\DatVe::where('trang_thai', 1)->count();
            $pendingBookings = (int) \App\Models\DatVe::where('trang_thai', 0)->count();
            $totalRevenue = 0; // Tạm thời đặt 0 vì không có cột tong_tien
            
            return view('staff.dat-ve.index', compact('datVes', 'totalBookings', 'paidBookings', 'pendingBookings', 'totalRevenue'));
        })->name('index');
        Route::get('/{datVe}', function($datVe) {
            $datVe = \App\Models\DatVe::findOrFail($datVe);
            return view('staff.dat-ve.show', compact('datVe'));
        })->whereNumber('datVe')->name('show');
    });

    // Quản lý khuyến mãi (Staff chỉ xem)
    Route::prefix('khuyen-mai')->name('khuyen-mai.')->group(function () {
        Route::get('/', function() {
            $query = \App\Models\KhuyenMai::query();
            
            // Search filter
            if (request()->filled('search')) {
                $s = trim(request()->search);
                if ($s !== '') {
                    $query->where('ten_khuyen_mai', 'like', "%{$s}%");
                }
            }
            
            // Status filter
            if (request()->filled('status')) {
                if (request()->string('status') === 'active') {
                    $query->where('ngay_bat_dau', '<=', now())
                          ->where('ngay_ket_thuc', '>=', now());
                } elseif (request()->string('status') === 'expired') {
                    $query->where('ngay_ket_thuc', '<', now());
                } elseif (request()->string('status') === 'upcoming') {
                    $query->where('ngay_bat_dau', '>', now());
                }
            }
            
            $khuyenMais = $query->orderByDesc('id')->paginate(12);
            
            // Quick stats
            $totalPromotions = (int) \App\Models\KhuyenMai::count();
            $activePromotions = (int) \App\Models\KhuyenMai::where('ngay_bat_dau', '<=', now())
                                                          ->where('ngay_ket_thuc', '>=', now())->count();
            $expiredPromotions = (int) \App\Models\KhuyenMai::where('ngay_ket_thuc', '<', now())->count();
            $upcomingPromotions = (int) \App\Models\KhuyenMai::where('ngay_bat_dau', '>', now())->count();
            
            return view('staff.khuyen-mai.index', compact('khuyenMais', 'totalPromotions', 'activePromotions', 'expiredPromotions', 'upcomingPromotions'));
        })->name('index');
        Route::get('/{khuyenMai}', function($khuyenMai) {
            $khuyenMai = \App\Models\KhuyenMai::findOrFail($khuyenMai);
            return view('staff.khuyen-mai.show', compact('khuyenMai'));
        })->whereNumber('khuyenMai')->name('show');
    });
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
