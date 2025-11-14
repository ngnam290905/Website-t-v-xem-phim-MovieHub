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

        // Quick stats (global)
        $now = now();
        $totalShowtimes = (int) SuatChieu::count();
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

        // Check for time conflicts
        $conflict = SuatChieu::where('id_phong', $request->room_id)
            ->where(function($query) use ($request) {
                $query->whereBetween('thoi_gian_bat_dau', [$request->start_time, $request->end_time])
                      ->orWhereBetween('thoi_gian_ket_thuc', [$request->start_time, $request->end_time])
                      ->orWhere(function($q) use ($request) {
                          $q->where('thoi_gian_bat_dau', '<=', $request->start_time)
                            ->where('thoi_gian_ket_thuc', '>=', $request->end_time);
                      });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'Thời gian này đã bị trùng với suất chiếu khác trong cùng phòng.']);
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

        // Check for time conflicts (excluding current suat chieu)
        $conflict = SuatChieu::where('id_phong', $request->room_id)
            ->where('id', '!=', $suatChieu->id)
            ->where(function($query) use ($request) {
                $query->whereBetween('thoi_gian_bat_dau', [$request->start_time, $request->end_time])
                      ->orWhereBetween('thoi_gian_ket_thuc', [$request->start_time, $request->end_time])
                      ->orWhere(function($q) use ($request) {
                          $q->where('thoi_gian_bat_dau', '<=', $request->start_time)
                            ->where('thoi_gian_ket_thuc', '>=', $request->end_time);
                      });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'Thời gian này đã bị trùng với suất chiếu khác trong cùng phòng.']);
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

    /**
     * Batch create suat chieu for auto generation
     */
    public function batchStore(Request $request)
    {
        $request->validate([
            'showtimes' => 'required|array|min:1|max:100',
            'showtimes.*.movie_id' => 'required|exists:phim,id',
            'showtimes.*.room_id' => 'required|exists:phong_chieu,id',
            'showtimes.*.start_time' => 'required|date',
            'showtimes.*.end_time' => 'required|date|after:showtimes.*.start_time',
        ]);

        $showtimes = $request->showtimes;
        $results = ['created' => 0, 'conflicts' => 0, 'errors' => []];

        // Group by room for efficient conflict checking
        $rooms = [];
        foreach ($showtimes as $index => $showtime) {
            $rooms[$showtime['room_id']][] = ['data' => $showtime, 'index' => $index];
        }

        DB::beginTransaction();
        try {
            foreach ($rooms as $roomId => $roomShowtimes) {
                // Get existing showtimes for this room in one query
                $timeRange = [
                    min(array_column(array_column($roomShowtimes, 'data'), 'start_time')),
                    max(array_column(array_column($roomShowtimes, 'data'), 'end_time'))
                ];
                
                $existingShowtimes = SuatChieu::where('id_phong', $roomId)
                    ->where(function($query) use ($timeRange) {
                        $query->whereBetween('thoi_gian_bat_dau', $timeRange)
                              ->orWhereBetween('thoi_gian_ket_thuc', $timeRange)
                              ->orWhere(function($q) use ($timeRange) {
                                  $q->where('thoi_gian_bat_dau', '<=', $timeRange[0])
                                    ->where('thoi_gian_ket_thuc', '>=', $timeRange[1]);
                              });
                    })
                    ->get(['thoi_gian_bat_dau', 'thoi_gian_ket_thuc']);

                // Check conflicts and prepare insert data
                $insertData = [];
                foreach ($roomShowtimes as $item) {
                    $showtime = $item['data'];
                    $index = $item['index'];
                    
                    $hasConflict = $existingShowtimes->contains(function($existing) use ($showtime) {
                        return !($showtime['end_time'] <= $existing->thoi_gian_bat_dau || 
                                $showtime['start_time'] >= $existing->thoi_gian_ket_thuc);
                    });

                    if ($hasConflict) {
                        $results['conflicts']++;
                        continue;
                    }

                    $insertData[] = [
                        'id_phim' => $showtime['movie_id'],
                        'id_phong' => $showtime['room_id'],
                        'thoi_gian_bat_dau' => $showtime['start_time'],
                        'thoi_gian_ket_thuc' => $showtime['end_time'],
                        'trang_thai' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                // Batch insert for this room
                if (!empty($insertData)) {
                    try {
                        SuatChieu::insert($insertData);
                        $results['created'] += count($insertData);
                    } catch (\Exception $e) {
                        $results['errors'][] = "Lỗi phòng {$roomId}: " . $e->getMessage();
                    }
                }
            }

            if (empty($results['errors'])) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => "Đã tạo thành công {$results['created']} suất. Trùng lịch: {$results['conflicts']}."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}
