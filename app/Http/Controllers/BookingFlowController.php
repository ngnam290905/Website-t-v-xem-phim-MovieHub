<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use App\Models\SuatChieu;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingFlowController extends Controller
{
    /**
     * Display booking page - step 1: select movie
     */
    public function index()
    {
        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->get();

        return view('booking.index', compact('movies'));
    }

    /**
     * Display showtimes selection page - step 2: select date and showtime
     */
    public function showtimes($movieId)
    {
        $movie = Phim::findOrFail($movieId);

        if ($movie->trang_thai !== 'dang_chieu') {
            return redirect()->route('booking.index')
                ->with('error', 'Phim này không còn đang chiếu.');
        }

        $selectedDate = request()->get('date', now()->format('Y-m-d'));

        return view('booking.showtimes', compact('movie', 'selectedDate'));
    }

    /**
     * API: Get showtimes by movie and date
     */
    public function getShowtimesByDate(Request $request, $movieId)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $movie = Phim::findOrFail($movieId);
        $date = Carbon::parse($request->date)->format('Y-m-d');

        $showtimes = SuatChieu::where('id_phim', $movieId)
            ->where('trang_thai', 1)
            ->whereDate('thoi_gian_bat_dau', $date)
            ->where('thoi_gian_bat_dau', '>', now()) // Chỉ lấy showtimes chưa bắt đầu
            ->with(['phongChieu'])
            ->orderBy('thoi_gian_bat_dau')
            ->get()
            ->map(function ($showtime) {
                return [
                    'id' => $showtime->id,
                    'time' => $showtime->thoi_gian_bat_dau->format('H:i'),
                    'end_time' => $showtime->thoi_gian_ket_thuc->format('H:i'),
                    'room_name' => $showtime->phongChieu->ten_phong ?? $showtime->phongChieu->name ?? 'Phòng chiếu',
                    'room_type' => $showtime->phongChieu->loai_phong ?? 'normal',
                    'date' => $showtime->thoi_gian_bat_dau->format('Y-m-d'),
                    'datetime' => $showtime->thoi_gian_bat_dau->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $showtimes,
            'date' => $date,
        ]);
    }

    /**
     * API: Get available dates for a movie (next 7 days)
     */
    public function getAvailableDates($movieId)
    {
        $movie = Phim::findOrFail($movieId);

        $dates = SuatChieu::where('id_phim', $movieId)
            ->where('trang_thai', 1)
            ->where('thoi_gian_bat_dau', '>', now())
            ->where('thoi_gian_bat_dau', '<=', now()->addDays(7))
            ->selectRaw('DATE(thoi_gian_bat_dau) as date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date')
            ->map(function ($date) {
                $carbon = Carbon::parse($date);
                return [
                    'date' => $date,
                    'formatted' => $carbon->format('d/m/Y'),
                    'day_name' => $carbon->format('l'),
                    'is_today' => $carbon->isToday(),
                    'is_tomorrow' => $carbon->isTomorrow(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $dates,
        ]);
    }
}

