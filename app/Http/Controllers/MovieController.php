<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use App\Models\SuatChieu;
use App\Models\PhongChieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       

class MovieController extends Controller
{
    /**
     * Hiển thị danh sách phim (trang chủ)
     */
    public function index()
    {
        // Get featured movies for hero banner (top 5 movies with highest rating and currently showing)
        $featuredMovies = Phim::where('trang_thai', 'dang_chieu')
            ->orderByDesc('diem_danh_gia')
            ->orderByDesc('ngay_khoi_chieu')
            ->limit(5)
            ->get();
        
        // If not enough "dang_chieu" movies, add "sap_chieu"
        if ($featuredMovies->count() < 5) {
            $upcomingMovies = Phim::where('trang_thai', 'sap_chieu')
                ->orderByDesc('diem_danh_gia')
                ->orderByDesc('ngay_khoi_chieu')
                ->limit(5 - $featuredMovies->count())
                ->get();
            $featuredMovies = $featuredMovies->merge($upcomingMovies);
        }
        
        // Get featured movie IDs to exclude from other sections
        $featuredMovieIds = $featuredMovies->pluck('id')->toArray();
        
        // Phim hot (rating >= 8.0 và đang chiếu, exclude featured movies)
        $hotMovies = Phim::where('trang_thai', 'dang_chieu')
            ->where('diem_danh_gia', '>=', 8.0)
            ->whereNotIn('id', $featuredMovieIds)
            ->orderByDesc('diem_danh_gia')
            ->orderByDesc('so_luot_danh_gia')
            ->limit(8)
            ->get();
        
        // Get hot movie IDs to exclude from now showing section
        $hotMovieIds = $hotMovies->pluck('id')->toArray();
        $excludedIds = array_merge($featuredMovieIds, $hotMovieIds);
        
        // Phim đang chiếu (exclude featured and hot movies)
        $nowShowingMovies = Phim::where('trang_thai', 'dang_chieu')
            ->whereNotIn('id', $excludedIds)
            ->orderByDesc('ngay_khoi_chieu')
            ->orderByDesc('diem_danh_gia')
            ->get();
        
        // Phim sắp chiếu (exclude featured movies that are sap_chieu)
        $upcomingMovies = Phim::where('trang_thai', 'sap_chieu')
            ->whereNotIn('id', $featuredMovieIds)
            ->orderBy('ngay_khoi_chieu')
            ->orderByDesc('diem_danh_gia')
            ->get();
        
        return view('home', compact('featuredMovies', 'nowShowingMovies', 'hotMovies', 'upcomingMovies'));
    }

    /**
     * Hiển thị tất cả phim theo category
     */
    public function category($category)
    {
        $query = Phim::query();
        $title = '';
        $description = '';
        $icon = '';
        $color = '';
        
        switch ($category) {
            case 'hot':
                $query->where('trang_thai', 'dang_chieu')
                    ->where('diem_danh_gia', '>=', 8.0)
                    ->orderByDesc('diem_danh_gia')
                    ->orderByDesc('so_luot_danh_gia');
                $title = 'Phim Hot';
                $description = 'Những bộ phim được đánh giá cao nhất';
                $icon = 'fa-fire';
                $color = '#FF784E';
                break;
                
            case 'now':
                $query->where('trang_thai', 'dang_chieu')
                    ->orderByDesc('ngay_khoi_chieu')
                    ->orderByDesc('diem_danh_gia');
                $title = 'Phim Đang Chiếu';
                $description = 'Đặt vé ngay để không bỏ lỡ';
                $icon = 'fa-video';
                $color = '#60a5fa';
                break;
                
            case 'coming':
                $query->where('trang_thai', 'sap_chieu')
                    ->orderBy('ngay_khoi_chieu')
                    ->orderByDesc('diem_danh_gia');
                $title = 'Phim Sắp Chiếu';
                $description = 'Sắp ra mắt - Đặt vé sớm để nhận ưu đãi';
                $icon = 'fa-calendar-alt';
                $color = '#a78bfa';
                break;
                
            default:
                abort(404);
        }
        
        $movies = $query->paginate(20);
        
        return view('movies.category', compact('movies', 'title', 'description', 'icon', 'color', 'category'));
    }

