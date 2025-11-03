<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Phim;
use App\Models\DatVe;
use App\Models\ChiTietDatVe;
use App\Models\NguoiDung;
use App\Models\SuatChieu;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function revenue(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1);

        if ($startDate && $endDate) {
            $query->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
        } else {
            switch ($period) {
                case 'today':
                    $query->whereDate('dat_ve.created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('dat_ve.created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('dat_ve.created_at', Carbon::now()->month)
                          ->whereYear('dat_ve.created_at', Carbon::now()->year);
                    break;
                case 'year':
                    $query->whereYear('dat_ve.created_at', Carbon::now()->year);
                    break;
            }
        }

        $revenueData = $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(gia_ve) as total_revenue'),
            DB::raw('COUNT(*) as total_tickets')
        )
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        $totalRevenue = $revenueData->sum('total_revenue');
        $totalTickets = $revenueData->sum('total_tickets');

        return response()->json([
            'revenue_data' => $revenueData,
            'total_revenue' => $totalRevenue,
            'total_tickets' => $totalTickets,
            'period' => $period
        ]);
    }

    public function topMovies(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Phim::join('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1);


        if ($startDate && $endDate) {
            $query->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
        } else {
            switch ($period) {
                case 'today':
                    $query->whereDate('dat_ve.created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('dat_ve.created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('dat_ve.created_at', Carbon::now()->month)
                          ->whereYear('dat_ve.created_at', Carbon::now()->year);
                    break;
                case 'year':
                    $query->whereYear('dat_ve.created_at', Carbon::now()->year);
                    break;
            }
        }

        $topMovies = $query->select(
            'phim.id',
            'phim.ten_phim',
            'phim.poster',
            DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue'),
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets')
        )
        ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster')
        ->orderBy('total_revenue', 'desc')
        ;

        if ($limit && intval($limit) > 0) {
            $topMovies = $topMovies->limit(intval($limit))->get();
        } else {
            $topMovies = $topMovies->get();
        }

        return response()->json([
            'top_movies' => $topMovies,
            'period' => $period
        ]);
    }

    public function topCustomers(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = NguoiDung::join('dat_ve', 'nguoi_dung.id', '=', 'dat_ve.id_nguoi_dung')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1);


        if ($startDate && $endDate) {
            $query->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
        } else {
            switch ($period) {
                case 'today':
                    $query->whereDate('dat_ve.created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('dat_ve.created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('dat_ve.created_at', Carbon::now()->month)
                          ->whereYear('dat_ve.created_at', Carbon::now()->year);
                    break;
                case 'year':
                    $query->whereYear('dat_ve.created_at', Carbon::now()->year);
                    break;
            }
        }

        $topCustomers = $query->select(
            'nguoi_dung.id',
            'nguoi_dung.ho_ten',
            'nguoi_dung.email',
            'nguoi_dung.sdt',
            DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_spent'),
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets')
        )
        ->groupBy('nguoi_dung.id', 'nguoi_dung.ho_ten', 'nguoi_dung.email', 'nguoi_dung.sdt')
        ->orderBy('total_spent', 'desc')
        ;

        if ($limit && intval($limit) > 0) {
            $topCustomers = $topCustomers->limit(intval($limit))->get();
        } else {
            $topCustomers = $topCustomers->get();
        }

        return response()->json([
            'top_customers' => $topCustomers,
            'period' => $period
        ]);
    }

    public function topShowtimes(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = SuatChieu::join('phim', 'suat_chieu.id_phim', '=', 'phim.id')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1);

        if ($startDate && $endDate) {
            $query->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
        } else {
            switch ($period) {
                case 'today':
                    $query->whereDate('dat_ve.created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('dat_ve.created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('dat_ve.created_at', Carbon::now()->month)
                          ->whereYear('dat_ve.created_at', Carbon::now()->year);
                    break;
                case 'year':
                    $query->whereYear('dat_ve.created_at', Carbon::now()->year);
                    break;
            }
        }

        $topShowtimes = $query->select(
            'suat_chieu.id',
            'phim.ten_phim',
            'suat_chieu.thoi_gian',
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
            DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue')
        )
        ->groupBy('suat_chieu.id', 'phim.ten_phim', 'suat_chieu.thoi_gian')
        ->orderBy('total_tickets', 'desc')
        ->limit(intval($limit))
        ->get();

        return response()->json([
            'top_showtimes' => $topShowtimes,
            'period' => $period
        ]);
    }

    public function dashboard()
    {
        $todayRevenue = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereDate('dat_ve.created_at', Carbon::today())
            ->sum('chi_tiet_dat_ve.gia_ve');

        $monthRevenue = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereMonth('dat_ve.created_at', Carbon::now()->month)
            ->whereYear('dat_ve.created_at', Carbon::now()->year)
            ->sum('chi_tiet_dat_ve.gia_ve');

        $totalCustomers = NguoiDung::where('trang_thai', 1)->count();
        $totalMovies = Phim::where('trang_thai', 1)->count();
        $totalBookings = DatVe::where('trang_thai', 1)->count();

        $recentBookings = DatVe::with(['nguoiDung', 'suatChieu.phim'])
            ->where('trang_thai', 1)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.reports.dashboard', compact(
            'todayRevenue',
            'monthRevenue',
            'totalCustomers',
            'totalMovies',
            'totalBookings',
            'recentBookings'
        ));
    }
}
