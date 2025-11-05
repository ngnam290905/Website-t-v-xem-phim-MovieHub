<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use App\Models\SuatChieu;
use App\Models\PhongChieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class MovieController extends Controller
{
    /**
     * Hiển thị danh sách phim (trang chủ)
     */
    public function index()
    {
        $movies = Phim::orderByDesc('ngay_khoi_chieu')->get();
        return view('home', compact('movies'));
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
        $movie->load(['suatChieu.phongChieu']);
        if (request()->routeIs('movie-detail')) {
            return view('movie-detail', compact('movie'));
        }

        // Admin detail: support date-based filtering and quick stats
        $dateParam = request()->query('date');
        try {
            $selectedDate = $dateParam ? Carbon::parse($dateParam)->startOfDay() : Carbon::today();
        } catch (\Throwable $e) {
            $selectedDate = Carbon::today();
        }

        $days = collect(range(0, 6))->map(function ($i) use ($selectedDate) {
            return $selectedDate->copy()->startOfDay()->addDays($i);
        });

        $suatChieu = SuatChieu::with(['phongChieu'])
            ->where('id_phim', $movie->id)
            ->whereDate('thoi_gian_bat_dau', $selectedDate)
            ->orderBy('thoi_gian_bat_dau')
            ->get();

        $doanhThu = $movie->calculateDoanhThu();
        $loiNhuan = $movie->calculateLoiNhuan();

        return view('admin.movies.show', [
            'movie' => $movie,
            'selectedDate' => $selectedDate,
            'days' => $days,
            'suatChieu' => $suatChieu,
            'doanhThu' => $doanhThu,
            'loiNhuan' => $loiNhuan,
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
        // Delete poster file if exists
        if ($movie->poster && Storage::disk('public')->exists($movie->poster)) {
            Storage::disk('public')->delete($movie->poster);
        }

        $movie->delete();

        return redirect()->route('admin.movies.index')
            ->with('success', 'Xóa phim thành công!');
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