    /**
     * Display the movie listing page
     */
    public function list(Request $request)
    {
        $query = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }

        // Filter by genre
        if ($request->filled('genre')) {
            $query->where('the_loai', 'LIKE', "%{$request->genre}%");
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('quoc_gia', $request->country);
        }

        // Filter by age rating
        if ($request->filled('age')) {
            $query->where('do_tuoi', $request->age);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ten_phim', 'LIKE', "%{$search}%")
                  ->orWhere('dao_dien', 'LIKE', "%{$search}%")
                  ->orWhere('dien_vien', 'LIKE', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('ngay_khoi_chieu', 'asc');
                break;
            case 'rating':
                $query->orderBy('diem_danh_gia', 'desc');
                break;
            case 'name':
                $query->orderBy('ten_phim', 'asc');
                break;
            case 'newest':
            default:
                $query->orderByRaw("CASE WHEN trang_thai = 'dang_chieu' THEN 0 ELSE 1 END")
                      ->orderBy('ngay_khoi_chieu', 'desc');
                break;
        }

        $movies = $query->paginate(12)->appends($request->query());

        return view('movies.index', [
            'movies' => $movies,
            'title' => 'Tất cả phim',
            'description' => 'Danh sách tất cả các phim đang và sắp chiếu',
            'activeTab' => 'all'
        ]);
    }

    /**
     * Show now showing movies
     */
    public function nowShowing()
    {
        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->paginate(12);

        return view('movies.listing', [
            'movies' => $movies,
            'title' => 'Phim đang chiếu',
            'description' => 'Danh sách các phim đang được chiếu tại rạp',
            'activeTab' => 'now-showing'
        ]);
    }

    /**
     * Show coming soon movies
     */
    public function comingSoon()
    {
        $movies = Phim::where('trang_thai', 'sap_chieu')
            ->orderBy('ngay_khoi_chieu', 'asc')
            ->paginate(12);

        return view('movies.listing', [
            'movies' => $movies,
            'title' => 'Phim sắp chiếu',
            'description' => 'Các phim sắp được công chiếu trong thời gian tới',
            'activeTab' => 'coming-soon'
        ]);
    }

    /**
     * Show hot movies
     */
    public function hotMovies()
    {
        $movies = Phim::where('hot', true)
            ->whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->paginate(12);

        return view('movies.listing', [
            'movies' => $movies,
            'title' => 'Phim hot',
            'description' => 'Các phim đang được yêu thích nhất',
            'activeTab' => 'hot'
        ]);
    }

    /**
     * Show movies by genre
     */
    public function byGenre($genre)
    {
        // Decode the URL-encoded genre name
        $genreName = urldecode($genre);
        
        // Get all unique genres for the filter sidebar
        $allGenres = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->whereNotNull('the_loai')
            ->where('the_loai', '!=', '')
            ->pluck('the_loai')
            ->flatMap(function($item) {
                return array_map('trim', explode(',', $item));
            })
            ->unique()
            ->sort()
            ->values();

        // Get movies that match the selected genre
        $movies = Phim::where(function($query) use ($genreName) {
                $query->where('the_loai', 'LIKE', "%{$genreName}%");
            })
            ->whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->paginate(12);

        return view('movies.listing', [
            'movies' => $movies,
            'title' => 'Thể loại: ' . $genreName,
            'description' => 'Danh sách phim thể loại ' . $genreName,
            'activeTab' => 'genre',
            'currentGenre' => $genreName,
            'genres' => $allGenres
        ]);
    }

