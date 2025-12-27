<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DatVe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ScanController extends Controller
{
    public function index()
    {
        return view('staff.scan');
    }

    public function checkTicket(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        
        if (!$ticketId) {
            return response()->json([
                'valid' => false,
                'message' => 'Vui lòng nhập mã vé'
            ]);
        }

        // Extract ticket ID from QR code format
        // Support formats: ticket_id=123, ticket_id=MV000123, MV000123, 123
        $rawTicketId = trim($ticketId);
        if (preg_match('/ticket_id[=:]([A-Za-z0-9]+)/i', $rawTicketId, $matches)) {
            $ticketId = $matches[1];
        } else {
            $ticketId = $rawTicketId;
        }

        // Find ticket by ID or ticket_code
        // Try numeric ID first, then ticket_code
        $ticket = null;
        
        // If ticketId is numeric, try to find by ID first
        if (is_numeric($ticketId)) {
            $ticket = DatVe::with([
                'suatChieu.phim',
                'chiTietDatVe.ghe'
            ])->where('id', (int)$ticketId)->first();
        }
        
        // If not found or ticketId is not numeric, try ticket_code
        if (!$ticket) {
            $ticket = DatVe::with([
                'suatChieu.phim',
                'chiTietDatVe.ghe'
            ])->where(function($q) use ($ticketId) {
                $q->where('ticket_code', $ticketId);
                // Also try numeric ID if ticketId contains numbers
                if (is_numeric($ticketId)) {
                    $q->orWhere('id', (int)$ticketId);
                }
            })->first();
        }

        if (!$ticket) {
            return response()->json([
                'valid' => false,
                'message' => 'Vé không tồn tại'
            ]);
        }

        // Check if ticket is paid (trang_thai = 1 means paid)
        if ($ticket->trang_thai != 1) {
            return response()->json([
                'valid' => false,
                'message' => 'Vé chưa thanh toán'
            ]);
        }

        // Check if ticket already checked in
        if ($ticket->checked_in) {
            return response()->json([
                'valid' => false,
                'message' => 'Vé này đã được quét trước đó'
            ]);
        }

        $showtimeStart = optional($ticket->suatChieu)->thoi_gian_bat_dau ? Carbon::parse($ticket->suatChieu->thoi_gian_bat_dau) : null;
        if (!$showtimeStart) {
            return response()->json([
                'valid' => false,
                'message' => 'Vé không có suất chiếu hợp lệ'
            ]);
        }
        $now = Carbon::now();
        // Chỉ kiểm tra vé đã hết hạn (sau giờ chiếu hoặc sau expires_at)
        if (($ticket->expires_at && $now->greaterThan(Carbon::parse($ticket->expires_at))) || $now->greaterThanOrEqualTo($showtimeStart)) {
            return response()->json([
                'valid' => false,
                'message' => 'Vé đã hết hạn'
            ]);
        }

        // Ticket is valid - return ticket info
        $seats = $ticket->chiTietDatVe->map(function($detail) {
            return $detail->ghe->so_ghe ?? 'N/A';
        })->filter()->implode(', ');

        return response()->json([
            'valid' => true,
            'ticket' => [
                'id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code ?? 'N/A',
                'movie' => $ticket->suatChieu->phim->ten_phim ?? 'N/A',
                'seats' => $seats,
                'showtime' => $ticket->suatChieu->thoi_gian_bat_dau ? 
                    $ticket->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') : 'N/A'
            ]
        ]);
    }

    public function confirmCheckIn(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        
        if (!$ticketId) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã vé'
            ]);
        }

        // Extract ticket ID
        $rawTicketId = trim($ticketId);
        if (preg_match('/ticket_id[=:]([A-Za-z0-9]+)/i', $rawTicketId, $matches)) {
            $ticketId = $matches[1];
        } else {
            $ticketId = $rawTicketId;
        }

        // Try numeric ID first, then ticket_code
        $ticket = null;
        if (is_numeric($ticketId)) {
            $ticket = DatVe::where('id', (int)$ticketId)->first();
        }
        
        if (!$ticket) {
            $ticket = DatVe::where(function($q) use ($ticketId) {
                $q->where('ticket_code', $ticketId);
                if (is_numeric($ticketId)) {
                    $q->orWhere('id', (int)$ticketId);
                }
            })->first();
        }

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Vé không tồn tại'
            ]);
        }

        if ($ticket->trang_thai != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Vé chưa thanh toán'
            ]);
        }

        if ($ticket->checked_in) {
            return response()->json([
                'success' => false,
                'message' => 'Vé này đã được quét trước đó'
            ]);
        }

        $showtimeStart = optional($ticket->suatChieu)->thoi_gian_bat_dau ? Carbon::parse($ticket->suatChieu->thoi_gian_bat_dau) : null;
        if (!$showtimeStart) {
            return response()->json([
                'success' => false,
                'message' => 'Vé không có suất chiếu hợp lệ'
            ]);
        }
        $now = Carbon::now();
        // Chỉ kiểm tra vé đã hết hạn (sau giờ chiếu hoặc sau expires_at)
        if (($ticket->expires_at && $now->greaterThan(Carbon::parse($ticket->expires_at))) || $now->greaterThanOrEqualTo($showtimeStart)) {
            return response()->json([
                'success' => false,
                'message' => 'Vé đã hết hạn'
            ]);
        }

        // Mark as checked in
        $ticket->checked_in = true;
        $ticket->save();

        Log::info('Ticket checked in', [
            'ticket_id' => $ticket->id,
            'staff_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Xác nhận thành công'
        ]);
    }
}
