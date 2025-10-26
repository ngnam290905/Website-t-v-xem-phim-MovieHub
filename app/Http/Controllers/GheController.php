<?php

namespace App\Http\Controllers;

use App\Models\Ghe;
use App\Models\PhongChieu;
use App\Models\LoaiGhe;
use Illuminate\Http\Request;

class GheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ghe = Ghe::with(['phongChieu', 'loaiGhe'])
            ->orderBy('id_phong')
            ->orderBy('so_hang')
            ->orderBy('so_ghe')
            ->paginate(20);
        
        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.ghe.index', compact('ghe'));
        }
        
        return view('admin.ghe.index', compact('ghe'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        $loaiGhe = LoaiGhe::all();
        
        return view('admin.ghe.create', compact('phongChieu', 'loaiGhe'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_phong' => 'required|exists:phong_chieu,id',
            'id_loai' => 'required|exists:loai_ghe,id',
            'so_ghe' => 'required|string|max:10',
            'so_hang' => 'required|integer|min:1',
            'so_cot' => 'required|integer|min:1',
            'trang_thai' => 'boolean'
        ]);

        // Check if seat already exists in the same room
        $existingGhe = Ghe::where('id_phong', $request->id_phong)
            ->where('so_ghe', $request->so_ghe)
            ->exists();

        if ($existingGhe) {
            return back()->withErrors(['so_ghe' => 'Ghế này đã tồn tại trong phòng.']);
        }

        Ghe::create($request->all());

        return redirect()->route('admin.ghe.index')
            ->with('success', 'Tạo ghế thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ghe $ghe)
    {
        $ghe->load(['phongChieu', 'loaiGhe']);
        
        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.ghe.show', compact('ghe'));
        }
        
        return view('admin.ghe.show', compact('ghe'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ghe $ghe)
    {
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        $loaiGhe = LoaiGhe::all();
        
        return view('admin.ghe.edit', compact('ghe', 'phongChieu', 'loaiGhe'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ghe $ghe)
    {
        $request->validate([
            'id_phong' => 'required|exists:phong_chieu,id',
            'id_loai' => 'required|exists:loai_ghe,id',
            'so_ghe' => 'required|string|max:10',
            'so_hang' => 'required|integer|min:1',
            'so_cot' => 'required|integer|min:1',
            'trang_thai' => 'boolean'
        ]);

        // Check if seat already exists in the same room (excluding current seat)
        $existingGhe = Ghe::where('id_phong', $request->id_phong)
            ->where('so_ghe', $request->so_ghe)
            ->where('id', '!=', $ghe->id)
            ->exists();

        if ($existingGhe) {
            return back()->withErrors(['so_ghe' => 'Ghế này đã tồn tại trong phòng.']);
        }

        $ghe->update($request->all());

        return redirect()->route('admin.ghe.index')
            ->with('success', 'Cập nhật ghế thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ghe $ghe)
    {
        // Check if seat has any bookings
        if ($ghe->chiTietDatVe()->exists()) {
            return back()->withErrors(['error' => 'Không thể xóa ghế đã có vé đặt.']);
        }

        $ghe->delete();

        return redirect()->route('admin.ghe.index')
            ->with('success', 'Xóa ghế thành công!');
    }

    /**
     * Update status of seat (for staff)
     */
    public function updateStatus(Request $request, Ghe $ghe)
    {
        $request->validate([
            'trang_thai' => 'required|boolean'
        ]);

        $ghe->update(['trang_thai' => $request->trang_thai]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái ghế thành công!'
        ]);
    }

    /**
     * Get seats by room
     */
    public function getByRoom(Request $request)
    {
        $request->validate([
            'id_phong' => 'required|exists:phong_chieu,id'
        ]);

        $ghe = Ghe::with(['loaiGhe'])
            ->where('id_phong', $request->id_phong)
            ->where('trang_thai', 1)
            ->orderBy('hang')
            ->orderBy('cot')
            ->get();

        return response()->json($ghe);
    }

    /**
     * Generate seats for a room automatically
     */
    public function generateSeats(Request $request)
    {
        $request->validate([
            'id_phong' => 'required|exists:phong_chieu,id',
            'id_loai' => 'required|exists:loai_ghe,id'
        ]);

        $phongChieu = PhongChieu::findOrFail($request->id_phong);
        
        // Clear existing seats for this room
        Ghe::where('id_phong', $request->id_phong)->delete();

        // Generate seats
        for ($hang = 1; $hang <= $phongChieu->so_hang; $hang++) {
            for ($cot = 1; $cot <= $phongChieu->so_cot; $cot++) {
                Ghe::create([
                    'id_phong' => $request->id_phong,
                    'id_loai' => $request->id_loai,
                    'so_ghe' => $hang . chr(64 + $cot), // A, B, C, etc.
                    'so_hang' => $hang,
                    'so_cot' => $cot,
                    'trang_thai' => true
                ]);
            }
        }

        return redirect()->route('admin.ghe.index')
            ->with('success', 'Tạo ghế tự động thành công!');
    }
}
