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
    public function index()
    {
        $suatChieu = SuatChieu::with(['phim', 'phongChieu'])
            ->orderBy('thoi_gian_bat_dau', 'desc')
            ->paginate(10);
        
        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.suat-chieu.index', compact('suatChieu'));
        }
        
        return view('admin.suat-chieu.index', compact('suatChieu'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $phim = Movie::where('trang_thai', 1)->get();
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        
        return view('admin.suat-chieu.create', compact('phim', 'phongChieu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_phim' => 'required|exists:phim,id',
            'id_phong' => 'required|exists:phong_chieu,id',
            'thoi_gian_bat_dau' => 'required|date|after:now',
            'thoi_gian_ket_thuc' => 'required|date|after:thoi_gian_bat_dau',
        ]);

        // Check for time conflicts
        $conflict = SuatChieu::where('id_phong', $request->id_phong)
            ->where(function($query) use ($request) {
                $query->whereBetween('thoi_gian_bat_dau', [$request->thoi_gian_bat_dau, $request->thoi_gian_ket_thuc])
                      ->orWhereBetween('thoi_gian_ket_thuc', [$request->thoi_gian_bat_dau, $request->thoi_gian_ket_thuc])
                      ->orWhere(function($q) use ($request) {
                          $q->where('thoi_gian_bat_dau', '<=', $request->thoi_gian_bat_dau)
                            ->where('thoi_gian_ket_thuc', '>=', $request->thoi_gian_ket_thuc);
                      });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['thoi_gian_bat_dau' => 'Thời gian này đã bị trùng với suất chiếu khác trong cùng phòng.']);
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
        $suatChieu->load(['phim', 'phongChieu', 'phongChieu.ghe']);
        
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
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        
        return view('admin.suat-chieu.edit', compact('suatChieu', 'phim', 'phongChieu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuatChieu $suatChieu)
    {
        $request->validate([
            'id_phim' => 'required|exists:phim,id',
            'id_phong' => 'required|exists:phong_chieu,id',
            'thoi_gian_bat_dau' => 'required|date',
            'thoi_gian_ket_thuc' => 'required|date|after:thoi_gian_bat_dau',
            'trang_thai' => 'boolean'
        ]);

        // Check for time conflicts (excluding current suat chieu)
        $conflict = SuatChieu::where('id_phong', $request->id_phong)
            ->where('id', '!=', $suatChieu->id)
            ->where(function($query) use ($request) {
                $query->whereBetween('thoi_gian_bat_dau', [$request->thoi_gian_bat_dau, $request->thoi_gian_ket_thuc])
                      ->orWhereBetween('thoi_gian_ket_thuc', [$request->thoi_gian_bat_dau, $request->thoi_gian_ket_thuc])
                      ->orWhere(function($q) use ($request) {
                          $q->where('thoi_gian_bat_dau', '<=', $request->thoi_gian_bat_dau)
                            ->where('thoi_gian_ket_thuc', '>=', $request->thoi_gian_ket_thuc);
                      });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['thoi_gian_bat_dau' => 'Thời gian này đã bị trùng với suất chiếu khác trong cùng phòng.']);
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
            'trang_thai' => 'required|boolean'
        ]);

        $suatChieu->update(['trang_thai' => $request->trang_thai]);

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
}
