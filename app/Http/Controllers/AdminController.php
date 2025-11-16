<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Phim;
use App\Models\DatVe;
use App\Models\ChiTietDatVe;
use App\Models\NguoiDung;
use App\Models\SuatChieu;
use App\Models\PhongChieu;
use App\Models\Ghe;
use App\Models\KhuyenMai;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
  /**
   * Display the admin dashboard.
   */
  public function dashboard()
  {
    // Check if this is staff route
    if (request()->is('staff/*')) {
      return view('staff.dashboard');
    }

    // Thống kê doanh thu
    $todayRevenue = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
        ->where('dat_ve.trang_thai', 1)
        ->whereDate('dat_ve.created_at', Carbon::today())
        ->sum('chi_tiet_dat_ve.gia_ve');

    $monthRevenue = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
        ->where('dat_ve.trang_thai', 1)
        ->whereMonth('dat_ve.created_at', Carbon::now()->month)
        ->whereYear('dat_ve.created_at', Carbon::now()->year)
        ->sum('chi_tiet_dat_ve.gia_ve');

    // Thống kê số lượng
    $totalMovies = Phim::where('trang_thai', 1)->count();
    $totalCustomers = NguoiDung::where('trang_thai', 1)->count();
    $totalBookings = DatVe::where('trang_thai', 1)->count();
    $totalRooms = PhongChieu::where('status', 'active')->count();
    $totalSeats = Ghe::count();

    // Suất chiếu hôm nay
    $todayShowtimes = SuatChieu::whereDate('thoi_gian_bat_dau', Carbon::today())
        ->where('trang_thai', 1)
        ->count();

    // Đặt vé hôm nay
    $todayBookings = DatVe::where('trang_thai', 1)
        ->whereDate('created_at', Carbon::today())
        ->count();

    // Khuyến mãi đang áp dụng
    $activePromotions = KhuyenMai::where('trang_thai', 1)
        ->where('ngay_bat_dau', '<=', Carbon::today())
        ->where('ngay_ket_thuc', '>=', Carbon::today())
        ->count();

    // Đặt vé gần đây
    $recentBookings = DatVe::with(['nguoiDung', 'suatChieu.phim', 'chiTietDatVe'])
        ->where('trang_thai', 1)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    // Phim phổ biến (top 5 phim có nhiều đặt vé nhất)
    $topMovies = Phim::join('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
        ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
        ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
        ->where('dat_ve.trang_thai', 1)
        ->whereMonth('dat_ve.created_at', Carbon::now()->month)
        ->select('phim.id', 'phim.ten_phim', 'phim.poster')
        ->selectRaw('COUNT(chi_tiet_dat_ve.id) as total_tickets')
        ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster')
        ->orderBy('total_tickets', 'desc')
        ->limit(5)
        ->get();
    
    return view('admin.dashboard', compact(
        'todayRevenue',
        'monthRevenue',
        'totalMovies',
        'totalCustomers',
        'totalBookings',
        'totalRooms',
        'totalSeats',
        'todayShowtimes',
        'todayBookings',
        'activePromotions',
        'recentBookings',
        'topMovies'
    ));
  }
}


