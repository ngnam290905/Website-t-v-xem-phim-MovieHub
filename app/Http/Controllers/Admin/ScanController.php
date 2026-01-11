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
            'chiTietFood.food',
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

        $comboItems = $ticket->chiTietCombo ?? collect();
        $foodItems = $ticket->chiTietFood ?? collect();
        $isPrinted = $ticket->da_in ?? false;

        return view('admin.scan.show', compact('ticket', 'seats', 'comboItems', 'foodItems', 'isPrinted'));
    }

    /**
     * Mark ticket as printed (Admin can also mark)
     */
    public function markAsPrinted($id)
    {
        $ticket = DatVe::findOrFail($id);

        // Chỉ đánh dấu nếu chưa in
        if (!$ticket->da_in) {
            $ticket->update([
                'da_in' => true,
                'thoi_gian_in' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vé đã được đánh dấu là đã in',
                'printed_at' => $ticket->thoi_gian_in->format('d/m/Y H:i:s')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Vé này đã được in rồi',
            'printed_at' => $ticket->thoi_gian_in ? $ticket->thoi_gian_in->format('d/m/Y H:i:s') : null
        ], 400);
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

        // Accept formats like ticket_id=123, ticket_id=MV000123, MV000123, plain 123
        $raw = trim((string)$ticketId);
        if (preg_match('/ticket_id[=:]([A-Za-z0-9]+)/i', $raw, $m)) {
            $raw = $m[1];
        }
        
        // Try to find ticket by ticket_code first (more reliable)
        $ticket = DatVe::with(['suatChieu.phim','chiTietDatVe.ghe'])
            ->where('ticket_code', $raw)
            ->first();
        
        // If not found and raw is numeric, try by ID
        if (!$ticket && is_numeric($raw)) {
            $ticket = DatVe::with(['suatChieu.phim','chiTietDatVe.ghe'])
                ->where('id', (int)$raw)
                ->first();
        }
        
        // If still not found, try extracting numeric from ticket_code format (MV000290 -> 290)
        if (!$ticket && preg_match('/(\d+)/', $raw, $m2)) {
            $numeric = (int)$m2[1];
            $ticket = DatVe::with(['suatChieu.phim','chiTietDatVe.ghe'])
                ->where('id', $numeric)
                ->first();
        }

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
        // Chỉ kiểm tra vé đã hết hạn (sau giờ chiếu hoặc sau expires_at)
        if (($ticket->expires_at && $now->greaterThan(Carbon::parse($ticket->expires_at))) || $now->greaterThanOrEqualTo($showtimeStart)) {
            return response()->json(['valid' => false, 'message' => 'Vé đã hết hạn']);
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

        $raw = trim((string)$ticketId);
        if (preg_match('/ticket_id[=:]([A-Za-z0-9]+)/i', $raw, $m)) {
            $raw = $m[1];
        }
        
        // Try to find ticket by ticket_code first
        $ticket = DatVe::where('ticket_code', $raw)->first();
        
        // If not found and raw is numeric, try by ID
        if (!$ticket && is_numeric($raw)) {
            $ticket = DatVe::where('id', (int)$raw)->first();
        }
        
        // If still not found, try extracting numeric from ticket_code format
        if (!$ticket && preg_match('/(\d+)/', $raw, $m2)) {
            $numeric = (int)$m2[1];
            $ticket = DatVe::where('id', $numeric)->first();
        }
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
        // Chỉ kiểm tra vé đã hết hạn (sau giờ chiếu hoặc sau expires_at)
        if (($ticket->expires_at && $now->greaterThan(Carbon::parse($ticket->expires_at))) || $now->greaterThanOrEqualTo($showtimeStart)) {
            return response()->json(['success' => false, 'message' => 'Vé đã hết hạn']);
        }

        $ticket->checked_in = true;
        $ticket->save();

        return response()->json(['success' => true, 'message' => 'Xác nhận thành công']);
    }

    /**
     * Show print page for multiple tickets. Each seat becomes a separate printable ticket.
     * Accepts query param `ids` as comma-separated booking IDs or repeated `ids[]`.
     */
    public function printMultiple(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids) && strpos($ids, ',') !== false) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }

        if (is_null($ids)) {
            $single = $request->input('id');
            if ($single) $ids = [$single];
        }

        $ids = (array) $ids;
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất 1 vé để in.');
        }

        $tickets = DatVe::with(['suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe.loaiGhe', 'chiTietCombo.combo', 'chiTietFood.food', 'nguoiDung', 'thanhToan', 'khuyenMai'])
            ->whereIn('id', $ids)
            ->where('trang_thai', 1)
            ->get();

        // Chỉ cho phép in 1 lần
        $alreadyPrinted = $tickets->filter(function ($t) {
            return (bool) ($t->da_in ?? false);
        });
        if ($alreadyPrinted->isNotEmpty()) {
            $first = $alreadyPrinted->first();
            $printedAt = $first->thoi_gian_in ? $first->thoi_gian_in->format('d/m/Y H:i:s') : 'N/A';
            return redirect()->back()->with('error', 'Vé này đã được in trước đó (' . $printedAt . '). Không thể in lại.');
        }

        // Build per-seat printable items
        $printItems = [];
        foreach ($tickets as $ticket) {
            foreach ($ticket->chiTietDatVe as $detail) {
                $printItems[] = [
                    'booking' => $ticket,
                    'seat' => isset($detail->ghe->so_ghe) ? $detail->ghe->so_ghe : 'N/A',
                    'price' => $detail->gia ?? ($detail->gia_ve ?? 0),
                ];
            }
        }

        // Build per-combo printable items (each combo line = one separate voucher)
        $comboPrintItems = [];
        foreach ($tickets as $ticket) {
            foreach (($ticket->chiTietCombo ?? collect()) as $comboDetail) {
                $combo = $comboDetail->combo;
                $comboName = $combo->ten ?? $combo->ten_combo ?? 'Combo';
                $qty = max(1, (int)($comboDetail->so_luong ?? 1));
                $unit = (float)($comboDetail->gia_ap_dung ?? ($combo->gia ?? 0));
                $comboPrintItems[] = [
                    'booking' => $ticket,
                    'combo' => $combo,
                    'name' => $comboName,
                    'qty' => $qty,
                    'unit_price' => $unit,
                    'total' => $unit * $qty,
                ];
            }
        }

        // Build per-food printable items (each food line = one separate voucher)
        $foodPrintItems = [];
        foreach ($tickets as $ticket) {
            foreach (($ticket->chiTietFood ?? collect()) as $foodDetail) {
                $food = $foodDetail->food;
                $foodName = $food->name ?? 'Đồ ăn';
                $qty = max(1, (int)($foodDetail->quantity ?? 1));
                $unit = (float)($foodDetail->price ?? ($food->price ?? 0));
                $foodPrintItems[] = [
                    'booking' => $ticket,
                    'food' => $food,
                    'name' => $foodName,
                    'qty' => $qty,
                    'unit_price' => $unit,
                    'total' => $unit * $qty,
                ];
            }
        }

        $bookings = $tickets;
        return view('admin.scan.print-multiple', compact('printItems', 'comboPrintItems', 'foodPrintItems', 'bookings'));
    }

    /**
     * Mark multiple tickets as printed (POST).
     * Accepts `ids[]` array of booking ids.
     */
    public function markMultiplePrinted(Request $request)
    {
        $ids = $request->input('ids', []);
        $ids = array_map('intval', (array)$ids);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Không có vé để đánh dấu.'], 400);
        }

        // Chặn đánh dấu lại nếu đã in
        $alreadyPrinted = DatVe::whereIn('id', $ids)
            ->where('da_in', 1)
            ->first();
        if ($alreadyPrinted) {
            return response()->json([
                'success' => false,
                'message' => 'Vé này đã được in trước đó. Không thể in lại.',
                'printed_at' => $alreadyPrinted->thoi_gian_in ? $alreadyPrinted->thoi_gian_in->format('d/m/Y H:i:s') : null,
            ], 400);
        }

        $now = now();
        DB::table('dat_ve')->whereIn('id', $ids)->update(['da_in' => 1, 'thoi_gian_in' => $now]);

        return response()->json(['success' => true, 'message' => 'Đã đánh dấu các vé là đã in', 'count' => count($ids)]);
    }

    /**
     * Render the client ticket detail view inside admin (adminMode=true)
     */
    public function clientPrintView($id)
    {
        $booking = DatVe::with(['suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe.loaiGhe', 'chiTietCombo.combo', 'chiTietFood.food', 'nguoiDung', 'thanhToan', 'khuyenMai'])
            ->findOrFail($id);

        return view('booking.ticket-detail', ['booking' => $booking, 'adminMode' => true]);
    }
}