    /**
     * Show movie showtimes for all movies
     */
    public function showtimes(Request $request)
    {
        // Get the selected date or default to today
        $selectedDate = $request->has('date') 
            ? \Carbon\Carbon::parse($request->date)
            : now();
            
        // Format the selected date for display
        $formattedSelectedDate = $selectedDate->format('Y-m-d');
        
        // Get all active showtimes for the selected date
        $startOfDay = $selectedDate->copy()->startOfDay();
        $endOfDay = $selectedDate->copy()->endOfDay();
        $isToday = $selectedDate->isToday();
        
        // Get all unique genres from movies that have showtimes
        $genres = Phim::where('trang_thai', 'dang_chieu')
            ->whereHas('suatChieu', function($query) use ($startOfDay, $endOfDay, $isToday) {
                $query->where('thoi_gian_bat_dau', '>=', $startOfDay)
                    ->where('thoi_gian_bat_dau', '<=', $endOfDay)
                    ->where('trang_thai', 1);
                // For today: only show showtimes that haven't ended yet
                if ($isToday) {
                    $query->where('thoi_gian_ket_thuc', '>', now());
                }
            })
            ->select('the_loai')
            ->distinct()
            ->pluck('the_loai')
            ->filter()
            ->sort()
            ->values();

        // Get selected genre from request
        $selectedGenre = $request->input('genre');
        
        // Get movies that have showtimes in the selected date range
        // Only show showtimes that haven't ended yet (for all dates, not just today)
        $movies = Phim::with(['suatChieu' => function($query) use ($startOfDay, $endOfDay) {
                $query->where('thoi_gian_bat_dau', '>=', $startOfDay)
                    ->where('thoi_gian_bat_dau', '<=', $endOfDay)
                    ->where('thoi_gian_ket_thuc', '>', now()) // Only showtimes that haven't ended
                    ->where('trang_thai', 1); // Only active showtimes
                $query->orderBy('thoi_gian_bat_dau');
            }])
            ->whereHas('suatChieu', function($query) use ($startOfDay, $endOfDay) {
                $query->where('thoi_gian_bat_dau', '>=', $startOfDay)
                    ->where('thoi_gian_bat_dau', '<=', $endOfDay)
                    ->where('thoi_gian_ket_thuc', '>', now()) // Only showtimes that haven't ended
                    ->where('trang_thai', 1); // Only active showtimes
            })
            ->when($selectedGenre, function($query) use ($selectedGenre) {
                return $query->where('the_loai', $selectedGenre);
            })
            ->where('trang_thai', 'dang_chieu') // Only showing movies that are currently playing
            ->orderBy('ten_phim')
            ->get()
            ->filter(function($movie) {
                // Filter out movies that have no showtimes after the with() eager load
                return $movie->suatChieu->isNotEmpty();
            });

        // Generate dates for the next 7 days
        $dates = [];
        $today = now();
        
        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->addDays($i);
            $dateString = $date->format('Y-m-d');
            $dates[] = [
                'date' => $dateString,
                'day' => $date->format('d/m'),
                'weekday' => $this->getWeekdayName($date->dayOfWeek),
                'is_today' => $date->isToday(),
                'is_selected' => $dateString === $formattedSelectedDate
            ];
        }

        // Get all rooms for filter
        $rooms = PhongChieu::orderBy('ten_phong')->get();

