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

        // Aggregate revenue per booking to avoid row inflation
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');

        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $query = DB::table('dat_ve')
            ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','dat_ve.id'); })
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
            DB::raw('DATE(dat_ve.created_at) as date'),
            DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_revenue'),
            DB::raw('SUM(COALESCE(s.seat_total,0) > 0) as booking_count'),
            DB::raw('SUM((SELECT COUNT(*) FROM chi_tiet_dat_ve d2 WHERE d2.id_dat_ve = dat_ve.id)) as total_tickets')
        )
        ->groupBy(DB::raw('DATE(dat_ve.created_at)'))
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

        // Build per-booking aggregates then sum by movie
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $query = DB::table('phim')
            ->join('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','dat_ve.id'); })
            ->where('dat_ve.trang_thai', 1);

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

        $topMovies = $query->select(
            'phim.id',
            'phim.ten_phim',
            'phim.poster',
            DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_revenue'),
            DB::raw('SUM((SELECT COUNT(*) FROM chi_tiet_dat_ve d2 WHERE d2.id_dat_ve = dat_ve.id)) as total_tickets')
        )
        ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster')
        ->orderBy('total_revenue', 'desc')
        ->limit($limit)
        ->get();

        return response()->json([
            'top_movies' => $topMovies,
            'period' => $period
        ]);
    }

    public function topCustomers(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 'month');

        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $query = DB::table('nguoi_dung')
            ->join('dat_ve', 'nguoi_dung.id', '=', 'dat_ve.id_nguoi_dung')
            ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','dat_ve.id'); })
            ->where('dat_ve.trang_thai', 1);

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

        $topCustomers = $query->select(
            'nguoi_dung.id',
            'nguoi_dung.ho_ten',
            'nguoi_dung.email',
            'nguoi_dung.sdt',
            DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_spent'),
            DB::raw('SUM((SELECT COUNT(*) FROM chi_tiet_dat_ve d2 WHERE d2.id_dat_ve = dat_ve.id)) as total_tickets')
        )
        ->groupBy('nguoi_dung.id', 'nguoi_dung.ho_ten', 'nguoi_dung.email', 'nguoi_dung.sdt')
        ->orderBy('total_spent', 'desc')
        ->limit($limit)
        ->get();

        return response()->json([
            'top_customers' => $topCustomers,
            'period' => $period
        ]);
    }

    public function dashboard()
    {
        // Dashboard: sum seats + combos
        $todaySeat = DB::table('chi_tiet_dat_ve as d')
            ->join('dat_ve as v', 'v.id', '=', 'd.id_dat_ve')
            ->where('v.trang_thai', 1)
            ->whereDate('v.created_at', Carbon::today())
            ->sum('d.gia');
        $todayCombo = DB::table('chi_tiet_dat_ve_combo as c')
            ->join('dat_ve as v', 'v.id', '=', 'c.id_dat_ve')
            ->where('v.trang_thai', 1)
            ->whereDate('v.created_at', Carbon::today())
            ->sum(DB::raw('c.gia_ap_dung * COALESCE(c.so_luong,1)'));
        $todayRevenue = $todaySeat + $todayCombo;

        $monthSeat = DB::table('chi_tiet_dat_ve as d')
            ->join('dat_ve as v', 'v.id', '=', 'd.id_dat_ve')
            ->where('v.trang_thai', 1)
            ->whereMonth('v.created_at', Carbon::now()->month)
            ->whereYear('v.created_at', Carbon::now()->year)
            ->sum('d.gia');
        $monthCombo = DB::table('chi_tiet_dat_ve_combo as c')
            ->join('dat_ve as v', 'v.id', '=', 'c.id_dat_ve')
            ->where('v.trang_thai', 1)
            ->whereMonth('v.created_at', Carbon::now()->month)
            ->whereYear('v.created_at', Carbon::now()->year)
            ->sum(DB::raw('c.gia_ap_dung * COALESCE(c.so_luong,1)'));
        $monthRevenue = $monthSeat + $monthCombo;

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
