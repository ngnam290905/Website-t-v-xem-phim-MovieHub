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
        try {
            \Log::info('=== getShowtimesByDate START ===', [
                'movie_id' => $movieId,
                'request_date' => $request->get('date'),
                'all_params' => $request->all(),
            ]);

        $request->validate([
            'date' => 'required|date',
        ]);

        $movie = Phim::findOrFail($movieId);
        $date = Carbon::parse($request->date)->format('Y-m-d');
            $today = Carbon::today()->format('Y-m-d');
            $isToday = ($date === $today);
            $now = now();

            \Log::info('getShowtimesByDate - After parse', [
                'movie_id' => $movieId,
                'movie_name' => $movie->ten_phim,
                'date' => $date,
                'today' => $today,
                'is_today' => $isToday,
                'now' => $now->format('Y-m-d H:i:s'),
            ]);

        // For today: show all showtimes that haven't ended yet
        // For future dates: show all showtimes that haven't ended yet (including ongoing ones)
        $query = SuatChieu::where('id_phim', $movieId)
            ->where('trang_thai', 1) // Only active showtimes
            ->whereDate('thoi_gian_bat_dau', $date)
            ->whereHas('phongChieu', function($q) {
                // Only showtimes in active rooms
                $q->where('trang_thai', 1);
            })
            ->where('thoi_gian_ket_thuc', '>', $now); // Show all showtimes that haven't ended yet

        $showtimes = $query->with(['phongChieu'])
            ->orderBy('thoi_gian_bat_dau')
            ->get();

        \Log::info('Showtimes found', [
            'count' => $showtimes->count(),
            'movie_id' => $movieId,
            'date' => $date,
            'is_today' => $isToday,
            'now' => $now->format('Y-m-d H:i:s'),
            'showtimes' => $showtimes->map(function($st) {
                return [
                    'id' => $st->id,
                    'start' => $st->thoi_gian_bat_dau->format('Y-m-d H:i:s'),
                    'end' => $st->thoi_gian_ket_thuc->format('Y-m-d H:i:s'),
                    'room_id' => $st->id_phong,
                    'room_name' => $st->phongChieu ? ($st->phongChieu->name ?? $st->phongChieu->ten_phong) : 'N/A',
                    'room_status' => $st->phongChieu ? $st->phongChieu->trang_thai : 'N/A',
                ];
            })->toArray(),
        ]);

        $mapped = $showtimes->map(function ($showtime) {
            $isPast = $showtime->thoi_gian_bat_dau->lt(now());
            $isOngoing = $showtime->thoi_gian_bat_dau->lte(now()) && $showtime->thoi_gian_ket_thuc->gt(now());
            
                return [
                    'id' => $showtime->id,
                    'time' => $showtime->thoi_gian_bat_dau->format('H:i'),
                    'end_time' => $showtime->thoi_gian_ket_thuc->format('H:i'),
                    'room_name' => $showtime->phongChieu->ten_phong ?? $showtime->phongChieu->name ?? 'Phòng chiếu',
                    'room_type' => $showtime->phongChieu->loai_phong ?? 'normal',
                    'date' => $showtime->thoi_gian_bat_dau->format('Y-m-d'),
                    'datetime' => $showtime->thoi_gian_bat_dau->toIso8601String(),
                'is_past' => $isPast,
                'is_ongoing' => $isOngoing,
                ];
            });

            \Log::info('getShowtimesByDate - Response', [
                'count' => $mapped->count(),
                'date' => $date,
            ]);

        return response()->json([
            'success' => true,
                'data' => $mapped,
            'date' => $date,
                'is_today' => $isToday,
                'debug' => [
                    'today' => $today,
                    'now' => $now->format('Y-m-d H:i:s'),
                    'raw_count' => $showtimes->count(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('getShowtimesByDate ERROR', [
                'movie_id' => $movieId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * API: Get available dates for a movie (next 7 days)
     */
    public function getAvailableDates($movieId)
    {
        $movie = Phim::findOrFail($movieId);
        $today = Carbon::today()->format('Y-m-d');
        $now = now();

        // Get dates with showtimes that haven't ended yet (for all dates)
        $dates = SuatChieu::where('id_phim', $movieId)
            ->where('trang_thai', 1) // Only active showtimes
            ->whereHas('phongChieu', function($q) {
                // Only showtimes in active rooms
                $q->where('trang_thai', 1);
            })
            ->where('thoi_gian_ket_thuc', '>', $now) // Show all showtimes that haven't ended yet
            ->where('thoi_gian_bat_dau', '<=', $now->copy()->addDays(7)) // Limit to next 7 days
            ->selectRaw('DATE(thoi_gian_bat_dau) as date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date')
            ->map(function ($date) {
                $carbon = Carbon::parse($date);
                $todayStr = Carbon::today()->format('Y-m-d');
                return [
                    'date' => $date,
                    'formatted' => $carbon->format('d/m/Y'),
                    'day_name' => $carbon->format('l'),
                    'is_today' => ($date === $todayStr),
                    'is_tomorrow' => $carbon->isTomorrow(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $dates,
        ]);
    }
}

