<?php

namespace App\Http\Controllers;

use App\Models\PhongChieu;
use App\Models\Ghe;
use App\Models\LoaiGhe;
use App\Models\SuatChieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhongChieuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PhongChieu::withCount(['seats', 'showtimes']);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $phongChieu = $query->paginate(20);

        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.phong-chieu.index', compact('phongChieu'));
        }
        
        return view('admin.phong-chieu.index', compact('phongChieu'));
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
            'type' => 'required|string|in:2D,3D,IMAX,4DX',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,inactive',
            'audio_system' => 'nullable|string|max:255',
            'screen_type' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create room
            $phongChieu = PhongChieu::create([
                'name' => $request->name,
                'rows' => $request->rows,
                'cols' => $request->cols,
                'type' => $request->type,
                'description' => $request->description,
                'status' => $request->status,
                'audio_system' => $request->audio_system,
                'screen_type' => $request->screen_type,
            ]);

            // Generate seats
            $this->createSeatsForRoom($phongChieu, $request->rows, $request->cols, $request->get('seat_type', 'normal'));

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
            $query->orderBy('row_label')->orderBy('so_ghe');
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
            'type' => 'required|string|in:2D,3D,IMAX,4DX',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,inactive',
            'audio_system' => 'nullable|string|max:255',
            'screen_type' => 'nullable|string|max:255',
        ]);

        $phongChieu->update($request->only([
            'name', 'type', 'description', 'status', 'audio_system', 'screen_type'
        ]));

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
            ->where('start_time', '>', now())
            ->count();

        if ($upcomingShowtimes > 0) {
            return back()->withErrors(['error' => 'Không thể xóa phòng chiếu đang có suất chiếu sắp diễn ra!']);
        }

        DB::beginTransaction();
        try {
            // Delete all seats first
            $phongChieu->seats()->delete();
            
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

        $phongChieu->update(['status' => $request->status]);

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
            ->orderBy('row_label')
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
        $seatType = LoaiGhe::where('ten_loai', $defaultType)->first();
        if (!$seatType) {
            $seatType = LoaiGhe::first(); // Fallback to first available type
        }
        $seats = [];

        for ($row = 1; $row <= $rows; $row++) {
            $rowLabel = chr(64 + $row); // A, B, C, ...
            
            for ($col = 1; $col <= $cols; $col++) {
                $seats[] = [
                    'room_id' => $phongChieu->id,
                    'id_loai' => $seatType ? $seatType->id : 1,
                    'seat_code' => $rowLabel . $col, // A1, A2, B1, B2, etc.
                    'row_label' => $rowLabel,
                    'col_number' => $col,
                    'so_ghe' => $col,
                    'status' => 'available',
                    'price' => $seatType ? $seatType->he_so_gia * 50000 : 50000,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        Ghe::insert($seats);
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
        $seatCode = $request->row_label . $request->so_ghe;

        $seat = $phongChieu->seats()->create([
            'id_loai' => $request->id_loai,
            'seat_code' => $seatCode,
            'row_label' => $request->row_label,
            'col_number' => $request->so_ghe,
            'so_ghe' => $request->so_ghe,
            'status' => $request->status,
            'price' => $request->price ?? ($seatType ? $seatType->he_so_gia * 50000 : 50000)
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
        $seatCode = $request->row_label . $request->so_ghe;

        $ghe->update([
            'id_loai' => $request->id_loai,
            'seat_code' => $seatCode,
            'row_label' => $request->row_label,
            'col_number' => $request->so_ghe,
            'so_ghe' => $request->so_ghe,
            'status' => $request->status,
            'price' => $request->price ?? ($seatType ? $seatType->he_so_gia * 50000 : 50000)
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
            'status' => 'required|in:available,booked,locked'
        ]);

        $ghe->update(['status' => $request->status]);

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

        $seatType = LoaiGhe::find($request->id_loai);
        $ghe->update([
            'id_loai' => $request->id_loai,
            'price' => $seatType ? $seatType->he_so_gia * 50000 : 50000
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loại ghế đã được cập nhật!',
            'seat' => $ghe->load('seatType')
        ]);
    }
}

