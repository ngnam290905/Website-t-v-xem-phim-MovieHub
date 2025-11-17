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
        // Both admin and staff use the same dashboard
        $user = auth()->user();
        
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
        $todayBookings = DatVe::where('trang_thai', 1)
            ->whereDate('created_at', Carbon::today())
            ->count();
        $totalRooms = PhongChieu::where('status', 'active')->count();
        $totalSeats = Ghe::count();

        // Khuyến mãi đang áp dụng
        $activePromotions = KhuyenMai::where('trang_thai', 1)
            ->where('ngay_bat_dau', '<=', Carbon::today())
            ->where('ngay_ket_thuc', '>=', Carbon::today())
            ->count();

        // Suất chiếu hôm nay
        $todayShowtimes = SuatChieu::whereDate('thoi_gian_bat_dau', Carbon::today())
            ->where('trang_thai', 1)
            ->with(['phim', 'phongChieu'])
            ->orderBy('thoi_gian_bat_dau')
            ->get();

        // Đặt vé gần đây
        $recentBookings = DatVe::with(['showtime.phim', 'showtime.phongChieu', 'nguoiDung'])
            ->latest()
            ->take(5)
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
            ->orderByDesc('total_tickets')
            ->limit(5)
            ->get();

        // Doanh thu theo tháng (12 tháng gần nhất)
        $revenueByMonth = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->select(
                DB::raw('YEAR(dat_ve.created_at) as year'),
                DB::raw('MONTH(dat_ve.created_at) as month'),
                DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total')
            )
            ->where('dat_ve.trang_thai', 1)
            ->where('dat_ve.created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Format dữ liệu cho biểu đồ
        $revenueLabels = [];
        $revenueData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenueLabels[] = $date->format('m/Y');
            
            $revenue = $revenueByMonth->first(function ($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });
            
            $revenueData[] = $revenue ? $revenue->total : 0;
        }

        return view('admin.dashboard', compact(
            'todayRevenue',
            'monthRevenue',
            'todayBookings',
            'activePromotions',
            'totalMovies',
            'totalCustomers',
            'totalBookings',
            'totalRooms',
            'totalSeats',
            'todayShowtimes',
            'recentBookings',
            'topMovies',
            'revenueLabels',
            'revenueData'
        ));
    }
}
