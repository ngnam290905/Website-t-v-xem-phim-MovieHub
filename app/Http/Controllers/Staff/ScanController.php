<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DatVe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        // Extract ticket ID from QR code format (ticket_id=1234 or just 1234)
        if (preg_match('/ticket_id[=:](\d+)/i', $ticketId, $matches)) {
            $ticketId = $matches[1];
        } elseif (preg_match('/(\d+)/', $ticketId, $matches)) {
            $ticketId = $matches[1];
        }

        // Find ticket by ID or ticket_code
        $ticket = DatVe::with([
            'suatChieu.phim',
            'chiTietDatVe.ghe'
        ])->where('id', $ticketId)
          ->orWhere('ticket_code', $ticketId)
          ->first();

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
        if (preg_match('/ticket_id[=:](\d+)/i', $ticketId, $matches)) {
            $ticketId = $matches[1];
        } elseif (preg_match('/(\d+)/', $ticketId, $matches)) {
            $ticketId = $matches[1];
        }

        $ticket = DatVe::where('id', $ticketId)
                      ->orWhere('ticket_code', $ticketId)
                      ->first();

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
