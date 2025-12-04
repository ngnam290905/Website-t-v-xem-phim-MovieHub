<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use App\Models\SuatChieu;
use App\Models\PhongChieu;
use App\Models\Ghe;
use App\Models\Combo;
use App\Models\DatVe;
use App\Models\ChiTietDatVe;
use App\Models\ChiTietCombo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingDataController extends Controller
{
    /**
     * Display booking data overview
     */
    public function index()
    {
        $stats = [
            'total_movies' => Phim::where('trang_thai', 'dang_chieu')->count(),
            'total_rooms' => PhongChieu::where('trang_thai', 1)->count(),
            'total_seats' => Ghe::count(),
            'total_shows' => SuatChieu::where('trang_thai', 1)->count(),
            'total_combos' => Combo::where('trang_thai', 1)->count(),
            'total_bookings' => DatVe::count(),
            'paid_bookings' => DatVe::where('trang_thai', 'PAID')->count(),
            'pending_bookings' => DatVe::where('trang_thai', 'PENDING')->count(),
        ];

        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->withCount('suatChieu')
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->take(10)
            ->get();

        $rooms = PhongChieu::where('trang_thai', 1)
            ->withCount('seats')
            ->get();

        $combos = Combo::where('trang_thai', 1)
            ->orderBy('gia', 'asc')
            ->get();

        $recentBookings = DatVe::with([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe',
            'chiTietCombo'
        ])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('booking.data', compact('stats', 'movies', 'rooms', 'combos', 'recentBookings'));
    }

    /**
     * Display movie details with showtimes
     */
    public function movie($id)
    {
        $movie = Phim::with(['suatChieu' => function($query) {
            $query->where('trang_thai', 1)
                  ->where('thoi_gian_ket_thuc', '>', now()) // Only showtimes that haven't ended
                  ->orderBy('thoi_gian_bat_dau');
        }, 'suatChieu.phongChieu'])
            ->findOrFail($id);

        $showtimesByDate = $movie->suatChieu->groupBy(function($showtime) {
            return $showtime->thoi_gian_bat_dau->format('Y-m-d');
        });

        return view('booking.movie-data', compact('movie', 'showtimesByDate'));
    }

    /**
     * Display room details with seats
     */
    public function room($id)
    {
        $room = PhongChieu::with(['seats.seatType', 'showtimes' => function($query) {
            $query->where('trang_thai', 1)
                  ->where('thoi_gian_ket_thuc', '>', now()) // Only showtimes that haven't ended
                  ->orderBy('thoi_gian_bat_dau')
                  ->take(5);
        }, 'showtimes.phim'])
            ->findOrFail($id);

        $seatsByRow = $room->seats->groupBy('so_hang');

        return view('booking.room-data', compact('room', 'seatsByRow'));
    }

    /**
     * Display showtime details with seat map
     */
    public function showtime($id)
    {
        $showtime = SuatChieu::with([
            'phim',
            'phongChieu.seats.seatType',
            'datVe.chiTietDatVe.ghe'
        ])
            ->findOrFail($id);

        $bookedSeatIds = DB::table('chi_tiet_dat_ve as ctdv')
            ->join('dat_ve as dv', 'ctdv.id_dat_ve', '=', 'dv.id')
            ->where('dv.id_suat_chieu', $id)
            ->whereIn('dv.trang_thai', ['PAID', 'CONFIRMED', 'PENDING'])
            ->pluck('ctdv.id_ghe')
            ->toArray();

        $seats = $showtime->phongChieu->seats()
            ->with('seatType')
            ->orderBy('so_hang')
            ->orderBy('so_ghe')
            ->get();

        $seatsByRow = $seats->groupBy('so_hang');

        return view('booking.showtime-data', compact('showtime', 'seatsByRow', 'bookedSeatIds'));
    }

    /**
     * Display booking details
     */
    public function booking($id)
    {
        $booking = DatVe::with([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.seatType',
            'chiTietCombo',
            'thanhToan',
            'nguoiDung'
        ])
            ->findOrFail($id);

        return view('booking.booking-data', compact('booking'));
    }
}

