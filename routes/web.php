<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuatChieuController;
use App\Http\Controllers\GheController;
use App\Http\Controllers\PhongChieuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ScanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminKhuyenMaiController;
use App\Http\Controllers\QuanLyDatVeController;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\ComboController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ThanhVienController;
use App\Http\Controllers\BookingController;


// Middleware alias 'block.admin.staff' is registered in bootstrap/app.php

// Main routes
Route::get('/', [HomeController::class, 'index'])->middleware('block.admin.staff')->name('home');
Route::get('/ve', function(){ return View::make('tickets.check'); })->middleware('block.admin.staff')->name('tickets.check');

// Movie listing pages (must be before /movies/{movie} to avoid route conflict)
Route::get('/movies/category/{category}', [MovieController::class, 'category'])->middleware('block.admin.staff')->name('movies.category');

// Movie detail routes
Route::get('/movies/{movie}', [MovieController::class, 'show'])->middleware('block.admin.staff')->name('movie-detail');

// Client Movie Routes
Route::prefix('phim')->name('movies.')->middleware('block.admin.staff')->group(function () {
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

// Public Chatbot endpoint (mirror of routes/api.php) to prevent 404 in some deployments
Route::middleware('api')->post('/api/chat', [ChatController::class, 'chat'])->name('chat.api');

// API routes for AJAX calls
Route::get('/api/movies', [MovieController::class, 'getMovies'])->name('api.movies');
Route::get('/api/featured-movies', [MovieController::class, 'getFeaturedMovies'])->name('api.featured-movies');
Route::get('/api/search', [MovieController::class, 'search'])->name('api.search');
Route::get('/api/suat-chieu/{movieId}', [MovieController::class, 'getSuatChieu'])->name('api.suat-chieu');
Route::get('/api/phong-chieu', [MovieController::class, 'getPhongChieu'])->name('api.phong-chieu');
Route::get('/api/booked-seats/{showtimeId}', [BookingController::class, 'getBookedSeats'])->name('api.booked-seats');
Route::get('/showtime-seats/{showtimeId}', [BookingController::class, 'getShowtimeSeats']);
Route::post('/api/showtimes/{id}/select-seats', [BookingController::class, 'selectSeats'])->name('api.showtimes.select-seats');
// VNPAY return is handled by PaymentController below (single source of truth)
// Booking routes (new user flow)
Route::get('/booking', [App\Http\Controllers\BookingFlowController::class, 'index'])->middleware('block.admin.staff')->name('booking.index');
Route::get('/booking/movie/{movieId}/showtimes', [App\Http\Controllers\BookingFlowController::class, 'showtimes'])->middleware('block.admin.staff')->name('booking.showtimes');
Route::get('/api/booking/movie/{movieId}/showtimes', [App\Http\Controllers\BookingFlowController::class, 'getShowtimesByDate'])->middleware('block.admin.staff')->name('api.booking.showtimes');
Route::get('/api/booking/movie/{movieId}/dates', [App\Http\Controllers\BookingFlowController::class, 'getAvailableDates'])->middleware('block.admin.staff')->name('api.booking.dates');

// Booking data display routes
Route::get('/booking-data', [App\Http\Controllers\BookingDataController::class, 'index'])->middleware('block.admin.staff')->name('booking.data');
Route::get('/booking-data/movie/{id}', [App\Http\Controllers\BookingDataController::class, 'movie'])->middleware('block.admin.staff')->name('booking.data.movie');
Route::get('/booking-data/room/{id}', [App\Http\Controllers\BookingDataController::class, 'room'])->middleware('block.admin.staff')->name('booking.data.room');
Route::get('/booking-data/showtime/{id}', [App\Http\Controllers\BookingDataController::class, 'showtime'])->middleware('block.admin.staff')->name('booking.data.showtime');
Route::get('/booking-data/booking/{id}', [App\Http\Controllers\BookingDataController::class, 'booking'])->middleware('block.admin.staff')->name('booking.data.booking');

// Public pages
// Route::get('/phim', [PublicController::class, 'movies'])->name('public.movies'); // Commented out - using movies.index instead
Route::get('/lich-chieu', [PublicController::class, 'schedule'])->middleware('block.admin.staff')->name('public.schedule');
Route::get('/combo', [PublicController::class, 'combos'])->middleware('block.admin.staff')->name('public.combos');
Route::get('/tin-tuc', [PublicController::class, 'news'])->middleware('block.admin.staff')->name('public.news');
Route::get('/tin-tuc/{slug}', [PublicController::class, 'newsDetail'])->middleware('block.admin.staff')->name('public.news.detail');
Route::get('/gioi-thieu', function(){ return View::make('about'); })->middleware('block.admin.staff')->name('about');

// Debug route (remove in production)
Route::get('/debug/showtimes', [App\Http\Controllers\DebugController::class, 'checkShowtimes'])->name('debug.showtimes');
Route::get('/test/showtimes-today', function() {
    $today = \Carbon\Carbon::today()->format('Y-m-d');
    $now = now();
    
    $allToday = \App\Models\SuatChieu::whereDate('thoi_gian_bat_dau', $today)
        ->where('trang_thai', 1)
        ->get();
    
    $notEnded = \App\Models\SuatChieu::whereDate('thoi_gian_bat_dau', $today)
        ->where('trang_thai', 1)
        ->where('thoi_gian_ket_thuc', '>', $now)
        ->get();
    
    return response()->json([
        'today' => $today,
        'now' => $now->format('Y-m-d H:i:s'),
        'all_today_count' => $allToday->count(),
        'not_ended_count' => $notEnded->count(),
        'all_today' => $allToday->map(function($st) {
            return [
                'id' => $st->id,
                'movie_id' => $st->id_phim,
                'start' => $st->thoi_gian_bat_dau->format('Y-m-d H:i:s'),
                'end' => $st->thoi_gian_ket_thuc->format('Y-m-d H:i:s'),
                'is_ended' => $st->thoi_gian_ket_thuc->lt(now()),
            ];
        }),
        'not_ended' => $notEnded->map(function($st) {
            return [
                'id' => $st->id,
                'movie_id' => $st->id_phim,
                'start' => $st->thoi_gian_bat_dau->format('Y-m-d H:i:s'),
                'end' => $st->thoi_gian_ket_thuc->format('Y-m-d H:i:s'),
            ];
        }),
    ], 200, [], JSON_PRETTY_PRINT);
});

// Booking store route - requires authentication
Route::post('/booking/store', [BookingController::class, 'store'])->middleware('auth')->middleware('block.admin.staff')->name('booking.store.public');

// New booking system routes
Route::middleware('auth')->middleware('block.admin.staff')->prefix('booking')->name('booking.')->group(function () {
    Route::post('/store', [BookingController::class, 'store'])->name('store');
});


Route::middleware('auth')->middleware('block.admin.staff')->group(function () {
    Route::get('/shows/{showId}/seats', [App\Http\Controllers\BookingController::class, 'showSeats'])->name('booking.seats');
    // New seat hold endpoints
    Route::post('/shows/{showId}/seats/hold', [App\Http\Controllers\BookingController::class, 'holdSeat'])->name('booking.seats.hold');
    Route::post('/shows/{showId}/seats/release', [App\Http\Controllers\BookingController::class, 'releaseSeat'])->name('booking.seats.release');
    Route::post('/shows/{showId}/seats/confirm-booking', [App\Http\Controllers\BookingController::class, 'confirmBooking'])->name('booking.seats.confirm');
    // Legacy endpoints removed: lock/unlock (frontend switched to hold/release)
Route::get('/shows/{showId}/seats/refresh', [App\Http\Controllers\BookingController::class, 'refreshSeats'])->name('booking.seats.refresh');
    Route::get('/bookings/{bookingId}/addons', [App\Http\Controllers\BookingController::class, 'addons'])->name('booking.addons');
    Route::post('/bookings/{bookingId}/addons', [App\Http\Controllers\BookingController::class, 'updateAddons'])->name('booking.addons.update');
    Route::get('/checkout/{bookingId}', [App\Http\Controllers\BookingController::class, 'checkout'])->name('booking.checkout');
    Route::post('/checkout/{bookingId}/payment', [App\Http\Controllers\BookingController::class, 'processPayment'])->name('booking.payment.process');
    Route::post('/payment/callback', [App\Http\Controllers\BookingController::class, 'paymentCallback'])->name('booking.payment.callback');
    Route::get('/result', [App\Http\Controllers\BookingController::class, 'result'])->name('booking.result');
    Route::get('/tickets', [App\Http\Controllers\BookingController::class, 'tickets'])->name('booking.tickets');
    Route::get('/tickets/{id}', [App\Http\Controllers\BookingController::class, 'ticketDetail'])->name('booking.ticket.detail');

    // Continue to payment from seat selection
    Route::post('/booking/continue', [App\Http\Controllers\BookingController::class, 'continueToPayment'])->name('booking.continue');
    Route::get('/booking/payment', [App\Http\Controllers\BookingController::class, 'showPaymentPage'])->name('booking.payment');
});

// Legacy booking routes
Route::get('/dat-ve/{id?}', [BookingController::class, 'create'])->middleware('block.admin.staff')->name('booking');
Route::get('/dat-ve-dong/{id?}', function ($id = 1) {
    return view('booking-dynamic', ['id' => $id]);
})->middleware('block.admin.staff')->name('booking-dynamic');


Route::get('/payment/vnpay-return', [\App\Http\Controllers\PaymentController::class, 'vnpayReturn'])->name('payment.vnpay_return');
// VNPAY IPN (server-to-server) callback (no CSRF)
Route::post('/payment/vnpay-ipn', [\App\Http\Controllers\PaymentController::class, 'vnpayIpn'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
    ->name('payment.vnpay_ipn');

// Mini game route
Route::get('/mini-game', function () {
    return view('mini-game');
})->middleware('block.admin.staff')->name('mini-game');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.form');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// User profile routes
Route::middleware('auth')->middleware('block.admin.staff')->prefix('user')->name('user.')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile');
    Route::get('/edit-profile', [UserProfileController::class, 'edit'])->name('edit-profile');
    Route::put('/update-profile', [UserProfileController::class, 'update'])->name('update-profile');
    Route::get('/change-password', [UserProfileController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [UserProfileController::class, 'changePassword'])->name('change-password');
    Route::get('/booking-history', [UserProfileController::class, 'bookingHistory'])->name('booking-history');
    // Route::post('/cancel-booking/{id}', [UserProfileController::class, 'cancelBooking'])->name('cancel-booking'); // disabled
    
    // Additional routes from master
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    // Route::post('/bookings/{id}/cancel', [UserProfileController::class, 'cancelBooking'])->name('bookings.cancel'); // disabled
});

// Thành viên routes (loyalty program)
Route::middleware('auth')->middleware('block.admin.staff')->prefix('thanh-vien')->name('thanh-vien.')->group(function () {
    Route::get('/profile', [ThanhVienController::class, 'profile'])->name('profile');
});


// Admin routes - cả Admin và Staff
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,staff'])->group(function () {
    // Admin dashboard for admins, redirect staff to a suitable module (e.g., movies)
    Route::get('/', [AdminController::class, 'handleDashboard'])->name('dashboard');

    // Quản lý phim (Admin & Staff view-only except staff may be restricted by policy)
    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', [MovieController::class, 'adminIndex'])->name('index');
        Route::get('/search', [MovieController::class, 'adminIndex'])->name('search');
Route::get('/create', [MovieController::class, 'create'])->middleware('role:admin,staff')->name('create');
        Route::post('/', [MovieController::class, 'store'])->middleware('role:admin,staff')->name('store');
        Route::get('/{movie}', [MovieController::class, 'show'])->name('show');
        Route::get('/{movie}/edit', [MovieController::class, 'edit'])->middleware('role:admin,staff')->name('edit');
        Route::put('/{movie}', [MovieController::class, 'update'])->middleware('role:admin,staff')->name('update');
        Route::delete('/{movie}', [MovieController::class, 'destroy'])->middleware('role:admin,staff')->name('destroy');
        Route::patch('/{movie}/toggle-status', [MovieController::class, 'toggleStatus'])->middleware('role:admin,staff')->name('toggle-status');
    });

    // Quản lý người dùng
    Route::prefix('users')->name('users.')->middleware('role:admin')->group(function () {
        // Route xem chi tiết người dùng - cho cả admin và staff
        Route::get('/{id}', [UserController::class, 'show'])
            ->whereNumber('id')
            ->name('show')
            ->middleware('role:admin');
            
        // Các route quản lý người dùng - chỉ admin
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [UserController::class, 'trash'])->name('trash');
            Route::post('/restore/{id}', [UserController::class, 'restore'])->name('restore');
        });
    });

    // Quản lý suất chiếu (Admin only)
    // Chỉ Admin: CRUD & thao tác thay đổi trạng thái/nhân bản (đặt trước show để tránh nuốt '/create')
    Route::middleware('role:admin')->group(function () {
        Route::get('suat-chieu/auto', [SuatChieuController::class, 'auto'])->name('suat-chieu.auto');
        Route::get('suat-chieu/create', [SuatChieuController::class, 'create'])->name('suat-chieu.create');
        Route::post('suat-chieu', [SuatChieuController::class, 'store'])->name('suat-chieu.store');
        Route::post('suat-chieu/check-conflict', [SuatChieuController::class, 'checkConflict'])->name('suat-chieu.check-conflict');
        Route::get('suat-chieu/{suatChieu}/edit', [SuatChieuController::class, 'edit'])->name('suat-chieu.edit');
        Route::put('suat-chieu/{suatChieu}', [SuatChieuController::class, 'update'])->name('suat-chieu.update');
        Route::delete('suat-chieu/{suatChieu}', [SuatChieuController::class, 'destroy'])->name('suat-chieu.destroy');
Route::patch('suat-chieu/{suatChieu}/status', [SuatChieuController::class, 'updateStatus'])->name('suat-chieu.update-status');
        Route::post('suat-chieu/{suatChieu}/duplicate', [SuatChieuController::class, 'duplicate'])->name('suat-chieu.duplicate');
    });
    // Admin only: xem danh sách/chi tiết
    Route::resource('suat-chieu', SuatChieuController::class)->only(['index', 'show'])->middleware('role:admin');
    Route::get('suat-chieu-by-movie-date', [SuatChieuController::class, 'getByMovieAndDate'])->middleware('role:admin')->name('suat-chieu.by-movie-date');

    // Quản lý phòng chiếu
    // Staff & Admin: chỉ xem danh sách
    Route::get('phong-chieu', [PhongChieuController::class, 'index'])->name('phong-chieu.index');
    Route::get('phong-chieu/{phongChieu}/seats', [PhongChieuController::class, 'getByRoom'])->name('phong-chieu.seats');
    // Chỉ Admin: CRUD & seat management (đặt trước show để tránh nuốt '/create')
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('phong-chieu/create', [PhongChieuController::class, 'create'])->name('phong-chieu.create');
        Route::post('phong-chieu', [PhongChieuController::class, 'store'])->name('phong-chieu.store');
        Route::get('phong-chieu/{phongChieu}/edit', [PhongChieuController::class, 'edit'])->name('phong-chieu.edit');
        Route::put('phong-chieu/{phongChieu}', [PhongChieuController::class, 'update'])->name('phong-chieu.update');
        Route::delete('phong-chieu/{phongChieu}', [PhongChieuController::class, 'destroy'])->name('phong-chieu.destroy');
        // Fallback route to handle POST requests that should be DELETE
        Route::post('phong-chieu/{phongChieu}/delete', [PhongChieuController::class, 'destroy'])->name('phong-chieu.destroy.post');
        Route::patch('phong-chieu/{phongChieu}/status', [PhongChieuController::class, 'updateStatus'])->name('phong-chieu.update-status');
        Route::get('phong-chieu/{phongChieu}/can-modify', [PhongChieuController::class, 'canModify'])->name('phong-chieu.can-modify');
        Route::post('phong-chieu/{phongChieu}/generate-seats', [PhongChieuController::class, 'generateSeats'])->name('phong-chieu.generate-seats');
        Route::get('phong-chieu/{phongChieu}/manage-seats', [PhongChieuController::class, 'manageSeats'])->name('phong-chieu.manage-seats');
        Route::get('phong-chieu/{phongChieu}/seats/{ghe}', [PhongChieuController::class, 'showSeat'])->name('phong-chieu.seats.show');
        Route::post('phong-chieu/{phongChieu}/seats', [PhongChieuController::class, 'storeSeat'])->name('phong-chieu.seats.store');
        Route::put('phong-chieu/{phongChieu}/seats/{ghe}', [PhongChieuController::class, 'updateSeat'])->name('phong-chieu.seats.update');
        Route::delete('phong-chieu/{phongChieu}/seats/{ghe}', [PhongChieuController::class, 'destroySeat'])->name('phong-chieu.seats.destroy');
