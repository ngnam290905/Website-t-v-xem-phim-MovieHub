<?php

namespace App\Http\Controllers;

use App\Models\SuatChieu;
use App\Models\Phim;
use App\Models\PhongChieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuatChieuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SuatChieu::with(['phim', 'phongChieu'])
            ->whereHas('phongChieu', function($q) {
                $q->where('trang_thai', 1);
            });
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('phim', function($q) use ($search) {
                $q->where('ten_phim', 'like', "%{$search}%");
            })->orWhereHas('phongChieu', function($q) use ($search) {
                $q->where('ten_phong', 'like', "%{$search}%");
            });
        }
        
        // Filter by movie
        if ($request->filled('phim_id')) {
            $query->where('id_phim', $request->phim_id);
        }
        
        // Filter by room
        if ($request->filled('phong_id')) {
            $query->where('id_phong', $request->phong_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            // Status filter sẽ dùng trang_thai = 1 (active)
            $query->where('trang_thai', 1);
        }
        
        // Filter by date range
        if ($request->filled('tu_ngay')) {
            $query->whereDate('thoi_gian_bat_dau', '>=', $request->tu_ngay);
        }
        
        if ($request->filled('den_ngay')) {
            $query->whereDate('thoi_gian_bat_dau', '<=', $request->den_ngay);
        }
        
        // Sort functionality
        $sortBy = $request->get('sort_by', 'thoi_gian_bat_dau');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Map sort_by to actual column names
        $columnMap = [
            'start_time' => 'thoi_gian_bat_dau',
            'end_time' => 'thoi_gian_ket_thuc',
            'status' => 'trang_thai'
        ];
        
        $actualColumn = $columnMap[$sortBy] ?? $sortBy;
        
        if (in_array($actualColumn, ['thoi_gian_bat_dau', 'thoi_gian_ket_thuc', 'trang_thai'])) {
            $query->orderBy($actualColumn, $sortOrder);
        } else {
            $query->orderBy('thoi_gian_bat_dau', 'desc');
        }
        
        $perPage = $request->get('per_page', 10);
        $suatChieu = $query->paginate($perPage)->appends($request->query());

        // Quick stats (global) - đếm tất cả suất chiếu đã tạo, không lọc theo điều kiện
        $now = now();
        // Tổng suất chiếu: đếm tất cả suất chiếu trong database (không có điều kiện lọc)
        // Đảm bảo bao gồm tất cả các suất chiếu đã được tạo
        $totalShowtimes = (int) SuatChieu::count();
        
        // Các thống kê khác: đếm theo trạng thái thời gian
        $comingCount = (int) SuatChieu::where('thoi_gian_bat_dau', '>', $now)->count();
        $ongoingCount = (int) SuatChieu::where('thoi_gian_bat_dau', '<=', $now)
            ->where('thoi_gian_ket_thuc', '>=', $now)->count();
        $finishedCount = (int) SuatChieu::where('thoi_gian_ket_thuc', '<', $now)->count();
        $todayCount = (int) SuatChieu::whereDate('thoi_gian_bat_dau', $now->toDateString())->count();
        
        // Get filter options
        $phim = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])->get();
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        
        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.suat-chieu.index', compact('suatChieu', 'phim', 'phongChieu', 'totalShowtimes', 'comingCount', 'ongoingCount', 'finishedCount', 'todayCount'));
        }
        
        return view('admin.suat-chieu.index', compact('suatChieu', 'phim', 'phongChieu', 'totalShowtimes', 'comingCount', 'ongoingCount', 'finishedCount', 'todayCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $phim = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])->get();
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        
        return view('admin.suat-chieu.create', compact('phim', 'phongChieu'));
    }

    /**
     * Standalone auto-schedule page
     */
    public function auto()
    {
        $phim = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])->get();
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        return view('admin.suat-chieu.auto', compact('phim', 'phongChieu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Support legacy field names from form: id_phim, id_phong
        if ($request->has('id_phim') && !$request->has('movie_id')) {
            $request->merge(['movie_id' => $request->input('id_phim')]);
        }
        if ($request->has('id_phong') && !$request->has('room_id')) {
            $request->merge(['room_id' => $request->input('id_phong')]);
        }
        $request->validate([
            'movie_id' => 'required|exists:phim,id',
            'room_id' => 'required|exists:phong_chieu,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        // Kiểm tra giờ hoạt động: 8:00 - 24:00
        $startTime = \Carbon\Carbon::parse($request->start_time);
        $endTime = \Carbon\Carbon::parse($request->end_time);
        $now = \Carbon\Carbon::now();
        
        // Kiểm tra start_time không được trong quá khứ
        if ($startTime->lte($now)) {
            return back()->withErrors(['start_time' => 'Không thể tạo suất chiếu vào thời gian quá khứ.'])->withInput();
        }
        
        // Kiểm tra end_time không được trong quá khứ
        if ($endTime->lte($now)) {
            return back()->withErrors(['end_time' => 'Không thể tạo suất chiếu kết thúc trong quá khứ.'])->withInput();
        }
        $startHour = $startTime->hour;
        $endHour = $endTime->hour;
        $endMinute = $endTime->minute;
        
        // Kiểm tra giờ bắt đầu: phải từ 8:00 đến trước 24:00 (tức là 0:00 - 23:59)
        // Nhưng chỉ cho phép từ 8:00 trở đi
        if ($startHour < 8 || $startHour >= 24) {
            return back()->withErrors(['start_time' => 'Rạp đang đóng cửa. Giờ hoạt động: 08:00–24:00.'])->withInput();
        }
        
        // Kiểm tra giờ kết thúc: 
        // - Nếu cùng ngày: phải từ 8:00 đến 24:00 (cho phép kết thúc đúng 00:00 ngày hôm sau)
        // - Nếu khác ngày: chỉ cho phép kết thúc đúng 00:00 (24:00)
        $isSameDay = $endTime->format('Y-m-d') == $startTime->format('Y-m-d');
        
        if ($isSameDay) {
            // Cùng ngày: giờ kết thúc phải từ 8:00 đến 24:00
            if ($endHour < 8 || ($endHour >= 24 && $endMinute > 0)) {
                return back()->withErrors(['end_time' => 'Rạp đang đóng cửa. Giờ hoạt động: 08:00–24:00.'])->withInput();
            }
        } else {
            // Khác ngày: chỉ cho phép kết thúc đúng 00:00 (24:00)
            if (!($endHour == 0 && $endMinute == 0)) {
                return back()->withErrors(['end_time' => 'Rạp đang đóng cửa. Giờ hoạt động: 08:00–24:00.'])->withInput();
            }
        }

        // Kiểm tra thời lượng suất chiếu phải >= thời lượng phim
        $phim = Phim::find($request->movie_id);
        if ($phim && $phim->do_dai) {
            $durationMinutes = $startTime->diffInMinutes($endTime);
            if ($durationMinutes < $phim->do_dai) {
                return back()->withErrors(['end_time' => "Thời gian suất chiếu ({$durationMinutes} phút) không thể nhỏ hơn thời lượng phim ({$phim->do_dai} phút)."])->withInput();
            }
        }

        // Kiểm tra xem phòng đã có suất chiếu trong cùng ngày chưa
        $startDate = $startTime->format('Y-m-d');
        $existingShowtimeInDay = SuatChieu::where('id_phong', $request->room_id)
            ->whereDate('thoi_gian_bat_dau', $startDate)
            ->with('phim')
            ->first();
        
        if ($existingShowtimeInDay) {
            $movieName = $existingShowtimeInDay->phim ? $existingShowtimeInDay->phim->ten_phim : 'phim khác';
            $existingStart = \Carbon\Carbon::parse($existingShowtimeInDay->thoi_gian_bat_dau);
            $existingEnd = \Carbon\Carbon::parse($existingShowtimeInDay->thoi_gian_ket_thuc);
            $existingTime = $existingStart->format('d/m/Y H:i') . ' - ' . $existingEnd->format('H:i');
            
            return back()->withErrors([
                'room_id' => "Phòng chiếu này đã có suất chiếu trong ngày ({$movieName} từ {$existingTime}). Không thể tạo suất chiếu mới vào phòng đã có suất chiếu trong cùng ngày."
            ])->withInput();
        }

        // Check for time conflicts - kiểm tra overlap giữa 2 khoảng thời gian
        // Trước tiên kiểm tra trùng hoàn toàn (cùng ngày, cùng phòng, cùng thời gian)
        $exactDuplicate = SuatChieu::where('id_phong', $request->room_id)
            ->where('thoi_gian_bat_dau', $request->start_time)
            ->where('thoi_gian_ket_thuc', $request->end_time)
            ->with('phim')
            ->first();
        
        if ($exactDuplicate) {
            $movieName = $exactDuplicate->phim ? $exactDuplicate->phim->ten_phim : 'phim khác';
            $conflictStart = \Carbon\Carbon::parse($exactDuplicate->thoi_gian_bat_dau);
            $conflictTime = $conflictStart->format('d/m/Y H:i') . ' - ' . \Carbon\Carbon::parse($exactDuplicate->thoi_gian_ket_thuc)->format('H:i');
            
            return back()->withErrors([
                'start_time' => "Đã tồn tại suất chiếu trùng hoàn toàn trong cùng phòng, cùng ngày, cùng thời gian ({$movieName} từ {$conflictTime}). Không thể tạo suất chiếu trùng."
            ])->withInput();
        }
        
        // Kiểm tra tất cả các trường hợp trùng (overlap):
        // 1. Suất mới nằm hoàn toàn trong suất cũ: oldStart <= newStart && oldEnd >= newEnd
        // 2. Suất mới bao trùm suất cũ: newStart <= oldStart && newEnd >= oldEnd
        // 3. Suất mới bắt đầu khi suất cũ chưa kết thúc: newStart < oldEnd && newStart >= oldStart
        // 4. Suất mới kết thúc khi suất cũ đã bắt đầu: newEnd > oldStart && newEnd <= oldEnd
        // 5. Hai suất chiếu chạm nhau: oldEnd == newStart || newEnd == oldStart
        // Logic tổng quát: Overlap nếu: oldStart <= newEnd && oldEnd >= newStart
        $conflict = SuatChieu::where('id_phong', $request->room_id)
            ->where('thoi_gian_bat_dau', '<=', $request->end_time)
            ->where('thoi_gian_ket_thuc', '>=', $request->start_time)
            ->exists();

        if ($conflict) {
            // Lấy thông tin suất chiếu bị trùng để hiển thị chi tiết
            $conflictingShowtime = SuatChieu::where('id_phong', $request->room_id)
                ->where('thoi_gian_bat_dau', '<=', $request->end_time)
                ->where('thoi_gian_ket_thuc', '>=', $request->start_time)
                ->with('phim')
                ->first();
            
            if ($conflictingShowtime) {
                $conflictStart = \Carbon\Carbon::parse($conflictingShowtime->thoi_gian_bat_dau);
                $conflictEnd = \Carbon\Carbon::parse($conflictingShowtime->thoi_gian_ket_thuc);
                $movieName = $conflictingShowtime->phim ? $conflictingShowtime->phim->ten_phim : 'phim khác';
                $conflictTime = $conflictStart->format('d/m/Y H:i') . ' - ' . $conflictEnd->format('H:i');
                
                return back()->withErrors([
                    'start_time' => "Thời gian này bị trùng với suất chiếu khác trong cùng phòng ({$movieName} từ {$conflictTime}). Không thể tạo suất chiếu trùng hoặc chạm nhau."
                ])->withInput();
            }
            
            return back()->withErrors(['start_time' => 'Thời gian này đã bị trùng với suất chiếu khác trong cùng phòng. Không thể tạo trùng suất chiếu trong cùng một ngày.'])->withInput();
        }

        SuatChieu::create([
            'id_phim' => $request->movie_id,
            'id_phong' => $request->room_id,
            'thoi_gian_bat_dau' => $request->start_time,
            'thoi_gian_ket_thuc' => $request->end_time,
            'trang_thai' => 1
        ]);

        return redirect()->route('admin.suat-chieu.index')
            ->with('success', 'Tạo suất chiếu thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SuatChieu $suatChieu)
    {
        $suatChieu->load(['movie', 'room', 'room.seats']);
        
        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.suat-chieu.show', compact('suatChieu'));
        }
        
        return view('admin.suat-chieu.show', compact('suatChieu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SuatChieu $suatChieu)
    {
        $phim = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])->get();
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        
        return view('admin.suat-chieu.edit', compact('suatChieu', 'phim', 'phongChieu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuatChieu $suatChieu)
    {
        // Support legacy field names from form: id_phim, id_phong
        if ($request->has('id_phim') && !$request->has('movie_id')) {
            $request->merge(['movie_id' => $request->input('id_phim')]);
        }
        if ($request->has('id_phong') && !$request->has('room_id')) {
            $request->merge(['room_id' => $request->input('id_phong')]);
        }
        $request->validate([
            'movie_id' => 'required|exists:phim,id',
            'room_id' => 'required|exists:phong_chieu,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'string|in:coming,ongoing,finished'
        ]);

        // Kiểm tra giờ hoạt động: 8:00 - 24:00
        $startTime = \Carbon\Carbon::parse($request->start_time);
        $endTime = \Carbon\Carbon::parse($request->end_time);
        $now = \Carbon\Carbon::now();
        
        // Kiểm tra start_time không được trong quá khứ
        if ($startTime->lte($now)) {
            return back()->withErrors(['start_time' => 'Không thể đặt suất chiếu vào thời gian quá khứ.'])->withInput();
        }
        
        // Kiểm tra end_time không được trong quá khứ
        if ($endTime->lte($now)) {
            return back()->withErrors(['end_time' => 'Không thể đặt suất chiếu kết thúc trong quá khứ.'])->withInput();
        }
        
        $startHour = $startTime->hour;
        $endHour = $endTime->hour;
        $endMinute = $endTime->minute;
        
        // Kiểm tra giờ bắt đầu: phải từ 8:00 đến trước 24:00
        if ($startHour < 8 || $startHour >= 24) {
            return back()->withErrors(['start_time' => 'Rạp đang đóng cửa. Giờ hoạt động: 08:00–24:00.'])->withInput();
        }
        
        // Kiểm tra giờ kết thúc
        $isSameDay = $endTime->format('Y-m-d') == $startTime->format('Y-m-d');
        
        if ($isSameDay) {
            // Cùng ngày: giờ kết thúc phải từ 8:00 đến 24:00
            if ($endHour < 8 || ($endHour >= 24 && $endMinute > 0)) {
                return back()->withErrors(['end_time' => 'Rạp đang đóng cửa. Giờ hoạt động: 08:00–24:00.'])->withInput();
            }
        } else {
            // Khác ngày: chỉ cho phép kết thúc đúng 00:00 (24:00)
            if (!($endHour == 0 && $endMinute == 0)) {
                return back()->withErrors(['end_time' => 'Rạp đang đóng cửa. Giờ hoạt động: 08:00–24:00.'])->withInput();
            }
        }

        // Kiểm tra thời lượng suất chiếu phải >= thời lượng phim
        $phim = Phim::find($request->movie_id);
        if ($phim && $phim->do_dai) {
            $durationMinutes = $startTime->diffInMinutes($endTime);
            if ($durationMinutes < $phim->do_dai) {
                return back()->withErrors(['end_time' => "Thời gian suất chiếu ({$durationMinutes} phút) không thể nhỏ hơn thời lượng phim ({$phim->do_dai} phút)."])->withInput();
            }
        }

        // Kiểm tra xem phòng đã có suất chiếu khác trong cùng ngày chưa (trừ suất chiếu hiện tại)
        $startDate = $startTime->format('Y-m-d');
        $existingShowtimeInDay = SuatChieu::where('id_phong', $request->room_id)
            ->where('id', '!=', $suatChieu->id)
            ->whereDate('thoi_gian_bat_dau', $startDate)
            ->with('phim')
            ->first();
        
        if ($existingShowtimeInDay) {
            $movieName = $existingShowtimeInDay->phim ? $existingShowtimeInDay->phim->ten_phim : 'phim khác';
            $existingStart = \Carbon\Carbon::parse($existingShowtimeInDay->thoi_gian_bat_dau);
            $existingEnd = \Carbon\Carbon::parse($existingShowtimeInDay->thoi_gian_ket_thuc);
            $existingTime = $existingStart->format('d/m/Y H:i') . ' - ' . $existingEnd->format('H:i');
            
            return back()->withErrors([
                'room_id' => "Phòng chiếu này đã có suất chiếu khác trong ngày ({$movieName} từ {$existingTime}). Không thể cập nhật suất chiếu vào phòng đã có suất chiếu khác trong cùng ngày."
            ])->withInput();
        }

        // Check for time conflicts (excluding current suat chieu)
        // Trước tiên kiểm tra trùng hoàn toàn (cùng ngày, cùng phòng, cùng thời gian)
        $exactDuplicate = SuatChieu::where('id_phong', $request->room_id)
            ->where('id', '!=', $suatChieu->id)
            ->where('thoi_gian_bat_dau', $request->start_time)
            ->where('thoi_gian_ket_thuc', $request->end_time)
            ->with('phim')
            ->first();
        
        if ($exactDuplicate) {
            $movieName = $exactDuplicate->phim ? $exactDuplicate->phim->ten_phim : 'phim khác';
            $conflictStart = \Carbon\Carbon::parse($exactDuplicate->thoi_gian_bat_dau);
            $conflictTime = $conflictStart->format('d/m/Y H:i') . ' - ' . \Carbon\Carbon::parse($exactDuplicate->thoi_gian_ket_thuc)->format('H:i');
            
            return back()->withErrors([
                'start_time' => "Đã tồn tại suất chiếu trùng hoàn toàn trong cùng phòng, cùng ngày, cùng thời gian ({$movieName} từ {$conflictTime}). Không thể cập nhật thành suất chiếu trùng."
            ])->withInput();
        }
        
        // Kiểm tra tất cả các trường hợp trùng (overlap), bao gồm cả chạm nhau
        // Overlap nếu: oldStart <= newEnd && oldEnd >= newStart
        $conflict = SuatChieu::where('id_phong', $request->room_id)
            ->where('id', '!=', $suatChieu->id)
            ->where('thoi_gian_bat_dau', '<=', $request->end_time)
            ->where('thoi_gian_ket_thuc', '>=', $request->start_time)
            ->exists();

        if ($conflict) {
            // Lấy thông tin suất chiếu bị trùng để hiển thị chi tiết
            $conflictingShowtime = SuatChieu::where('id_phong', $request->room_id)
                ->where('id', '!=', $suatChieu->id)
                ->where('thoi_gian_bat_dau', '<=', $request->end_time)
                ->where('thoi_gian_ket_thuc', '>=', $request->start_time)
                ->with('phim')
                ->first();
            
            if ($conflictingShowtime) {
                $conflictStart = \Carbon\Carbon::parse($conflictingShowtime->thoi_gian_bat_dau);
                $conflictEnd = \Carbon\Carbon::parse($conflictingShowtime->thoi_gian_ket_thuc);
                $movieName = $conflictingShowtime->phim ? $conflictingShowtime->phim->ten_phim : 'phim khác';
                $conflictTime = $conflictStart->format('d/m/Y H:i') . ' - ' . $conflictEnd->format('H:i');
                
                return back()->withErrors([
                    'start_time' => "Thời gian này bị trùng với suất chiếu khác trong cùng phòng ({$movieName} từ {$conflictTime}). Không thể tạo suất chiếu trùng hoặc chạm nhau."
                ])->withInput();
            }
            
            return back()->withErrors(['start_time' => 'Thời gian này đã bị trùng với suất chiếu khác trong cùng phòng. Không thể tạo trùng suất chiếu trong cùng một ngày.'])->withInput();
        }

        $suatChieu->update([
            'id_phim' => $request->movie_id,
            'id_phong' => $request->room_id,
            'thoi_gian_bat_dau' => $request->start_time,
            'thoi_gian_ket_thuc' => $request->end_time,
            'trang_thai' => 1
        ]);

        return redirect()->route('admin.suat-chieu.index')
            ->with('success', 'Cập nhật suất chiếu thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuatChieu $suatChieu)
    {
        // Check if there are any bookings for this suat chieu
        if ($suatChieu->datVe()->exists()) {
            return back()->withErrors(['error' => 'Không thể xóa suất chiếu đã có vé đặt.']);
        }

        $suatChieu->delete();

        return redirect()->route('admin.suat-chieu.index')
            ->with('success', 'Xóa suất chiếu thành công!');
    }

    /**
     * Update status of suat chieu (for staff)
     */
    public function updateStatus(Request $request, SuatChieu $suatChieu)
    {
        $request->validate([
            'status' => 'required|string|in:coming,ongoing,finished'
        ]);

        $suatChieu->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ]);
    }

    /**
     * Get suat chieu by movie and date
     */
    public function getByMovieAndDate(Request $request)
    {
        $request->validate([
            'id_phim' => 'required|exists:phim,id',
            'ngay' => 'required|date'
        ]);

        $suatChieu = SuatChieu::with(['phongChieu'])
            ->where('id_phim', $request->id_phim)
            ->whereDate('thoi_gian_bat_dau', $request->ngay)
            ->where('trang_thai', 1)
            ->orderBy('thoi_gian_bat_dau')
            ->get();

        return response()->json($suatChieu);
    }

    /**
     * Check if showtime conflicts with existing showtimes in database
     */
    public function checkConflict(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:phong_chieu,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
        ]);

        $startTime = \Carbon\Carbon::parse($request->start_time);
        $startDate = $startTime->format('Y-m-d');

        // Kiểm tra xem phòng đã có suất chiếu trong cùng ngày chưa
        $existingShowtimeInDay = SuatChieu::where('id_phong', $request->room_id)
            ->whereDate('thoi_gian_bat_dau', $startDate)
            ->with('phim')
            ->first();
        
        if ($existingShowtimeInDay) {
            $movieName = $existingShowtimeInDay->phim ? $existingShowtimeInDay->phim->ten_phim : 'phim khác';
            $existingStart = \Carbon\Carbon::parse($existingShowtimeInDay->thoi_gian_bat_dau);
            $existingEnd = \Carbon\Carbon::parse($existingShowtimeInDay->thoi_gian_ket_thuc);
            $existingTime = $existingStart->format('d/m/Y H:i') . ' - ' . $existingEnd->format('H:i');
            
            return response()->json([
                'has_conflict' => true,
                'conflict_type' => 'room_has_showtime',
                'movie_name' => $movieName,
                'conflict_time' => $existingTime,
                'message' => "Phòng chiếu đã có suất chiếu trong ngày ({$movieName} từ {$existingTime})"
            ]);
        }

        // Kiểm tra trùng hoàn toàn
        $exactDuplicate = SuatChieu::where('id_phong', $request->room_id)
            ->where('thoi_gian_bat_dau', $request->start_time)
            ->where('thoi_gian_ket_thuc', $request->end_time)
            ->with('phim')
            ->first();
        
        if ($exactDuplicate) {
            $movieName = $exactDuplicate->phim ? $exactDuplicate->phim->ten_phim : 'phim khác';
            $conflictStart = \Carbon\Carbon::parse($exactDuplicate->thoi_gian_bat_dau);
            $conflictTime = $conflictStart->format('d/m/Y H:i') . ' - ' . \Carbon\Carbon::parse($exactDuplicate->thoi_gian_ket_thuc)->format('H:i');
            
            return response()->json([
                'has_conflict' => true,
                'conflict_type' => 'exact_duplicate',
                'movie_name' => $movieName,
                'conflict_time' => $conflictTime,
                'message' => "Trùng hoàn toàn với suất chiếu ({$movieName} từ {$conflictTime})"
            ]);
        }

        // Kiểm tra overlap
        $conflictingShowtime = SuatChieu::where('id_phong', $request->room_id)
            ->where('thoi_gian_bat_dau', '<=', $request->end_time)
            ->where('thoi_gian_ket_thuc', '>=', $request->start_time)
            ->with('phim')
            ->first();

        if ($conflictingShowtime) {
            $conflictStart = \Carbon\Carbon::parse($conflictingShowtime->thoi_gian_bat_dau);
            $conflictEnd = \Carbon\Carbon::parse($conflictingShowtime->thoi_gian_ket_thuc);
            $movieName = $conflictingShowtime->phim ? $conflictingShowtime->phim->ten_phim : 'phim khác';
            $conflictTime = $conflictStart->format('d/m/Y H:i') . ' - ' . $conflictEnd->format('H:i');
            
            return response()->json([
                'has_conflict' => true,
                'conflict_type' => 'overlap',
                'movie_name' => $movieName,
                'conflict_time' => $conflictTime,
                'message' => "Bị trùng với suất chiếu ({$movieName} từ {$conflictTime})"
            ]);
        }

        return response()->json([
            'has_conflict' => false
        ]);
    }

    /**
     * Duplicate a suat chieu
     */
    public function duplicate(SuatChieu $suatChieu)
    {
        try {
            $newSuatChieu = $suatChieu->replicate();
            $newSuatChieu->thoi_gian_bat_dau = now()->addDay(); // Set to tomorrow
            $newSuatChieu->thoi_gian_ket_thuc = now()->addDay()->addMinutes($suatChieu->phim->do_dai ?? 120);
            $newSuatChieu->trang_thai = 1;
            $newSuatChieu->save();

            return response()->json([
                'success' => true,
                'message' => 'Suất chiếu đã được nhân bản thành công',
                'data' => $newSuatChieu
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi nhân bản suất chiếu: ' . $e->getMessage()
            ], 500);
        }
    }
}