        return view('movies.showtimes', [
            'movies' => $movies,
            'dates' => $dates,
            'rooms' => $rooms,
            'activeDate' => now()->format('Y-m-d'),
            'title' => 'Lịch chiếu phim',
            'description' => 'Xem lịch chiếu phim mới nhất tại rạp của chúng tôi',
            'activeTab' => 'showtimes'
        ]);
    }

    /**
     * Get weekday name in Vietnamese
     */
    private function getWeekdayName($dayOfWeek)
    {
        $days = [
            0 => 'CN',
            1 => 'T2',
            2 => 'T3',
            3 => 'T4',
            4 => 'T5',
            5 => 'T6',
            6 => 'T7',
        ];

        return $days[$dayOfWeek] ?? '';
    }

    /**
     * Admin index for movies list page
     */
    public function adminIndex(Request $request)
    {
        $query = Phim::query();

        // Status filter
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->string('status'));
        }

        // Search filter
        if ($request->filled('search')) {
            $s = trim($request->string('search'));
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

        // Additional filters: Diễn viên, Thể loại, Quốc gia
        if ($request->filled('dien_vien')) {
            $qActor = trim((string) $request->dien_vien);
            $query->where('dien_vien', 'like', "%{$qActor}%");
        }
        if ($request->filled('the_loai')) {
            $qGenre = trim((string) $request->the_loai);
            $query->where('the_loai', 'like', "%{$qGenre}%");
        }
        if ($request->filled('quoc_gia')) {
            $qCountry = trim((string) $request->quoc_gia);
            $query->where('quoc_gia', 'like', "%{$qCountry}%");
        }

        $movies = $query->orderByDesc('created_at')->paginate(12);

        // Quick stats
        $totalMovies = (int) Phim::count();
        $nowShowing = (int) Phim::where('trang_thai', 'dang_chieu')->count();
        $upcoming = (int) Phim::where('trang_thai', 'sap_chieu')->count();
        $ended = (int) Phim::where('trang_thai', 'ngung_chieu')->count();

        return view('admin.movies.index', compact('movies', 'totalMovies', 'nowShowing', 'upcoming', 'ended'));
    }

    /**
     * Show the form for creating a new movie
     * Only Admin can access
     */
    public function create()
    {
        return view('admin.movies.create');
    }

    /**
     * Store a newly created movie
     * Only Admin can access
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ten_phim' => 'required|string|max:255',
            'ten_goc' => 'nullable|string|max:255',
            'do_dai' => 'required|integer|min:1|max:600',
            'poster' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'mo_ta' => 'required|string|min:10|max:2000',
            'dao_dien' => 'required|string|max:100',
            'dien_vien' => 'required|string|max:500',
            'the_loai' => 'nullable|string|max:255',
            'quoc_gia' => 'nullable|string|max:100',
            'ngon_ngu' => 'nullable|string|max:100',
            'do_tuoi' => 'nullable|string|max:10',
            'ngay_khoi_chieu' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_khoi_chieu',
            'trailer' => 'nullable|url|max:500',
            'trang_thai' => 'required|in:sap_chieu,dang_chieu,ngung_chieu',
        ], [
            'ten_phim.required' => 'Tên phim không được để trống.',
            'ten_phim.max' => 'Tên phim không được vượt quá 255 ký tự.',
            'do_dai.required' => 'Độ dài phim không được để trống.',
            'do_dai.min' => 'Độ dài phim phải lớn hơn 0 phút.',
            'do_dai.max' => 'Độ dài phim không được vượt quá 600 phút.',
            'poster.required' => 'Vui lòng upload poster cho phim (bắt buộc).',
            'poster.image' => 'File poster phải là hình ảnh.',
            'poster.mimes' => 'Poster phải có định dạng: jpeg, png, jpg, gif, webp.',
            'poster.max' => 'Kích thước poster không được vượt quá 5MB.',
            'mo_ta.required' => 'Mô tả không được để trống.',
            'mo_ta.min' => 'Mô tả phải có ít nhất 10 ký tự.',
            'mo_ta.max' => 'Mô tả không được vượt quá 2000 ký tự.',
            'dao_dien.required' => 'Tên đạo diễn không được để trống.',
            'dao_dien.max' => 'Tên đạo diễn không được vượt quá 100 ký tự.',
            'dien_vien.required' => 'Danh sách diễn viên không được để trống.',
            'dien_vien.max' => 'Danh sách diễn viên không được vượt quá 500 ký tự.',
            'trailer.url' => 'Link trailer phải là URL hợp lệ.',
            'trailer.max' => 'Link trailer không được vượt quá 500 ký tự.',
            'trang_thai.required' => 'Trạng thái phim không được để trống.',
            'trang_thai.in' => 'Trạng thái phim không hợp lệ.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày khởi chiếu.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Vui lòng kiểm tra lại thông tin nhập vào.');
        }

        try {
            $data = $request->all();
            
            // Handle poster upload
            if ($request->hasFile('poster')) {
                $file = $request->file('poster');
                $filename = time() . '_' . $file->getClientOriginalName();
                $posterPath = $file->storeAs('posters', $filename, 'public');
                $data['poster'] = $posterPath;
            }

            // Set default values
            $data['diem_danh_gia'] = 0;
            $data['so_luot_danh_gia'] = 0;

            $movie = Phim::create($data);

            return redirect()->route('admin.movies.index')
                ->with('success', 'Thêm phim "' . $movie->ten_phim . '" thành công!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi thêm phim. Vui lòng thử lại.');
        }
    }

    
    public function show(Phim $movie)
    {
        \Log::info('=== MovieController::show START ===', [
            'movie_id' => $movie->id,
            'movie_name' => $movie->ten_phim,
        ]);

        $movie->load(['suatChieu.phongChieu']);
        
        if (request()->routeIs('movie-detail') || request()->routeIs('movies.show')) {
            // Group showtimes by date
            $showtimesByDate = [];
            $selectedDate = request()->get('date', now()->format('Y-m-d'));
            $today = now()->format('Y-m-d');
            
            \Log::info('MovieController::show - Query showtimes', [
                'movie_id' => $movie->id,
                'selected_date' => $selectedDate,
                'today' => $today,
            ]);
            
            // Lấy showtimes từ hôm nay trở đi (trong 7 ngày tới)
            // Bao gồm cả suất chiếu chưa kết thúc (cả hôm nay và tương lai)
            $showtimes = SuatChieu::where('id_phim', $movie->id)
                ->where('trang_thai', 1)
                ->where('thoi_gian_ket_thuc', '>', now()) // Show all showtimes that haven't ended yet
                ->where('thoi_gian_bat_dau', '<=', now()->addDays(7)) // Limit to next 7 days
                ->with(['phongChieu'])
                ->orderBy('thoi_gian_bat_dau')
                ->get();

            \Log::info('MovieController::show - Showtimes found', [
                'count' => $showtimes->count(),
                'showtimes' => $showtimes->map(function($st) {
                    return [
                        'id' => $st->id,
                        'start' => $st->thoi_gian_bat_dau->format('Y-m-d H:i:s'),
                        'end' => $st->thoi_gian_ket_thuc->format('Y-m-d H:i:s'),
                    ];
                })->toArray(),
            ]);
            
            foreach ($showtimes as $showtime) {
                $date = $showtime->thoi_gian_bat_dau->format('Y-m-d');
                if (!isset($showtimesByDate[$date])) {
                    $showtimesByDate[$date] = [];
                }
                $showtimesByDate[$date][] = $showtime;
            }
            
            // Get available dates (limit to 7 days)
            $availableDates = array_slice(array_keys($showtimesByDate), 0, 7);
            sort($availableDates);
            
            // Nếu selectedDate không có trong availableDates, chọn ngày đầu tiên
            if (!in_array($selectedDate, $availableDates) && !empty($availableDates)) {
                $selectedDate = $availableDates[0];
            }
            
            \Log::info('MovieController::show - Final result', [
                'available_dates' => $availableDates,
                'selected_date' => $selectedDate,
                'showtimes_by_date_count' => count($showtimesByDate),
                'showtimes_for_selected_date' => isset($showtimesByDate[$selectedDate]) ? count($showtimesByDate[$selectedDate]) : 0,
            ]);
            
            return view('movie-detail', compact('movie', 'showtimesByDate', 'selectedDate', 'availableDates'));
        }

        // Admin detail: support date-based filtering and quick stats
        $dateParam = request()->query('date');
        try {
            $selectedDate = $dateParam ? Carbon::parse($dateParam)->startOfDay() : Carbon::today();
        } catch (\Throwable $e) {
            $selectedDate = Carbon::today();
        }

        // Always render tabs as a fixed window starting from today
        $baseDate = Carbon::today();
        $days = collect(range(0, 6))->map(function ($i) use ($baseDate) {
            return $baseDate->copy()->addDays($i);
        });

        $suatChieu = SuatChieu::with(['phongChieu'])
            ->where('id_phim', $movie->id)
            ->whereDate('thoi_gian_bat_dau', $selectedDate)
            ->orderBy('thoi_gian_bat_dau')
            ->get();

        $doanhThu = $movie->calculateDoanhThu();
        $loiNhuan = $movie->calculateLoiNhuan();

        // Lấy thống kê chi tiết
        $statisticsService = new \App\Services\MovieStatisticsService();
        $statistics = $statisticsService->getMovieStatistics($movie->id, 'all');

        return view('admin.movies.show', [
            'movie' => $movie,
            'selectedDate' => $selectedDate,
            'days' => $days,
            'suatChieu' => $suatChieu,
            'doanhThu' => $doanhThu,
            'loiNhuan' => $loiNhuan,
            'statistics' => $statistics,
        ]);
    }

    
    public function edit(Phim $movie)
    {
        return view('admin.movies.edit', compact('movie'));
    }

    
    public function update(Request $request, Phim $movie)
    {
        $validator = Validator::make($request->all(), [
            'ten_phim' => 'required|string|max:255',
            'ten_goc' => 'nullable|string|max:255',
            'do_dai' => 'required|integer|min:1|max:600',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'mo_ta' => 'required|string|min:10|max:2000',
            'dao_dien' => 'required|string|max:100',
            'dien_vien' => 'required|string|max:500',
            'the_loai' => 'nullable|string|max:255',
            'quoc_gia' => 'nullable|string|max:100',
            'ngon_ngu' => 'nullable|string|max:100',
            'do_tuoi' => 'nullable|string|max:10',
            'ngay_khoi_chieu' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_khoi_chieu',
            'trailer' => 'nullable|url|max:500',
            'trang_thai' => 'required|in:sap_chieu,dang_chieu,ngung_chieu',
        ], [
            'ten_phim.required' => 'Tên phim không được để trống.',
            'ten_phim.max' => 'Tên phim không được vượt quá 255 ký tự.',
            'do_dai.required' => 'Độ dài phim không được để trống.',
            'do_dai.min' => 'Độ dài phim phải lớn hơn 0 phút.',
            'do_dai.max' => 'Độ dài phim không được vượt quá 600 phút.',
            'poster.image' => 'File poster phải là hình ảnh.',
            'poster.mimes' => 'Poster phải có định dạng: jpeg, png, jpg, gif, webp.',
            'poster.max' => 'Kích thước poster không được vượt quá 5MB.',
            'mo_ta.required' => 'Mô tả không được để trống.',
            'mo_ta.min' => 'Mô tả phải có ít nhất 10 ký tự.',
            'mo_ta.max' => 'Mô tả không được vượt quá 2000 ký tự.',
            'dao_dien.required' => 'Tên đạo diễn không được để trống.',
            'dao_dien.max' => 'Tên đạo diễn không được vượt quá 100 ký tự.',
            'dien_vien.required' => 'Danh sách diễn viên không được để trống.',
            'dien_vien.max' => 'Danh sách diễn viên không được vượt quá 500 ký tự.',
            'trailer.url' => 'Link trailer phải là URL hợp lệ.',
            'trailer.max' => 'Link trailer không được vượt quá 500 ký tự.',
            'trang_thai.required' => 'Trạng thái phim không được để trống.',
            'trang_thai.in' => 'Trạng thái phim không hợp lệ.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày khởi chiếu.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Handle poster upload
        if ($request->hasFile('poster')) {
            // Delete old poster if exists
            if ($movie->poster && Storage::disk('public')->exists($movie->poster)) {
                Storage::disk('public')->delete($movie->poster);
            }
            
            $file = $request->file('poster');
            $filename = time() . '_' . $file->getClientOriginalName();
            $posterPath = $file->storeAs('posters', $filename, 'public');
            $data['poster'] = $posterPath;
        }

        $movie->update($data);

        return redirect()->route('admin.movies.index')
            ->with('success', 'Cập nhật phim thành công!');
    }

    /**
     * Remove the specified movie
     * Only Admin can access
     */
    public function destroy(Phim $movie)
    {
        try {
            // Không cho phép xóa nếu còn suất chiếu trong tương lai (bao gồm hôm nay)
            $hasFutureShowtimes = SuatChieu::where('id_phim', $movie->id)
                ->where('thoi_gian_ket_thuc', '>', now()) // Only showtimes that haven't ended
                ->where('trang_thai', 1)
                ->exists();

            if ($hasFutureShowtimes) {
                return redirect()->route('admin.movies.index')
                    ->with('error', 'Không thể xóa phim vì vẫn còn suất chiếu trong tương lai. Vui lòng hủy hoặc cập nhật các suất chiếu trước.');
            }

            // Delete all related showtimes and their bookings
            $showtimes = SuatChieu::where('id_phim', $movie->id)->get();
            
            foreach ($showtimes as $showtime) {
                // Delete all bookings for this showtime
                $bookings = \App\Models\DatVe::where('id_suat_chieu', $showtime->id)->get();
                
                foreach ($bookings as $booking) {
                    // Delete booking details (seats)
                    \App\Models\ChiTietDatVe::where('id_dat_ve', $booking->id)->delete();
                    
                    // Delete combo details
                    \App\Models\ChiTietCombo::where('id_dat_ve', $booking->id)->delete();
                    
                    // Delete payment records
                    \App\Models\ThanhToan::where('id_dat_ve', $booking->id)->delete();
                    
                    // Delete the booking
                    $booking->delete();
                }
                
                // Delete the showtime
                $showtime->delete();
            }
            
            // Delete poster file if exists
            if ($movie->poster && Storage::disk('public')->exists($movie->poster)) {
                Storage::disk('public')->delete($movie->poster);
            }

            $movie->delete();

            return redirect()->route('admin.movies.index')
                ->with('success', 'Xóa phim và tất cả dữ liệu liên quan thành công!');
        } catch (\Exception $e) {
            Log::error('Error deleting movie: ' . $e->getMessage());
            return redirect()->route('admin.movies.index')
                ->with('error', 'Có lỗi xảy ra khi xóa phim!');
        }
    }

    /**
     * Toggle movie status
     * Only Admin can access
     */
    public function toggleStatus(Phim $movie)
    {
        $statusMap = [
            'sap_chieu' => 'dang_chieu',
            'dang_chieu' => 'ngung_chieu',
            'ngung_chieu' => 'sap_chieu'
        ];
        
        $newStatus = $statusMap[$movie->trang_thai] ?? 'sap_chieu';
        $movie->update(['trang_thai' => $newStatus]);
        
        $statusText = [
    'sap_chieu' => 'sắp chiếu',
    'dang_chieu' => 'đang chiếu',
    'ngung_chieu' => 'ngừng chiếu'
];

$displayText = $statusText[$newStatus] ?? 'không xác định';

return redirect()->back()
    ->with('success', "Đã cập nhật trạng thái phim thành '{$displayText}'!");
    }

    /**
     * API: Get movies list (basic data)
     */
    public function getMovies(Request $request)
    {
        $query = Phim::query();
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->string('status'));
        }
        $movies = $query->orderByDesc('ngay_khoi_chieu')->limit(50)->get();
        return response()->json($movies);
    }

    /**
     * API: Featured movies (dang_chieu first, then sap_chieu)
     */
    public function getFeaturedMovies()
    {
        $movies = Phim::orderByRaw("FIELD(trang_thai, 'dang_chieu','sap_chieu','ngung_chieu')")
            ->orderByDesc('diem_danh_gia')
            ->limit(12)
            ->get();
        return response()->json($movies);
    }

    /**
     * API: Search movies by keyword
     */
    public function search(Request $request)
    {
        $q = trim($request->string('q'));
        if ($q === '') {
            return response()->json([]);
        }
        $movies = Phim::where('ten_phim', 'like', "%{$q}%")
            ->orWhere('ten_goc', 'like', "%{$q}%")
            ->orWhere('the_loai', 'like', "%{$q}%")
            ->orderByDesc('ngay_khoi_chieu')
            ->limit(20)
            ->get();
        return response()->json($movies);
    }

    /**
     * API: Get showtimes by movie id
     */
    public function getSuatChieu($movieId)
    {
        $suatChieu = SuatChieu::with(['phongChieu'])
            ->where('id_phim', $movieId)
            ->orderBy('thoi_gian_bat_dau')
            ->get();
        return response()->json($suatChieu);
    }

    /**
     * API: Get rooms list
     */
    public function getPhongChieu()
    {
        $rooms = PhongChieu::orderBy('ten_phong')->get();
        return response()->json($rooms);
    }
}
