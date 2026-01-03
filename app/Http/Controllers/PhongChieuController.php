<?php

namespace App\Http\Controllers;

use App\Models\PhongChieu;
use App\Models\Ghe;
use App\Models\LoaiGhe;
use App\Models\SuatChieu;
use App\Models\Phim;
use Carbon\Carbon;
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
     * Append rows/columns of seats to the room without removing existing seats
     */
    public function appendSeats(Request $request, PhongChieu $phongChieu)
    {
        // Block appending seats if room has any showtimes
        if ($phongChieu->showtimes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chỉnh sửa sơ đồ ghế vì phòng đã có suất chiếu.'], 400);
        }
        $request->validate([
            'add_rows' => 'required|integer|min:0|max:20',
            'add_cols' => 'required|integer|min:0|max:30',
            'seat_type' => 'required|string|in:normal,vip,couple'
        ]);

        $addRows = (int) $request->add_rows;
        $addCols = (int) $request->add_cols;

        if ($addRows === 0 && $addCols === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không có hàng/cột nào được thêm.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Current dimensions (fallback to counts if null)
            $currentRows = (int) ($phongChieu->rows ?? max(1, (int) $phongChieu->seats()->max('so_hang')));
            $currentCols = (int) ($phongChieu->cols ?? max(1, (int) $phongChieu->seats()->selectRaw('MAX(LENGTH(so_ghe)) as len')->value('len')));

            $newRows = $currentRows + $addRows;
            $newCols = $currentCols + $addCols;

            // Map seat_type string to id_loai
            $typeIdMap = [ 'normal' => 1, 'vip' => 2, 'couple' => 3 ];
            $typeId = $typeIdMap[$request->seat_type] ?? 1;
            $seatType = LoaiGhe::find($typeId) ?: LoaiGhe::first();
            $typeId = $seatType ? $seatType->id : null;

            $toInsert = [];

            // 1) New rows: rows (currentRows+1 .. newRows) with all columns up to newCols
            if ($addRows > 0) {
                for ($r = $currentRows + 1; $r <= $newRows; $r++) {
                    $rowLabel = chr(64 + $r);
                    for ($c = 1; $c <= $newCols; $c++) {
                        $toInsert[] = [
                            'id_phong' => $phongChieu->id,
                            'id_loai' => $typeId,
                            'so_hang' => $r,
                            'so_ghe' => $rowLabel . $c,
                            'trang_thai' => 1,
                        ];
                    }
                }
            }

            // 2) New columns: columns (currentCols+1 .. newCols) for existing rows (1 .. currentRows)
            if ($addCols > 0) {
                for ($r = 1; $r <= $currentRows; $r++) {
                    $rowLabel = chr(64 + $r);
                    for ($c = $currentCols + 1; $c <= $newCols; $c++) {
                        $toInsert[] = [
                            'id_phong' => $phongChieu->id,
                            'id_loai' => $typeId,
                            'so_hang' => $r,
                            'so_ghe' => $rowLabel . $c,
                            'trang_thai' => 1,
                        ];
                    }
                }
            }

            if (!empty($toInsert)) {
                // Avoid duplicate seat codes just in case
                $existingCodes = Ghe::where('id_phong', $phongChieu->id)
                    ->whereIn('so_ghe', array_map(fn($s) => $s['so_ghe'], $toInsert))
                    ->pluck('so_ghe')
                    ->all();
                if (!empty($existingCodes)) {
                    $toInsert = array_values(array_filter($toInsert, fn($s) => !in_array($s['so_ghe'], $existingCodes, true)));
                }
            }

            if (!empty($toInsert)) {
                Ghe::insert($toInsert);
            }

            // Update room dimensions
            $phongChieu->update([
                'rows' => $newRows,
                'cols' => $newCols,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm hàng/cột ghế thành công!',
                'added_rows' => $addRows,
                'added_cols' => $addCols,
                'total_inserted' => count($toInsert),
                'new_rows' => $newRows,
                'new_cols' => $newCols,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
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
        // Không cho xóa nếu phòng đã có bất kỳ suất chiếu nào (quá khứ hoặc tương lai)
        if ($phongChieu->showtimes()->exists()) {
            return back()->withErrors(['error' => 'Không thể xóa phòng chiếu vì đã có suất chiếu được tạo trong phòng này!']);
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
        // Với quy tắc mới: chỉ cho xóa khi không có bất kỳ suất chiếu nào
        $anyShowtimes = $phongChieu->showtimes()->count();
        return response()->json([
            'success' => true,
            'future_showtimes' => (int) $phongChieu->showtimes()->where('thoi_gian_bat_dau', '>=', now())->count(),
            'can_pause' => $anyShowtimes === 0,
            'can_delete' => $anyShowtimes === 0,
            'message' => $anyShowtimes > 0 ? 'Phòng chiếu đã có suất chiếu, không thể xóa.' : 'Có thể thao tác.'
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
        // Block regenerating seats if room has any showtimes
        if ($phongChieu->showtimes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chỉnh sửa sơ đồ ghế vì phòng đã có suất chiếu.'], 400);
        }
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
        // Lấy các loại ghế từ database
        $normalSeatType = LoaiGhe::where('ten_loai', 'normal')->first() 
            ?? LoaiGhe::where('ten_loai', 'thường')->first()
            ?? LoaiGhe::first();
        
        $vipSeatType = LoaiGhe::where('ten_loai', 'vip')->first() 
            ?? LoaiGhe::where('ten_loai', 'VIP')->first()
            ?? LoaiGhe::where('id', 2)->first();
        
        $coupleSeatType = LoaiGhe::where('ten_loai', 'couple')->first()
            ?? LoaiGhe::where('ten_loai', 'đôi')->first()
            ?? LoaiGhe::where('id', 3)->first();
        
        // Nếu không tìm thấy, sử dụng loại ghế đầu tiên làm mặc định
        $normalTypeId = $normalSeatType ? $normalSeatType->id : 1;
        $vipTypeId = $vipSeatType ? $vipSeatType->id : ($normalSeatType ? $normalSeatType->id : 1);
        $coupleTypeId = $coupleSeatType ? $coupleSeatType->id : ($normalSeatType ? $normalSeatType->id : 1);
        
        $seats = [];
        
        // Cấu trúc ghế: mỗi hàng có 19 ghế, hàng L có 16 ghế đôi
        // Ghế VIP: D3-D16 và J3-J16
        // Hàng L: ghế đôi từ L1-L16
        
        for ($row = 1; $row <= $rows; $row++) {
            $rowLetter = chr(64 + $row); // A, B, C, D, ...
            
            // Xác định số ghế cho hàng này
            // Hàng L (row 12) chỉ có 16 ghế đôi
            $maxCols = ($rowLetter === 'L') ? 16 : $cols;
            
            for ($col = 1; $col <= $maxCols; $col++) {
                $seatCode = $rowLetter . $col; // A1, A2, D3, D16, J3, J16, L1, L16...
                
                // Xác định loại ghế
                $seatTypeId = $normalTypeId; // Mặc định là ghế thường
                
                // Hàng L: tất cả là ghế đôi
                if ($rowLetter === 'L') {
                    $seatTypeId = $coupleTypeId;
                }
                // Hàng D (row 4): ghế 3-16 là VIP
                elseif ($rowLetter === 'D' && $col >= 3 && $col <= 16) {
                    $seatTypeId = $vipTypeId;
                }
                // Hàng J (row 10): ghế 3-16 là VIP
                elseif ($rowLetter === 'J' && $col >= 3 && $col <= 16) {
                    $seatTypeId = $vipTypeId;
                }
                
                $seats[] = [
                    'id_phong' => $phongChieu->id,
                    'id_loai' => $seatTypeId,
                    'so_hang' => $row,
                    'so_ghe' => $seatCode,
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
        $locked = $phongChieu->showtimes()->exists();
        
        return view('admin.phong-chieu.manage-seats', compact('phongChieu', 'seatTypes', 'locked'));
    }

    /**
     * Create showtimes for selected rooms in a peak-hour window.
     */
    public function createPeakHoursShowtimes(Request $request)
    {
        $request->validate([
            'phim_id' => 'required|exists:phim,id',
            'phong_chieu' => 'required|array|min:1',
            'phong_chieu.*' => 'integer|exists:phong_chieu,id',
            'ngay_chieu' => 'required|date',
            'gio_bat_dau' => 'required|string',
            'gio_ket_thuc' => 'required|string',
            'thoi_gian_ngung' => 'required|integer|min:0',
        ]);

        $phim = Phim::find($request->phim_id);
        $duration = (int) ($phim->thoi_luong ?? 120);

        $rooms = $request->phong_chieu;
        $date = $request->ngay_chieu;
        $start = Carbon::parse("{$date} {$request->gio_bat_dau}");
        $end = Carbon::parse("{$date} {$request->gio_ket_thuc}");
        $gap = (int) $request->thoi_gian_ngung;

        if ($start >= $end) {
            return back()->withErrors(['error' => 'Giờ kết thúc phải sau giờ bắt đầu.']);
        }

        $created = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rooms as $roomId) {
                $current = $start->copy();
                while (true) {
                    $showEnd = $current->copy()->addMinutes($duration);
                    if ($showEnd->gt($end)) break;

                    // Check for overlapping existing showtime in same room
                    $overlap = SuatChieu::where('id_phong', $roomId)
                        ->where(function($q) use ($current, $showEnd) {
                            $q->whereBetween('thoi_gian_bat_dau', [$current, $showEnd->subSecond()])
                              ->orWhere(function($q2) use ($current, $showEnd) {
                                  $q2->where('thoi_gian_bat_dau', '<', $current)
                                     ->where('thoi_gian_ket_thuc', '>', $current);
                              });
                        })->exists();

                    if ($overlap) {
                        $skipped++;
                    } else {
                        SuatChieu::create([
                            'id_phim' => $phim->id,
                            'id_phong' => $roomId,
                            'thoi_gian_bat_dau' => $current->toDateTimeString(),
                            'thoi_gian_ket_thuc' => $showEnd->toDateTimeString(),
                            'trang_thai' => 1,
                        ]);
                        $created++;
                    }

                    // Move to next slot
                    $current = $showEnd->copy()->addMinutes($gap);
                }
            }

            DB::commit();
            return redirect()->route('admin.phong-chieu.peak-hours')
                ->with('success', "Hoàn tất: tạo {$created} suất chiếu, bỏ qua {$skipped} suất do trùng/xung đột.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi khi tạo suất chiếu: ' . $e->getMessage()]);
        }
    }

    /**
     * Show peak hours configuration form
     */
    public function showPeakHoursConfig()
    {
        $phims = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])->get();
        $phongChieus = PhongChieu::all();
        return view('admin.phong-chieu.peak-hours', compact('phims', 'phongChieus'));
    }

    /**
     * Store a new seat
     */
    public function storeSeat(Request $request, PhongChieu $phongChieu)
    {
        // Block adding seat if room has any showtimes
        if ($phongChieu->showtimes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thêm ghế vì phòng đã có suất chiếu.'], 400);
        }
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
        // Block updating seat if room has any showtimes
        if ($phongChieu->showtimes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chỉnh sửa ghế vì phòng đã có suất chiếu.'], 400);
        }
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
        // Block deleting seat if room has any showtimes
        if ($phongChieu->showtimes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa ghế vì phòng đã có suất chiếu.'], 400);
        }
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

        // Block changing seat status if room has any showtimes
        $room = PhongChieu::find($ghe->id_phong);
        if ($room && $room->showtimes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chỉnh sửa ghế vì phòng đã có suất chiếu.'], 400);
        }

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

        // Block changing seat type if room has any showtimes
        $room = PhongChieu::find($ghe->id_phong);
        if ($room && $room->showtimes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chỉnh sửa ghế vì phòng đã có suất chiếu.'], 400);
        }

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
        // Block bulk operations if room has any showtimes
        if ($phongChieu->showtimes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chỉnh sửa sơ đồ ghế vì phòng đã có suất chiếu.'], 400);
        }
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

