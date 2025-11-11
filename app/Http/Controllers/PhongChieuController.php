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
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'rows' => 'required|integer|min:1|max:20',
                'cols' => 'required|integer|min:1|max:30',
                'description' => 'nullable|string|max:1000',
                'status' => 'required|string|in:active,inactive',
                'type' => 'nullable|string|max:50',
                'audio_system' => 'nullable|string|max:255',
                'screen_type' => 'nullable|string|max:255',
                'layout_preset' => 'nullable|string|in:grid,arc,staggered,cluster',
                'segments' => 'nullable', // JSON string or array
            ]);

            DB::beginTransaction();
            
            // Create room - only include columns that exist
            $createData = [
                'name' => $request->name,
                'rows' => $request->rows,
                'cols' => $request->cols,
                'description' => $request->description,
                'status' => $request->status,
                'layout_json' => $request->has('layout_json') ? $request->input('layout_json') : null,
            ];
            
            // Add optional columns only if they exist in database
            if ($request->filled('type') && Schema::hasColumn('phong_chieu', 'type')) {
                $createData['type'] = $request->type;
            }
            if ($request->filled('audio_system') && Schema::hasColumn('phong_chieu', 'audio_system')) {
                $createData['audio_system'] = $request->audio_system;
            }
            if ($request->filled('screen_type') && Schema::hasColumn('phong_chieu', 'screen_type')) {
                $createData['screen_type'] = $request->screen_type;
            }
            
            $phongChieu = PhongChieu::create($createData);

            // Determine mixed segments if provided
            $segments = $request->input('segments');
            if (is_string($segments)) {
                $decoded = json_decode($segments, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $segments = $decoded;
                } else {
                    $segments = null;
                }
            }

            if (is_array($segments) && count($segments) > 0) {
                // Bulk create seats by segments
                $toInsert = [];
                $existingSet = [];
                foreach ($segments as $seg) {
                    if (!isset($seg['row_label'], $seg['count'], $seg['id_loai'])) continue;
                    $rowLabel = strtoupper((string) $seg['row_label']);
                    $rowNumber = max(1, ord($rowLabel) - 64);
                    $count = (int) $seg['count'];
                    // resolve seat type id (accept id or name)
                    $typeId = null;
                    $typeVal = $seg['id_loai'];
                    if (is_numeric($typeVal)) {
                        $typeId = (int) $typeVal;
                    } else if (is_string($typeVal)) {
                        $found = LoaiGhe::where('ten_loai', $typeVal)->first();
                        $typeId = $found ? $found->id : null;
                    }
                    if (!$typeId) {
                        $fallback = LoaiGhe::first();
                        $typeId = $fallback ? $fallback->id : null;
                    }
                    $start = (int) ($seg['start_index'] ?? 1);
                    for ($i = 0; $i < $count; $i++) {
                        $index = $start + $i;
                        $code = $rowLabel . $index;
                        if (isset($existingSet[$code])) continue;
                        $existingSet[$code] = true;
                        $toInsert[] = [
                            'id_phong' => $phongChieu->id,
                            'id_loai' => $typeId,
                            'so_hang' => $rowNumber,
                            'so_ghe' => $code,
                            'trang_thai' => 1,
                        ];
                    }
                }
                if (!empty($toInsert)) {
                    Ghe::insert($toInsert);
                }
            } else {
                // Fallback to uniform grid generation
                $this->createSeatsForRoom($phongChieu, $request->rows, $request->cols, $request->get('seat_type', 'normal'));
            }

            DB::commit();

            return redirect()->route('admin.phong-chieu.index')
                ->with('success', 'Phòng chiếu đã được tạo thành công!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating phong chieu: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['_token', 'segments'])
            ]);
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo phòng chiếu: ' . $e->getMessage()])->withInput();
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
            'type' => 'nullable|string|max:50',
            'audio_system' => 'nullable|string|max:255',
            'screen_type' => 'nullable|string|max:255',
        ]);

        // Only update fields that exist in database
        $updateData = [];
        
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->has('description')) {
            $updateData['description'] = $request->description;
        }
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        if ($request->has('type') && Schema::hasColumn('phong_chieu', 'type')) {
            $updateData['type'] = $request->type;
        }
        if ($request->has('audio_system') && Schema::hasColumn('phong_chieu', 'audio_system')) {
            $updateData['audio_system'] = $request->audio_system;
        }
        if ($request->has('screen_type') && Schema::hasColumn('phong_chieu', 'screen_type')) {
            $updateData['screen_type'] = $request->screen_type;
        }
        
        $phongChieu->update($updateData);

        return redirect()->route('admin.phong-chieu.index')
            ->with('success', 'Phòng chiếu đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhongChieu $phongChieu)
    {
        try {
            // Check if room has upcoming showtimes
            $upcomingShowtimes = $phongChieu->showtimes()
                ->where('thoi_gian_bat_dau', '>', now())
                ->count();

            if ($upcomingShowtimes > 0) {
                return back()->withErrors(['error' => 'Không thể xóa phòng chiếu đang có suất chiếu sắp diễn ra!']);
            }

            DB::beginTransaction();
            
            // Delete all booking details for seats in this room
            $seatIds = $phongChieu->seats()->pluck('id');
            if ($seatIds->isNotEmpty()) {
                DB::table('chi_tiet_dat_ve')
                    ->whereIn('id_ghe', $seatIds)
                    ->delete();
            }
            
            // Delete all seats first
            $phongChieu->seats()->delete();
            
            // Delete room
            $phongChieu->delete();

            DB::commit();

            return redirect()->route('admin.phong-chieu.index')
                ->with('success', 'Phòng chiếu đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error deleting phong chieu: ' . $e->getMessage(), [
                'exception' => $e,
                'phong_chieu_id' => $phongChieu->id ?? null
            ]);
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

        // Map status to legacy trang_thai column directly
        $trangThai = $request->status === 'active' ? 1 : 0;
        $phongChieu->update(['trang_thai' => $trangThai]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái phòng chiếu thành công!'
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
            'seat_type' => 'nullable|string',
            'default_seat_type' => 'nullable|integer|exists:loai_ghe,id'
        ]);

        DB::beginTransaction();
        try {
            // Get seat type from ID or name
            $seatTypeName = 'normal'; // default
            if ($request->filled('default_seat_type')) {
                $seatType = LoaiGhe::find($request->default_seat_type);
                if ($seatType) {
                    $seatTypeName = $seatType->ten_loai;
                }
            } else if ($request->filled('seat_type')) {
                $seatTypeName = $request->seat_type;
            }

            // Delete related records first
            $phongChieu->seats()->each(function($seat) {
                $seat->chiTietDatVe()->delete();
            });
            
            // Delete existing seats
            $phongChieu->seats()->delete();

            // Update room dimensions
            $phongChieu->update([
                'rows' => $request->rows,
                'cols' => $request->cols
            ]);

            // Generate new seats
            $this->createSeatsForRoom($phongChieu, $request->rows, $request->cols, $seatTypeName);

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
     * Bulk create seats with mixed types in one request
     */
    public function bulkCreateSeats(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'segments' => 'required|array|min:1',
            'segments.*.row_label' => 'required|string|max:1',
            'segments.*.count' => 'required|integer|min:1|max:200',
            'segments.*.id_loai' => 'required|exists:loai_ghe,id',
            'segments.*.start_index' => 'nullable|integer|min:1',
            'segments.*.gap' => 'nullable|integer|min:0',
        ]);

        $toInsert = [];
        $nowExistingCodes = Ghe::where('id_phong', $phongChieu->id)->pluck('so_ghe')->all();
        $existingSet = array_flip($nowExistingCodes);

        foreach ($request->segments as $seg) {
            $rowLabel = strtoupper($seg['row_label']);
            $rowNumber = max(1, ord($rowLabel) - 64);
            $count = (int) $seg['count'];
            $typeId = (int) $seg['id_loai'];
            $start = (int) ($seg['start_index'] ?? 1);
            $gap = (int) ($seg['gap'] ?? 0);

            for ($i = 0; $i < $count; $i++) {
                $index = $start + $i + ($gap > 0 ? floor($i / max(1, $gap)) : 0) * 0; // gap placeholder if later needed
                $code = $rowLabel . $index;
                if (isset($existingSet[$code])) {
                    continue; // skip duplicates
                }
                $toInsert[] = [
                    'id_phong' => $phongChieu->id,
                    'id_loai' => $typeId,
                    'so_hang' => $rowNumber,
                    'so_ghe' => $code,
                    'trang_thai' => 1,
                ];
            }
        }

        if (empty($toInsert)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có ghế mới cần thêm (trùng mã hoặc dữ liệu rỗng).'
            ], 422);
        }

        Ghe::insert($toInsert);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm ghế theo nhóm thành công!',
            'created' => count($toInsert),
        ]);
    }

    /**
     * Bulk update seat positions and optional layout_json
     */
    public function updateSeatPositions(Request $request, PhongChieu $phongChieu)
    {
        $request->validate([
            'seats' => 'required|array|min:1',
            'seats.*.id' => 'required|integer|exists:ghe,id',
            'seats.*.pos_x' => 'nullable|integer',
            'seats.*.pos_y' => 'nullable|integer',
            'seats.*.zone' => 'nullable|string|max:50',
            'seats.*.meta' => 'nullable|array',
            'layout_json' => 'nullable|array',
        ]);

        $ids = collect($request->seats)->pluck('id')->unique()->values();
        $seats = Ghe::whereIn('id', $ids)->where('id_phong', $phongChieu->id)->get()->keyBy('id');

        foreach ($request->seats as $item) {
            $seat = $seats->get($item['id']);
            if (!$seat) continue;
            $seat->update([
                'pos_x' => $item['pos_x'] ?? $seat->pos_x,
                'pos_y' => $item['pos_y'] ?? $seat->pos_y,
                'zone' => array_key_exists('zone', $item) ? $item['zone'] : $seat->zone,
                'meta' => array_key_exists('meta', $item) ? $item['meta'] : $seat->meta,
            ]);
        }

        if ($request->filled('layout_json')) {
            $phongChieu->update([
                'layout_json' => $request->layout_json,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật vị trí ghế và layout thành công!',
        ]);
    }

    /**
     * Get seat details
     */
    public function showSeat(PhongChieu $phongChieu, Ghe $ghe)
    {
        // Verify seat belongs to room
        if ($ghe->id_phong !== $phongChieu->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ghế không thuộc phòng này'
            ], 404);
        }

        $ghe->load('seatType');
        
        return response()->json([
            'id' => $ghe->id,
            'so_ghe' => $ghe->so_ghe,
            'so_hang' => $ghe->so_hang,
            'id_loai' => $ghe->id_loai,
            'trang_thai' => $ghe->trang_thai,
            'seat_type' => $ghe->seatType ? $ghe->seatType->ten_loai : null
        ]);
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