Route::patch('seats/{ghe}/status', [PhongChieuController::class, 'updateSeatStatus'])->name('seats.update-status');
        Route::patch('seats/{ghe}/type', [PhongChieuController::class, 'updateSeatType'])->name('seats.update-type');
        Route::post('phong-chieu/{phongChieu}/seats/bulk', [PhongChieuController::class, 'bulkSeats'])->name('phong-chieu.seats.bulk');
        Route::post('phong-chieu/{phongChieu}/seats/bulk-create', [PhongChieuController::class, 'bulkCreateSeats'])->name('phong-chieu.seats.bulk-create');
        Route::post('phong-chieu/{phongChieu}/seats/positions', [PhongChieuController::class, 'updateSeatPositions'])->name('phong-chieu.seats.positions');
        Route::post('phong-chieu/{phongChieu}/seats/append', [PhongChieuController::class, 'appendSeats'])->name('phong-chieu.seats.append');
        // Peak hours configuration routes
        Route::get('phong-chieu/peak-hours', [PhongChieuController::class, 'showPeakHoursConfig'])->name('phong-chieu.peak-hours');
        Route::post('phong-chieu/peak-hours', [PhongChieuController::class, 'createPeakHoursShowtimes'])->name('phong-chieu.peak-hours.store');
    });
    // Staff & Admin: chi tiết (ràng buộc là số để tránh nuốt '/create')
    Route::get('phong-chieu/{phongChieu}', [PhongChieuController::class, 'show'])->whereNumber('phongChieu')->name('phong-chieu.show');

    // Quản lý ghế (Admin only)
    // Admin: chỉ xem danh sách
    Route::get('ghe', [GheController::class, 'index'])->middleware('role:admin')->name('ghe.index');
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->middleware('role:admin')->name('ghe.by-room');
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
    // Staff & Admin: bỏ trang xem chi tiết ghế

    // Quản lý đặt vé
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [QuanLyDatVeController::class, 'index'])->name('index');
        // Các route cụ thể phải đặt TRƯỚC route '/{id}' để tránh nuốt đường dẫn
        Route::get('/{id}/edit', [QuanLyDatVeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [QuanLyDatVeController::class, 'update'])->name('update');
// Route::post('/{id}/cancel', [QuanLyDatVeController::class, 'cancel'])->name('cancel'); // disabled
        Route::post('/{id}/confirm', [QuanLyDatVeController::class, 'confirm'])->name('confirm');
        Route::post('/{id}/send-ticket', [QuanLyDatVeController::class, 'sendTicket'])->name('send-ticket');

        // API cho UI chỉnh sửa vé
        Route::get('/{id}/available-showtimes', [QuanLyDatVeController::class, 'availableShowtimes'])->name('available-showtimes');

        // Đặt SAU cùng
        Route::get('/{id}', [QuanLyDatVeController::class, 'show'])->name('show');
    });

    // API lấy bản đồ ghế theo suất chiếu
    Route::get('showtimes/{suatChieu}/seats', [QuanLyDatVeController::class, 'seatsByShowtime'])->name('showtimes.seats');

    // KHUYẾN MẠI
    // Đặt nhóm Admin (tạo/sửa/xóa) TRƯỚC để tránh 'show' nuốt '/create'
    Route::middleware('role:admin,staff')->group(function () {
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
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('combos/create', [ComboController::class, 'create'])->name('combos.create');
        Route::post('combos', [ComboController::class, 'store'])->name('combos.store');
        Route::get('combos/{combo}/edit', [ComboController::class, 'edit'])->name('combos.edit');
        Route::put('combos/{combo}', [ComboController::class, 'update'])->name('combos.update');
        Route::delete('combos/{combo}', [ComboController::class, 'destroy'])->name('combos.destroy');
    });
    // Admin & Staff: chi tiết (ràng buộc là số để tránh nuốt '/create')
    Route::get('combos/{combo}', [ComboController::class, 'show'])->whereNumber('combo')->name('combos.show');

    // Quản lý Scan vé
    Route::prefix('scan')->name('scan.')->group(function () {
Route::get('/', [ScanController::class, 'index'])->name('index');
        Route::get('/{id}', [ScanController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('/check', [ScanController::class, 'check'])->name('check');
        Route::post('/confirm', [ScanController::class, 'confirm'])->name('confirm');
    });
});

