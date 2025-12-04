<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatVe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Filter by date created
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter showtime date
        if ($request->filled('showtime_date')) {
            $query->whereHas('suatChieu', function($q) use ($request) {
                $q->whereDate('thoi_gian_bat_dau', $request->showtime_date);
            });
        }

        $tickets = $query->orderByDesc('created_at')->paginate(20);

        // Statistics (KHÔNG DÙNG checked_in)
        $stats = [
            'total_paid' => DatVe::where('trang_thai', 1)->count(),
            'total_today' => DatVe::where('trang_thai', 1)
                                   ->whereDate('created_at', today())
                                   ->count(),
            'total_showtime_today' => DatVe::where('trang_thai', 1)
                ->whereHas('suatChieu', function($q) {
                    $q->whereDate('thoi_gian_bat_dau', today());
                })
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

    // AJAX: Check ticket validity
    public function check(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        if (!$ticketId) {
            return response()->json(['valid' => false, 'message' => 'Vui lòng nhập mã vé']);
        }

        $raw = (string)$ticketId;

        if (preg_match('/ticket_id[=:]([A-Za-z0-9]+)/i', $raw, $m)) {
            $raw = $m[1];
        }

        $numeric = null;
        if (preg_match('/(\d+)/', $raw, $m2)) {
            $numeric = (int)$m2[1];
        }

        $ticket = DatVe::with(['suatChieu.phim','chiTietDatVe.ghe'])
            ->when($numeric !== null, fn($q) => $q->where('id', $numeric))
            ->when($numeric === null, fn($q) => $q->where('id', $raw))
            ->orWhere('ticket_code', $raw)
            ->first();

        if (!$ticket) {
            return response()->json(['valid' => false, 'message' => 'Vé không tồn tại']);
        }
        if ($ticket->trang_thai != 1) {
            return response()->json(['valid' => false, 'message' => 'Vé chưa thanh toán']);
        }

        $seats = $ticket->chiTietDatVe->map(fn($d) => $d->ghe->so_ghe ?? 'N/A')->filter()->implode(', ');

        return response()->json([
            'valid' => true,
            'ticket' => [
                'id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code ?? sprintf('MV%06d', $ticket->id),
                'movie' => $ticket->suatChieu->phim->ten_phim ?? 'N/A',
                'seats' => $seats,
                'showtime' => optional($ticket->suatChieu->thoi_gian_bat_dau)->format('d/m/Y H:i') ?? 'N/A'
            ]
        ]);
    }

    // AJAX: Confirm check-in (KHÔNG LƯU TRẠNG THÁI)
    public function confirm(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Quét vé thành công (KHÔNG LƯU TRẠNG THÁI CHECK-IN)'
        ]);
    }
}
