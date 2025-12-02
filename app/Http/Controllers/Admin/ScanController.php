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

    /**
     * AJAX: Check ticket validity (Admin)
     */
    public function check(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        if (!$ticketId) {
            return response()->json(['valid' => false, 'message' => 'Vui lòng nhập mã vé']);
        }

        // Accept formats like ticket_id=123, MV000123, plain 123
        $raw = (string)$ticketId;
        if (preg_match('/ticket_id[=:]([A-Za-z0-9]+)/i', $raw, $m)) {
            $raw = $m[1];
        }
        // Prefer numeric id if digits present (e.g., MV000290 -> 290)
        $numeric = null;
        if (preg_match('/(\d+)/', $raw, $m2)) {
            $numeric = (int)$m2[1];
        }
        $ticket = DatVe::with(['suatChieu.phim','chiTietDatVe.ghe'])
            ->when($numeric !== null, function($q) use ($numeric) { $q->where('id', $numeric); })
            ->when($numeric === null, function($q) use ($raw) { $q->where('id', $raw); })
            ->orWhere('ticket_code', $raw)
            ->first();

        if (!$ticket) {
            return response()->json(['valid' => false, 'message' => 'Vé không tồn tại']);
        }
        if ($ticket->trang_thai != 1) {
            return response()->json(['valid' => false, 'message' => 'Vé chưa thanh toán']);
        }
        if ($ticket->checked_in) {
            return response()->json(['valid' => false, 'message' => 'Vé này đã được quét trước đó']);
        }
        $showtimeStart = optional($ticket->suatChieu)->thoi_gian_bat_dau ? Carbon::parse($ticket->suatChieu->thoi_gian_bat_dau) : null;
        if (!$showtimeStart) {
            return response()->json(['valid' => false, 'message' => 'Vé không có suất chiếu hợp lệ']);
        }
        $now = Carbon::now();
        if (($ticket->expires_at && $now->greaterThan(Carbon::parse($ticket->expires_at))) || $now->greaterThanOrEqualTo($showtimeStart)) {
            return response()->json(['valid' => false, 'message' => 'Vé đã hết hạn']);
        }
        if ($now->lt($showtimeStart->copy()->subMinutes(30))) {
            return response()->json(['valid' => false, 'message' => 'Chỉ có thể quét vé trong vòng 30 phút trước khi phim bắt đầu']);
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

    /**
     * AJAX: Confirm check-in (Admin)
     */
    public function confirm(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        if (!$ticketId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập mã vé']);
        }

        $raw = (string)$ticketId;
        if (preg_match('/ticket_id[=:]([A-Za-z0-9]+)/i', $raw, $m)) { $raw = $m[1]; }
        $numeric = null;
        if (preg_match('/(\d+)/', $raw, $m2)) { $numeric = (int)$m2[1]; }

        $ticket = DatVe::when($numeric !== null, function($q) use ($numeric) { $q->where('id', $numeric); })
            ->when($numeric === null, function($q) use ($raw) { $q->where('id', $raw); })
            ->orWhere('ticket_code', $raw)
            ->first();
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Vé không tồn tại']);
        }
        if ($ticket->trang_thai != 1) {
            return response()->json(['success' => false, 'message' => 'Vé chưa thanh toán']);
        }
        if ($ticket->checked_in) {
            return response()->json(['success' => false, 'message' => 'Vé này đã được quét trước đó']);
        }
        $showtimeStart = optional($ticket->suatChieu)->thoi_gian_bat_dau ? Carbon::parse($ticket->suatChieu->thoi_gian_bat_dau) : null;
        if (!$showtimeStart) {
            return response()->json(['success' => false, 'message' => 'Vé không có suất chiếu hợp lệ']);
        }
        $now = Carbon::now();
        if (($ticket->expires_at && $now->greaterThan(Carbon::parse($ticket->expires_at))) || $now->greaterThanOrEqualTo($showtimeStart)) {
            return response()->json(['success' => false, 'message' => 'Vé đã hết hạn']);
        }
        if ($now->lt($showtimeStart->copy()->subMinutes(30))) {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể quét vé trong vòng 30 phút trước khi phim bắt đầu']);
        }

        $ticket->checked_in = true;
        $ticket->save();

        return response()->json(['success' => true, 'message' => 'Xác nhận thành công']);
    }
}