// BÁO CÁO - CHỈ ADMIN
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/top-movies', [ReportController::class, 'topMovies'])->name('top-movies');
        Route::get('/top-customers', [ReportController::class, 'topCustomers'])->name('top-customers');
        Route::get('/member-revenue', [ReportController::class, 'memberRevenue'])->name('member-revenue');
        Route::get('/popular-movies-showtimes', [ReportController::class, 'popularMoviesAndShowtimes'])->name('popular-movies-showtimes');
        Route::get('/movies-showtimes-data', [ReportController::class, 'moviesAndShowtimesData'])->name('movies-showtimes-data');
        Route::get('/bookings-data', [ReportController::class, 'bookingsData'])->name('bookings-data');
        Route::get('/hot-movies', [AdminReportController::class, 'hotMoviesReport'])->name('hot-movies');
        Route::get('/peak-booking-hours', [AdminReportController::class, 'peakBookingHoursReport'])->name('peak-booking-hours');
    });
});

// ==================== STAFF ROUTES REMOVED ====================
// Redirect all /staff URLs to /admin
Route::permanentRedirect('/staff', '/admin');
Route::permanentRedirect('/staff/{any}', '/admin')->where('any', '.*');

Route::post('/seat-price', [BookingController::class, 'getSeatPrice'])->middleware('block.admin.staff');
Route::post('/showtimes/{suatChieuId}/select-seats-temp', [BookingController::class, 'selectSeatsTemp'])->middleware('block.admin.staff');

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
