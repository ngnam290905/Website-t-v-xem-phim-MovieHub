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

// Staff routes - Staff can only view suat chieu and manage rooms/seats
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
    Route::get('ghe-by-room', [GheController::class, 'getByRoom'])->name('ghe.by-room');
    Route::post('ghe/generate', [GheController::class, 'generateSeats'])->name('ghe.generate');
});

// Staff routes - Staff can only view suat chieu and manage rooms/seats
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
>>>>>>> Duy


// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', function () { return view('auth.register'); })->name('register.form');
    Route::get('/login', function () { return view('auth.login'); })->name('login.form');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

