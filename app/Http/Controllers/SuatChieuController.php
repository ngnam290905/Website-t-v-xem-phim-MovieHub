<?php

namespace App\Http\Controllers;

use App\Models\SuatChieu;
use App\Models\Movie;
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
                $q->where('status', 'active');
            });
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('phim', function($q) use ($search) {
                $q->where('ten_phim', 'like', "%{$search}%");
            })->orWhereHas('phongChieu', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        
        // Filter by movie
        if ($request->filled('phim_id')) {
            $query->where('movie_id', $request->phim_id);
        }
        
        // Filter by room
        if ($request->filled('phong_id')) {
            $query->where('room_id', $request->phong_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('tu_ngay')) {
            $query->whereDate('start_time', '>=', $request->tu_ngay);
        }
        
        if ($request->filled('den_ngay')) {
            $query->whereDate('start_time', '<=', $request->den_ngay);
        }
        
        // Sort functionality
        $sortBy = $request->get('sort_by', 'start_time');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['start_time', 'end_time', 'status'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('start_time', 'desc');
        }
        
        $perPage = $request->get('per_page', 10);
        $suatChieu = $query->paginate($perPage)->appends($request->query());
        
        // Get filter options
        $phim = Movie::where('trang_thai', 1)->get();
        $phongChieu = PhongChieu::where('status', 'active')->get();
        
        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.suat-chieu.index', compact('suatChieu', 'phim', 'phongChieu'));
        }
        
        return view('admin.suat-chieu.index', compact('suatChieu', 'phim', 'phongChieu'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $phim = Movie::where('trang_thai', 1)->get();
        $phongChieu = PhongChieu::where('status', 'active')->get();
        
        return view('admin.suat-chieu.create', compact('phim', 'phongChieu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:phim,id',
            'room_id' => 'required|exists:phong_chieu,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        // Check for time conflicts
        $conflict = SuatChieu::where('room_id', $request->room_id)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                      });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'Thời gian này đã bị trùng với suất chiếu khác trong cùng phòng.']);
        }

        SuatChieu::create($request->all());

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
        $phim = Movie::where('trang_thai', 1)->get();
        $phongChieu = PhongChieu::where('status', 'active')->get();
        
        return view('admin.suat-chieu.edit', compact('suatChieu', 'phim', 'phongChieu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuatChieu $suatChieu)
    {
        $request->validate([
            'movie_id' => 'required|exists:phim,id',
            'room_id' => 'required|exists:phong_chieu,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'string|in:coming,ongoing,finished'
        ]);

        // Check for time conflicts (excluding current suat chieu)
        $conflict = SuatChieu::where('room_id', $request->room_id)
            ->where('id', '!=', $suatChieu->id)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                      });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'Thời gian này đã bị trùng với suất chiếu khác trong cùng phòng.']);
        }

        $suatChieu->update($request->all());

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
            ->whereDate('start_time', $request->ngay)
            ->where('status', 'coming')
            ->orderBy('start_time')
            ->get();

        return response()->json($suatChieu);
    }

    /**
     * Duplicate a suat chieu
     */
    public function duplicate(SuatChieu $suatChieu)
    {
        try {
            $newSuatChieu = $suatChieu->replicate();
            $newSuatChieu->start_time = now()->addDay(); // Set to tomorrow
            $newSuatChieu->end_time = now()->addDay()->addMinutes($suatChieu->phim->thoi_luong ?? 120);
            $newSuatChieu->status = 'coming';
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
