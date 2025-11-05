<?php

namespace App\Http\Controllers;

use App\Models\PhongChieu;
use App\Models\Ghe;
use App\Models\LoaiGhe;
use App\Models\SuatChieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PhongChieuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PhongChieu::withCount(['seats', 'showtimes']);

        // Search by name (legacy column: ten_phong)
        if ($request->filled('search')) {
            $query->where('ten_phong', 'like', '%' . $request->search . '%');
        }

        // Filter by type (only if column exists)
        if ($request->filled('type')) {
            if (Schema::hasColumn('phong_chieu', 'type')) {
                $query->where('type', $request->type);
            }
        }

        // Filter by status (map to legacy trang_thai 1/0)
        if ($request->filled('status')) {
            $status = $request->status;
            $query->where('trang_thai', $status === 'active' ? 1 : 0);
        }

        // Sort (map UI field -> DB column)
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $columnMap = [
            'name' => 'ten_phong',
            'rows' => 'so_hang',
            'cols' => 'so_cot',
            'status' => 'trang_thai',
        ];
        $actual = $columnMap[$sortBy] ?? $sortBy;
        $query->orderBy($actual, $sortOrder);

        $phongChieu = $query->paginate(20);

        // Quick stats
        $totalRooms = (int) PhongChieu::count();
        $activeRooms = (int) PhongChieu::where('trang_thai', 1)->count();
        $pausedRooms = (int) PhongChieu::where('trang_thai', 0)->count();
        $showtimesToday = (int) SuatChieu::whereDate('thoi_gian_bat_dau', now()->toDateString())->count();

        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.phong-chieu.index', compact('phongChieu', 'totalRooms', 'activeRooms', 'pausedRooms', 'showtimesToday'));
        }
        
        return view('admin.phong-chieu.index', compact('phongChieu', 'totalRooms', 'activeRooms', 'pausedRooms', 'showtimesToday'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $loaiGhe = LoaiGhe::all();
        return view('admin.phong-chieu.create', compact('loaiGhe'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rows' => 'required|integer|min:1|max:20',
            'cols' => 'required|integer|min:1|max:30',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,inactive',
            // Advanced seat type blocks (optional)
            'seat_blocks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create room
            $phongChieu = PhongChieu::create([
                'name' => $request->name,
                'rows' => $request->rows,
                'cols' => $request->cols,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            // Generate seats (default type)
            $this->createSeatsForRoom($phongChieu, $request->rows, $request->cols, $request->get('seat_type', 'normal'));

            // Apply advanced seat type blocks, if provided
            if ($request->filled('seat_blocks')) {
                $blocksJson = $request->string('seat_blocks');
                try {
                    $blocks = json_decode((string) $blocksJson, true, 512, JSON_THROW_ON_ERROR);
                } catch (\Throwable $e) {
                    $blocks = [];
                }

                if (is_array($blocks)) {
                    foreach ($blocks as $block) {
                        // Expected keys: row_from, row_to, col_from, col_to, id_loai
                        $rowFrom = max(1, (int)($block['row_from'] ?? 0));
                        $rowTo   = max($rowFrom, (int)($block['row_to'] ?? 0));
                        $colFrom = max(1, (int)($block['col_from'] ?? 0));
                        $colTo   = max($colFrom, (int)($block['col_to'] ?? 0));
                        $typeId  = (int)($block['id_loai'] ?? 0);

                        if ($rowFrom <= 0 || $colFrom <= 0 || $typeId <= 0) {
                            continue;
                        }
                        // Clamp to room bounds
                        $rowTo = min($rowTo, (int)$request->rows);
                        $colTo = min($colTo, (int)$request->cols);

                        // Build seat codes for the rectangle and update in bulk
                        $codes = [];
                        for ($r = $rowFrom; $r <= $rowTo; $r++) {
                            $rowLabel = chr(64 + $r);
                            for ($c = $colFrom; $c <= $colTo; $c++) {
                                $codes[] = $rowLabel . $c;
                            }
                        }
                        if (!empty($codes)) {
                            Ghe::where('id_phong', $phongChieu->id)
                                ->whereIn('so_ghe', $codes)
                                ->update(['id_loai' => $typeId]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.phong-chieu.index')
                ->with('success', 'Phòng chiếu đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo phòng chiếu: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PhongChieu $phongChieu)
    {
        $phongChieu->load(['seats' => function($query) {
            $query->orderBy('so_hang')->orderBy('so_ghe');
        }, 'showtimes.movie']);
        $phongChieu->loadCount(['seats', 'showtimes']);

        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.phong-chieu.show', compact('phongChieu'));
        }
        
        return view('admin.phong-chieu.show', compact('phongChieu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PhongChieu $phongChieu)
    {
        $loaiGhe = LoaiGhe::all();
        // Load counts for UI display
        $phongChieu->loadCount('seats');
        return view('admin.phong-chieu.edit', compact('phongChieu', 'loaiGhe'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,inactive',
            // Optional fields depend on DB columns
            'type' => 'nullable|string|max:50',
            'audio_system' => 'nullable|string|max:100',
            'screen_type' => 'nullable|string|max:100',
        ]);

        $data = $request->only(['name','description']);
        // Map UI status string to legacy numeric column
        if ($request->filled('status')) {
            $data['trang_thai'] = $request->status === 'active' ? 1 : 0;
        }
        // Include optional columns only if they exist
        if (Schema::hasColumn('phong_chieu', 'type') && $request->filled('type')) {
            $data['type'] = $request->input('type');
        }
        if (Schema::hasColumn('phong_chieu', 'audio_system') && $request->filled('audio_system')) {
            $data['audio_system'] = $request->input('audio_system');
        }
        if (Schema::hasColumn('phong_chieu', 'screen_type') && $request->filled('screen_type')) {
            $data['screen_type'] = $request->input('screen_type');
        }

        $phongChieu->update($data);

        return redirect()->route('admin.phong-chieu.index')
            ->with('success', 'Phòng chiếu đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhongChieu $phongChieu)
    {
        // Check if room has upcoming showtimes
        $upcomingShowtimes = $phongChieu->showtimes()
            ->where('thoi_gian_bat_dau', '>', now())
            ->count();

        if ($upcomingShowtimes > 0) {
            return back()->withErrors(['error' => 'Không thể xóa phòng chiếu đang có suất chiếu sắp diễn ra!']);
        }

        DB::beginTransaction();
        try {
            // Delete all seats first
            $phongChieu->seats()->delete();
            // Delete past showtimes to avoid FK issues (no upcoming showtimes at this point)
            $phongChieu->showtimes()->delete();
            
            // Delete room
            $phongChieu->delete();

            DB::commit();

            return redirect()->route('admin.phong-chieu.index')
                ->with('success', 'Phòng chiếu đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa phòng chiếu: ' . $e->getMessage()]);
        }
    }

    /**
     * Update room status
     */
    public function updateStatus(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive'
        ]);

        // Map to legacy trang_thai 1/0
        $phongChieu->update(['trang_thai' => $request->status === 'active' ? 1 : 0]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái phòng chiếu thành công!'
        ]);
    }

    /**
     * Pre-check if room can be modified (pause/delete) based on future showtimes
     */
    public function canModify(PhongChieu $phongChieu)
    {
        $future = $phongChieu->showtimes()
            ->where('thoi_gian_bat_dau', '>', now())
            ->count();
        return response()->json([
            'success' => true,
            'future_showtimes' => (int)$future,
            'can_pause' => $future === 0,
            'can_delete' => $future === 0,
            'message' => $future > 0 ? 'Phòng chiếu đang có suất chiếu sắp diễn ra.' : 'Có thể thao tác.'
        ]);
    }

    /**
     * Get seats by room
     */
    public function getByRoom(Request $request, PhongChieu $phongChieu)
    {
        $seats = $phongChieu->seats()
            ->orderBy('so_hang')
            ->orderBy('so_ghe')
            ->get();

        return response()->json($seats);
    }

    /**
     * Generate seats for room
     */
    public function generateSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'rows' => 'required|integer|min:1|max:20',
            'cols' => 'required|integer|min:1|max:30',
            'seat_type' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            // Delete related records first
            $phongChieu->seats()->each(function($seat) {
                // Delete related booking details (if relation exists)
                if (method_exists($seat, 'bookingDetails')) {
                    $seat->bookingDetails()->delete();
                }
            });
            
            // Delete existing seats
            $phongChieu->seats()->delete();

            // Update room dimensions
            $phongChieu->update([
                'rows' => $request->rows,
                'cols' => $request->cols
            ]);

            // Generate new seats
            $this->createSeatsForRoom($phongChieu, $request->rows, $request->cols, $request->seat_type);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sơ đồ ghế đã được tạo lại thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Private method to generate seats
     */
    private function createSeatsForRoom(PhongChieu $phongChieu, $rows, $cols, $defaultType = 'normal')
    {
        $seatType = LoaiGhe::where('ten_loai', $defaultType)->first() ?: LoaiGhe::first();
        $seats = [];
        for ($row = 1; $row <= $rows; $row++) {
            for ($col = 1; $col <= $cols; $col++) {
                $seats[] = [
                    'id_phong' => $phongChieu->id,
                    'id_loai' => $seatType ? $seatType->id : null,
                    'so_hang' => $row,
                    'so_ghe' => chr(64 + $row) . $col, // ví dụ A1, A2...
                    'trang_thai' => 1,
                ];
            }
        }
        if (!empty($seats)) {
            Ghe::insert($seats);
        }
    }

    /**
     * Show seat management interface
     */
    public function manageSeats(PhongChieu $phongChieu)
    {
        $phongChieu->load(['seats.seatType', 'showtimes.movie']);
        $seatTypes = LoaiGhe::all();
        
        return view('admin.phong-chieu.manage-seats', compact('phongChieu', 'seatTypes'));
    }

    /**
     * Store a new seat
     */
    public function storeSeat(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'row_label' => 'required|string|max:1',
            'so_ghe' => 'required|integer|min:1',
            'id_loai' => 'required|exists:loai_ghe,id',
            'status' => 'required|in:available,booked,locked',
            'price' => 'nullable|numeric|min:0'
        ]);

        $seatType = LoaiGhe::find($request->id_loai);
        $row = strtoupper($request->row_label);
        $rowNumber = max(1, ord($row) - 64);
        $seat = $phongChieu->seats()->create([
            'id_loai' => $request->id_loai,
            'so_hang' => $rowNumber,
            'so_ghe' => $row . $request->so_ghe,
            'trang_thai' => $request->status === 'available' ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ghế đã được thêm thành công!',
            'seat' => $seat->load('seatType')
        ]);
    }

    /**
     * Update a seat
     */
    public function updateSeat(Request $request, PhongChieu $phongChieu, Ghe $ghe)
    {
        $request->validate([
            'row_label' => 'required|string|max:1',
            'so_ghe' => 'required|integer|min:1',
            'id_loai' => 'required|exists:loai_ghe,id',
            'status' => 'required|in:available,booked,locked',
            'price' => 'nullable|numeric|min:0'
        ]);

        $seatType = LoaiGhe::find($request->id_loai);
        $row = strtoupper($request->row_label);
        $rowNumber = max(1, ord($row) - 64);
        $ghe->update([
            'id_loai' => $request->id_loai,
            'so_hang' => $rowNumber,
            'so_ghe' => $row . $request->so_ghe,
            'trang_thai' => $request->status === 'available' ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ghế đã được cập nhật thành công!',
            'seat' => $ghe->load('seatType')
        ]);
    }

    /**
     * Delete a seat
     */
    public function destroySeat(PhongChieu $phongChieu, Ghe $ghe)
    {
        // Check if seat has bookings
        if ($ghe->bookingDetails()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa ghế đã có đặt vé!'
            ], 400);
        }

        $ghe->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ghế đã được xóa thành công!'
        ]);
    }

    /**
     * Update seat status
     */
    public function updateSeatStatus(Request $request, Ghe $ghe)
    {
        $request->validate([
            'status' => 'required|in:available,booked,locked,unavailable,maintenance'
        ]);

        // Map UI status to legacy numeric column
        $map = [
            'available' => 1,
            'booked' => 0,
            'locked' => 0,
            'unavailable' => 0,
            'maintenance' => 0,
        ];
        $ghe->update(['trang_thai' => $map[$request->status] ?? 0]);

        return response()->json([
            'success' => true,
            'message' => 'Trạng thái ghế đã được cập nhật!',
            'seat' => $ghe->load('seatType')
        ]);
    }

    /**
     * Update seat type
     */
    public function updateSeatType(Request $request, Ghe $ghe)
    {
        $request->validate([
            'id_loai' => 'required|exists:loai_ghe,id'
        ]);

        $ghe->update([
            'id_loai' => $request->id_loai,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loại ghế đã được cập nhật!',
            'seat' => $ghe->load('seatType')
        ]);
    }

    /**
     * Bulk update seats: lock/unlock/type/delete
     */
    public function bulkSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'action' => 'required|string|in:lock,unlock,type,delete',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer|exists:ghe,id',
            'id_loai' => 'nullable|integer|exists:loai_ghe,id'
        ]);

        $action = $request->string('action');
        $ids = collect($request->seat_ids)->unique()->values();

        // Limit to seats in this room
        $seats = Ghe::whereIn('id', $ids)->where('id_phong', $phongChieu->id)->get();
        if ($seats->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy ghế hợp lệ trong phòng.'], 404);
        }

        $affected = 0; $skipped = [];
        switch ($action) {
            case 'lock':
                $affected = Ghe::whereIn('id', $seats->pluck('id'))->update(['trang_thai' => 0]);
                break;
            case 'unlock':
                $affected = Ghe::whereIn('id', $seats->pluck('id'))->update(['trang_thai' => 1]);
                break;
            case 'type':
                if (!$request->filled('id_loai')) {
                    return response()->json(['success' => false, 'message' => 'Thiếu id_loai cho hành động đổi loại.'], 422);
                }
                $affected = Ghe::whereIn('id', $seats->pluck('id'))->update(['id_loai' => $request->id_loai]);
                break;
            case 'delete':
                foreach ($seats as $seat) {
                    if ($seat->bookingDetails()->exists()) {
                        $skipped[] = $seat->id;
                        continue;
                    }
                    $seat->delete();
                    $affected++;
                }
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Thực hiện thành công.',
            'affected' => $affected,
            'skipped_ids' => $skipped,
        ]);
    }
}

