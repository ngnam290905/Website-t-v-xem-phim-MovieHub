<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatVe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScanController extends Controller
{
    public function index(Request $request)
    {
        $query = DatVe::with([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe',
            'nguoiDung',
            'thanhToan'
        ])->where('trang_thai', 1); // Only paid tickets

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('ticket_code', 'like', "%{$search}%")
                  ->orWhere('ten_khach_hang', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$search}%")
                  ->orWhereHas('suatChieu.phim', function($q) use ($search) {
                      $q->where('ten_phim', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by checked_in status
        if ($request->filled('status')) {
            if ($request->status === 'checked') {
                $query->where('checked_in', true);
            } elseif ($request->status === 'not_checked') {
                $query->where('checked_in', false);
            }
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by showtime date
        if ($request->filled('showtime_date')) {
            $query->whereHas('suatChieu', function($q) use ($request) {
                $q->whereDate('thoi_gian_bat_dau', $request->showtime_date);
            });
        }

        $tickets = $query->orderByDesc('created_at')->paginate(20);

        // Statistics
        $stats = [
            'total_paid' => DatVe::where('trang_thai', 1)->count(),
            'checked_in' => DatVe::where('trang_thai', 1)->where('checked_in', true)->count(),
            'not_checked' => DatVe::where('trang_thai', 1)->where('checked_in', false)->count(),
            'today_checked' => DatVe::where('trang_thai', 1)
                ->where('checked_in', true)
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'today_not_checked' => DatVe::where('trang_thai', 1)
                ->where('checked_in', false)
                ->whereDate('created_at', Carbon::today())
                ->count(),
        ];

        return view('admin.scan.index', compact('tickets', 'stats'));
    }

    public function show($id)
    {
        $ticket = DatVe::with([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe',
            'chiTietCombo.combo',
            'nguoiDung',
            'thanhToan',
            'khuyenMai'
        ])->findOrFail($id);

        $seats = $ticket->chiTietDatVe->map(function($detail) {
            return [
                'seat' => $detail->ghe->so_ghe ?? 'N/A',
                'type' => $detail->ghe->loaiGhe->ten_loai ?? 'N/A',
                'price' => $detail->gia ?? 0
            ];
        });

        return view('admin.scan.show', compact('ticket', 'seats'));
    }
}
