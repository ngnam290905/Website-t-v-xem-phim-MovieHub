<?php

namespace App\Http\Controllers;

use App\Models\SuatChieu;
use App\Models\Ghe;
use App\Services\ShowtimeSeatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShowtimeSeatController extends Controller
{
    protected $seatService;

    public function __construct(ShowtimeSeatService $seatService)
    {
        $this->seatService = $seatService;
    }

    /**
     * Get seat layout for a showtime
     */
    public function getSeatLayout(SuatChieu $showtime)
    {
        try {
            $seats = $this->seatService->getSeatsWithStatus($showtime);
            
            return response()->json([
                'success' => true,
                'showtime' => [
                    'id' => $showtime->id,
                    'movie' => $showtime->phim ? $showtime->phim->ten_phim : null,
                    'room' => $showtime->phongChieu ? $showtime->phongChieu->name : null,
                    'start_time' => $showtime->thoi_gian_bat_dau,
                    'end_time' => $showtime->thoi_gian_ket_thuc,
                ],
                'seats' => $seats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải sơ đồ ghế: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hold seats temporarily
     */
    public function holdSeats(Request $request, SuatChieu $showtime)
    {
        $request->validate([
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'required|integer|exists:ghe,id',
        ]);

        $userId = auth()->id();
        $result = $this->seatService->holdSeats($showtime, $request->seat_ids, $userId);

        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Release seat hold
     */
    public function releaseSeats(Request $request, SuatChieu $showtime)
    {
        $request->validate([
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'required|integer|exists:ghe,id',
        ]);

        try {
            foreach ($request->seat_ids as $seatId) {
                $seat = Ghe::findOrFail($seatId);
                $this->seatService->releaseExpiredHolds($showtime);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã giải phóng ghế thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi giải phóng ghế: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get seat management interface
     */
    public function manage(SuatChieu $showtime)
    {
        $showtime->load(['phim', 'phongChieu', 'phongChieu.seats.seatType']);
        
        $seats = $this->seatService->getSeatsWithStatus($showtime);
        
        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.showtime-seats.manage', compact('showtime', 'seats'));
        }
        
        return view('admin.showtime-seats.manage', compact('showtime', 'seats'));
    }

    /**
     * Admin: Manually book seat (for counter booking)
     */
    public function adminBookSeat(Request $request, SuatChieu $showtime, Ghe $seat)
    {
        try {
            $this->seatService->adminBookSeat($showtime, $seat);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã đặt ghế thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Admin: Cancel seat booking
     */
    public function adminCancelSeat(Request $request, SuatChieu $showtime, Ghe $seat)
    {
        try {
            $this->seatService->adminCancelSeat($showtime, $seat);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã hủy ghế thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Admin: Transfer seat to another showtime
     */
    public function adminTransferSeat(Request $request, SuatChieu $showtime, Ghe $seat)
    {
        $request->validate([
            'target_showtime_id' => 'required|exists:suat_chieu,id',
        ]);

        try {
            $targetShowtime = SuatChieu::findOrFail($request->target_showtime_id);
            $this->seatService->adminTransferSeat($showtime, $targetShowtime, $seat);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã chuyển ghế sang suất chiếu khác thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get seat selection history
     */
    public function getSeatHistory(SuatChieu $showtime, Ghe $seat)
    {
        try {
            $bookings = \App\Models\ChiTietDatVe::whereHas('datVe', function($query) use ($showtime) {
                    $query->where('id_suat_chieu', $showtime->id);
                })
                ->where('id_ghe', $seat->id)
                ->with(['datVe.user', 'datVe.thanhToan'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'history' => $bookings->map(function($item) {
                    return [
                        'booking_id' => $item->datVe->id,
                        'user' => $item->datVe->user ? $item->datVe->user->ten : 'Guest',
                        'booking_time' => $item->created_at,
                        'payment_status' => $item->datVe->trang_thai == 1 ? 'paid' : 'pending',
                        'price' => $item->gia_ve,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy lịch sử: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Release expired holds (can be called by cron)
     */
    public function releaseExpiredHolds(SuatChieu $showtime = null)
    {
        try {
            $count = $this->seatService->releaseExpiredHolds($showtime);
            
            return response()->json([
                'success' => true,
                'message' => "Đã giải phóng {$count} ghế hết hạn",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi giải phóng ghế hết hạn: ' . $e->getMessage()
            ], 500);
        }
    }
}

