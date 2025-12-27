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
use App\Models\LoaiGhe;
use App\Models\KhuyenMai;
use App\Services\MovieStatisticsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function advancedDashboard()
    {
        // Financial Overview - Tính cả vé và combo
        $todaySeatRevenue = DB::table('chi_tiet_dat_ve')
            ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereDate('dat_ve.created_at', Carbon::today())
            ->sum(DB::raw('COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)'));
        
        $todayComboRevenue = DB::table('chi_tiet_dat_ve_combo')
            ->join('dat_ve', 'chi_tiet_dat_ve_combo.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereDate('dat_ve.created_at', Carbon::today())
            ->sum(DB::raw('chi_tiet_dat_ve_combo.gia_ap_dung * COALESCE(chi_tiet_dat_ve_combo.so_luong, 1)'));
        
        $todayRevenue = $todaySeatRevenue + $todayComboRevenue;

        $monthSeatRevenue = DB::table('chi_tiet_dat_ve')
            ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereMonth('dat_ve.created_at', Carbon::now()->month)
            ->whereYear('dat_ve.created_at', Carbon::now()->year)
            ->sum(DB::raw('COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)'));
        
        $monthComboRevenue = DB::table('chi_tiet_dat_ve_combo')
            ->join('dat_ve', 'chi_tiet_dat_ve_combo.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereMonth('dat_ve.created_at', Carbon::now()->month)
            ->whereYear('dat_ve.created_at', Carbon::now()->year)
            ->sum(DB::raw('chi_tiet_dat_ve_combo.gia_ap_dung * COALESCE(chi_tiet_dat_ve_combo.so_luong, 1)'));
        
        $monthRevenue = $monthSeatRevenue + $monthComboRevenue;

        $yearSeatRevenue = DB::table('chi_tiet_dat_ve')
            ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereYear('dat_ve.created_at', Carbon::now()->year)
            ->sum(DB::raw('COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)'));
        
        $yearComboRevenue = DB::table('chi_tiet_dat_ve_combo')
            ->join('dat_ve', 'chi_tiet_dat_ve_combo.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereYear('dat_ve.created_at', Carbon::now()->year)
            ->sum(DB::raw('chi_tiet_dat_ve_combo.gia_ap_dung * COALESCE(chi_tiet_dat_ve_combo.so_luong, 1)'));
        
        $yearRevenue = $yearSeatRevenue + $yearComboRevenue;

        // Customer Analytics
        $totalCustomers = NguoiDung::where('trang_thai', 1)->count();
        $newCustomersThisMonth = NguoiDung::where('trang_thai', 1)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // VIP customers - Tính cả vé và combo
        $vipCustomers = DB::table('nguoi_dung')
            ->join('dat_ve', 'nguoi_dung.id', '=', 'dat_ve.id_nguoi_dung')
            ->leftJoin(DB::raw('(SELECT id_dat_ve, SUM(COALESCE(gia_ve, gia)) as seat_total FROM chi_tiet_dat_ve GROUP BY id_dat_ve) as seat_rev'), 'dat_ve.id', '=', 'seat_rev.id_dat_ve')
            ->leftJoin(DB::raw('(SELECT id_dat_ve, SUM(gia_ap_dung * COALESCE(so_luong, 1)) as combo_total FROM chi_tiet_dat_ve_combo GROUP BY id_dat_ve) as combo_rev'), 'dat_ve.id', '=', 'combo_rev.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->select('nguoi_dung.id', 'nguoi_dung.ho_ten', 'nguoi_dung.email')
            ->selectRaw('SUM(COALESCE(seat_rev.seat_total, 0) + COALESCE(combo_rev.combo_total, 0)) as total_spent')
            ->groupBy('nguoi_dung.id', 'nguoi_dung.ho_ten', 'nguoi_dung.email')
            ->having('total_spent', '>', 1000000) // VIP customers who spent more than 1M
            ->count();

        // Movie Performance
        $totalMovies = Phim::where('trang_thai', 1)->count();
        $activeMovies = Phim::where('trang_thai', 1)
            ->whereHas('suatChieu', function($query) {
                $query->where('ngay_chieu', '>=', Carbon::today());
            })
            ->count();

        // Booking Analytics
        $totalBookings = DatVe::where('trang_thai', 1)->count();
        $todayBookings = DatVe::where('trang_thai', 1)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $monthBookings = DatVe::where('trang_thai', 1)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Capacity Utilization
        $totalSeats = Ghe::count();
        $occupiedSeats = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereDate('dat_ve.created_at', Carbon::today())
            ->count();

        $capacityUtilization = $totalSeats > 0 ? ($occupiedSeats / $totalSeats) * 100 : 0;

        // Recent Activity
        $recentBookings = DatVe::with(['nguoiDung', 'suatChieu.phim', 'suatChieu.phongChieu'])
            ->where('trang_thai', 1)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.reports.advanced-dashboard', compact(
            'todayRevenue',
            'monthRevenue', 
            'yearRevenue',
            'totalCustomers',
            'newCustomersThisMonth',
            'vipCustomers',
            'totalMovies',
            'activeMovies',
            'totalBookings',
            'todayBookings',
            'monthBookings',
            'totalSeats',
            'occupiedSeats',
            'capacityUtilization',
            'recentBookings'
        ));
    }

    public function financialReport(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->join('phim', 'suat_chieu.id_phim', '=', 'phim.id')
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

        // Revenue by movie - Tính cả vé và combo
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(COALESCE(gia_ve, gia)) as seat_total'))
            ->groupBy('id_dat_ve');
        
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong, 1)) as combo_total'))
            ->groupBy('id_dat_ve');
        
        $revenueByMovie = DB::table('phim')
            ->join('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->leftJoinSub($seatSub, 's', function($j) { $j->on('s.id_dat_ve', '=', 'dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j) { $j->on('c.id_dat_ve', '=', 'dat_ve.id'); })
            ->leftJoin('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                return $q->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
            }, function($q) use ($period) {
                switch ($period) {
                    case 'today':
                        return $q->whereDate('dat_ve.created_at', Carbon::today());
                    case 'week':
                        return $q->whereBetween('dat_ve.created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                    case 'month':
                        return $q->whereMonth('dat_ve.created_at', Carbon::now()->month)
                              ->whereYear('dat_ve.created_at', Carbon::now()->year);
                    case 'year':
                        return $q->whereYear('dat_ve.created_at', Carbon::now()->year);
                }
            })
            ->select(
                'phim.id',
                'phim.ten_phim',
                'phim.poster',
                DB::raw('SUM(COALESCE(s.seat_total, 0) + COALESCE(c.combo_total, 0)) as total_revenue'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
                DB::raw('AVG(COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)) as avg_ticket_price')
            )
            ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Revenue by day - Tính cả vé và combo
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(COALESCE(gia_ve, gia)) as seat_total'))
            ->groupBy('id_dat_ve');
        
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong, 1)) as combo_total'))
            ->groupBy('id_dat_ve');
        
        $revenueByDay = DB::table('dat_ve')
            ->leftJoinSub($seatSub, 's', function($j) { $j->on('s.id_dat_ve', '=', 'dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j) { $j->on('c.id_dat_ve', '=', 'dat_ve.id'); })
            ->leftJoin('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                return $q->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
            }, function($q) use ($period) {
                switch ($period) {
                    case 'today':
                        return $q->whereDate('dat_ve.created_at', Carbon::today());
                    case 'week':
                        return $q->whereBetween('dat_ve.created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                    case 'month':
                        return $q->whereMonth('dat_ve.created_at', Carbon::now()->month)
                              ->whereYear('dat_ve.created_at', Carbon::now()->year);
                    case 'year':
                        return $q->whereYear('dat_ve.created_at', Carbon::now()->year);
                }
            })
            ->select(
                DB::raw('DATE(dat_ve.created_at) as date'),
                DB::raw('SUM(COALESCE(s.seat_total, 0) + COALESCE(c.combo_total, 0)) as daily_revenue'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as daily_tickets')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = $revenueByDay->sum('daily_revenue');
        $totalTickets = $revenueByDay->sum('daily_tickets');
        $avgTicketPrice = $totalTickets > 0 ? $totalRevenue / $totalTickets : 0;

        return response()->json([
            'revenue_by_movie' => $revenueByMovie,
            'revenue_by_day' => $revenueByDay,
            'total_revenue' => $totalRevenue,
            'total_tickets' => $totalTickets,
            'avg_ticket_price' => $avgTicketPrice,
            'period' => $period
        ]);
    }

    public function customerAnalytics(Request $request)
    {
        $period = $request->get('period', 'month');
        $limit = $request->get('limit', 20);

        $query = NguoiDung::join('dat_ve', 'nguoi_dung.id', '=', 'dat_ve.id_nguoi_dung')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
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

        // Top customers by spending - Tính cả vé và combo
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(COALESCE(gia_ve, gia)) as seat_total'))
            ->groupBy('id_dat_ve');
        
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong, 1)) as combo_total'))
            ->groupBy('id_dat_ve');
        
        $topCustomers = DB::table('nguoi_dung')
            ->join('dat_ve', 'nguoi_dung.id', '=', 'dat_ve.id_nguoi_dung')
            ->leftJoinSub($seatSub, 's', function($j) { $j->on('s.id_dat_ve', '=', 'dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j) { $j->on('c.id_dat_ve', '=', 'dat_ve.id'); })
            ->leftJoin('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->when($period === 'today', function($q) {
                return $q->whereDate('dat_ve.created_at', Carbon::today());
            })
            ->when($period === 'week', function($q) {
                return $q->whereBetween('dat_ve.created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            })
            ->when($period === 'month', function($q) {
                return $q->whereMonth('dat_ve.created_at', Carbon::now()->month)
                      ->whereYear('dat_ve.created_at', Carbon::now()->year);
            })
            ->when($period === 'year', function($q) {
                return $q->whereYear('dat_ve.created_at', Carbon::now()->year);
            })
            ->select(
                'nguoi_dung.id',
                'nguoi_dung.ho_ten',
                'nguoi_dung.email',
                'nguoi_dung.sdt',
                'nguoi_dung.created_at as registration_date',
                DB::raw('SUM(COALESCE(s.seat_total, 0) + COALESCE(c.combo_total, 0)) as total_spent'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
                DB::raw('AVG(COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)) as avg_ticket_price'),
                DB::raw('MAX(dat_ve.created_at) as last_booking_date')
            )
            ->groupBy('nguoi_dung.id', 'nguoi_dung.ho_ten', 'nguoi_dung.email', 'nguoi_dung.sdt', 'nguoi_dung.created_at')
            ->orderBy('total_spent', 'desc')
            ->limit($limit)
            ->get();

        // Customer segments
        $customerSegments = [
            'vip' => $topCustomers->where('total_spent', '>', 2000000)->count(),
            'regular' => $topCustomers->whereBetween('total_spent', [500000, 2000000])->count(),
            'new' => $topCustomers->where('total_spent', '<', 500000)->count()
        ];

        // Customer retention
        $totalCustomers = NguoiDung::where('trang_thai', 1)->count();
        $returningCustomers = $topCustomers->where('total_tickets', '>', 1)->count();
        $retentionRate = $totalCustomers > 0 ? ($returningCustomers / $totalCustomers) * 100 : 0;

        return response()->json([
            'top_customers' => $topCustomers,
            'customer_segments' => $customerSegments,
            'retention_rate' => $retentionRate,
            'total_customers' => $totalCustomers,
            'returning_customers' => $returningCustomers,
            'period' => $period
        ]);
    }

    public function inventoryReport(Request $request)
    {
        // Theater capacity analysis
        $theaters = PhongChieu::with(['ghe.loaiGhe'])->get();
        $theaterCapacity = [];

        foreach ($theaters as $theater) {
            $totalSeats = $theater->ghe->count();
            $occupiedSeats = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
                ->where('suat_chieu.id_phong_chieu', $theater->id)
                ->where('dat_ve.trang_thai', 1)
                ->whereDate('dat_ve.created_at', Carbon::today())
                ->distinct('chi_tiet_dat_ve.id_ghe')
                ->count();

            $utilizationRate = $totalSeats > 0 ? ($occupiedSeats / $totalSeats) * 100 : 0;

            $theaterCapacity[] = [
                'theater_name' => $theater->ten_phong,
                'total_seats' => $totalSeats,
                'occupied_seats' => $occupiedSeats,
                'utilization_rate' => $utilizationRate,
                'available_seats' => $totalSeats - $occupiedSeats
            ];
        }

        // Seat type analysis
        $seatTypeAnalysis = LoaiGhe::with(['ghe'])->get()->map(function($seatType) {
            $totalSeats = $seatType->ghe->count();
            $occupiedSeats = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('ghe', 'chi_tiet_dat_ve.id_ghe', '=', 'ghe.id')
                ->where('ghe.id_loai_ghe', $seatType->id)
                ->where('dat_ve.trang_thai', 1)
                ->whereDate('dat_ve.created_at', Carbon::today())
                ->count();

            return [
                'seat_type' => $seatType->ten_loai,
                'total_seats' => $totalSeats,
                'occupied_seats' => $occupiedSeats,
                'utilization_rate' => $totalSeats > 0 ? ($occupiedSeats / $totalSeats) * 100 : 0,
                'revenue' => ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                    ->join('ghe', 'chi_tiet_dat_ve.id_ghe', '=', 'ghe.id')
                    ->where('ghe.id_loai_ghe', $seatType->id)
                    ->where('dat_ve.trang_thai', 1)
                    ->whereDate('dat_ve.created_at', Carbon::today())
                    ->sum(DB::raw('COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)'))
            ];
        });

        return response()->json([
            'theater_capacity' => $theaterCapacity,
            'seat_type_analysis' => $seatTypeAnalysis
        ]);
    }

    public function performanceMetrics(Request $request)
    {
        $period = $request->get('period', 'month');
        
        // Movie performance metrics
        $movieMetrics = Phim::join('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->when($period === 'today', function($q) {
                return $q->whereDate('dat_ve.created_at', Carbon::today());
            })
            ->when($period === 'week', function($q) {
                return $q->whereBetween('dat_ve.created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            })
            ->when($period === 'month', function($q) {
                return $q->whereMonth('dat_ve.created_at', Carbon::now()->month)
                      ->whereYear('dat_ve.created_at', Carbon::now()->year);
            })
            ->when($period === 'year', function($q) {
                return $q->whereYear('dat_ve.created_at', Carbon::now()->year);
            })
            ->select(
                'phim.id',
                'phim.ten_phim',
                'phim.poster',
                'phim.thoi_luong',
                'phim.ngay_khoi_chieu',
                DB::raw('SUM(COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)) as total_revenue'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
                DB::raw('AVG(COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)) as avg_ticket_price'),
                DB::raw('COUNT(DISTINCT dat_ve.id_nguoi_dung) as unique_customers')
            )
            ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster', 'phim.thoi_luong', 'phim.ngay_khoi_chieu')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Peak hours analysis
        $peakHours = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->where('dat_ve.trang_thai', 1)
            ->when($period === 'today', function($q) {
                return $q->whereDate('dat_ve.created_at', Carbon::today());
            })
            ->when($period === 'week', function($q) {
                return $q->whereBetween('dat_ve.created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            })
            ->when($period === 'month', function($q) {
                return $q->whereMonth('dat_ve.created_at', Carbon::now()->month)
                      ->whereYear('dat_ve.created_at', Carbon::now()->year);
            })
            ->when($period === 'year', function($q) {
                return $q->whereYear('dat_ve.created_at', Carbon::now()->year);
            })
            ->select(
                DB::raw('HOUR(suat_chieu.gio_chieu) as hour'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as ticket_count'),
                DB::raw('SUM(COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)) as revenue')
            )
            ->groupBy('hour')
            ->orderBy('ticket_count', 'desc')
            ->get();

        return response()->json([
            'movie_metrics' => $movieMetrics,
            'peak_hours' => $peakHours,
            'period' => $period
        ]);
    }

    public function exportReport(Request $request)
    {
        $reportType = $request->get('type', 'financial');
        $period = $request->get('period', 'month');
        $format = $request->get('format', 'excel');

        // This would typically generate and return a file download
        // For now, we'll return a JSON response with the data
        $data = [];
        
        switch ($reportType) {
            case 'financial':
                $data = $this->getFinancialData($period);
                break;
            case 'customer':
                $data = $this->getCustomerData($period);
                break;
            case 'inventory':
                $data = $this->getInventoryData($period);
                break;
        }

        return response()->json([
            'report_type' => $reportType,
            'period' => $period,
            'format' => $format,
            'data' => $data,
            'generated_at' => Carbon::now()->toISOString()
        ]);
    }

    private function getFinancialData($period)
    {
        // Implementation for financial data export
        return [];
    }

    private function getCustomerData($period)
    {
        // Implementation for customer data export
        return [];
    }

    private function getInventoryData($period)
    {
        // Implementation for inventory data export
        return [];
    }

    /**
     * Báo cáo thống kê phim hot (được mua vé nhiều nhất)
     */
    public function hotMoviesReport(Request $request)
    {
        $period = $request->get('period', 'month');
        $limit = $request->get('limit', 10);

        $query = Phim::join('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
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
            case 'all':
                // Không lọc thời gian
                break;
        }

        // Tính cả vé và combo cho hotMovies
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(COALESCE(gia_ve, gia)) as seat_total'))
            ->groupBy('id_dat_ve');
        
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong, 1)) as combo_total'))
            ->groupBy('id_dat_ve');
        
        $hotMovies = DB::table('phim')
            ->join('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->leftJoinSub($seatSub, 's', function($j) { $j->on('s.id_dat_ve', '=', 'dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j) { $j->on('c.id_dat_ve', '=', 'dat_ve.id'); })
            ->leftJoin('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->when($period === 'today', function($q) {
                return $q->whereDate('dat_ve.created_at', Carbon::today());
            })
            ->when($period === 'week', function($q) {
                return $q->whereBetween('dat_ve.created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            })
            ->when($period === 'month', function($q) {
                return $q->whereMonth('dat_ve.created_at', Carbon::now()->month)
                      ->whereYear('dat_ve.created_at', Carbon::now()->year);
            })
            ->when($period === 'year', function($q) {
                return $q->whereYear('dat_ve.created_at', Carbon::now()->year);
            })
            ->select(
                'phim.id',
                'phim.ten_phim',
                'phim.poster',
                'phim.the_loai',
                'phim.quoc_gia',
                'phim.ngay_khoi_chieu',
                DB::raw('COUNT(DISTINCT dat_ve.id) as total_bookings'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets_sold'),
                DB::raw('SUM(COALESCE(s.seat_total, 0) + COALESCE(c.combo_total, 0)) as total_revenue'),
                DB::raw('AVG(COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)) as avg_ticket_price'),
                DB::raw('COUNT(DISTINCT dat_ve.id_nguoi_dung) as unique_customers'),
                DB::raw('COUNT(DISTINCT suat_chieu.id) as total_showtimes')
            )
        ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster', 'phim.the_loai', 'phim.quoc_gia', 'phim.ngay_khoi_chieu')
        ->orderBy('total_tickets_sold', 'desc')
        ->limit($limit)
        ->get();

        // Tính toán phần trăm so với tổng
        $totalTickets = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->when($period !== 'all', function($q) use ($period) {
                switch ($period) {
                    case 'today':
                        return $q->whereDate('dat_ve.created_at', Carbon::today());
                    case 'week':
                        return $q->whereBetween('dat_ve.created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                    case 'month':
                        return $q->whereMonth('dat_ve.created_at', Carbon::now()->month)
                              ->whereYear('dat_ve.created_at', Carbon::now()->year);
                    case 'year':
                        return $q->whereYear('dat_ve.created_at', Carbon::now()->year);
                }
            })
            ->count();

        $hotMovies->transform(function($movie) use ($totalTickets) {
            $movie->market_share = $totalTickets > 0 ? round(($movie->total_tickets_sold / $totalTickets) * 100, 2) : 0;
            return $movie;
        });

        return response()->json([
            'hot_movies' => $hotMovies,
            'total_tickets_period' => $totalTickets,
            'period' => $period,
            'generated_at' => Carbon::now()->toISOString()
        ]);
    }

    /**
     * Báo cáo thống kê khung giờ đặt vé nhiều nhất
     */
    public function peakBookingHoursReport(Request $request)
    {
        $period = $request->get('period', 'month');
        $groupBy = $request->get('group_by', 'hour'); // hour, day_hour

        $query = DatVe::join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
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
            case 'all':
                break;
        }

        if ($groupBy === 'day_hour') {
            // Phân tích theo ngày trong tuần và giờ
            $peakHours = $query->select(
                DB::raw('DAYOFWEEK(dat_ve.created_at) as day_of_week'),
                DB::raw('HOUR(dat_ve.created_at) as booking_hour'),
                DB::raw('COUNT(DISTINCT dat_ve.id) as total_bookings'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
                DB::raw('SUM(COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)) as total_revenue'),
                DB::raw('AVG(chi_tiet_dat_ve.gia_ve) as avg_ticket_price')
            )
            ->groupBy('day_of_week', 'booking_hour')
            ->orderBy('total_bookings', 'desc')
            ->get()
            ->map(function($item) {
                $daysMap = [
                    1 => 'Chủ Nhật',
                    2 => 'Thứ Hai',
                    3 => 'Thứ Ba',
                    4 => 'Thứ Tư',
                    5 => 'Thứ Năm',
                    6 => 'Thứ Sáu',
                    7 => 'Thứ Bảy'
                ];
                $item->day_name = $daysMap[$item->day_of_week] ?? 'N/A';
                $item->time_slot = sprintf('%02d:00 - %02d:59', $item->booking_hour, $item->booking_hour);
                return $item;
            });
        } else {
            // Phân tích chỉ theo giờ trong ngày
            $peakHours = $query->select(
                DB::raw('HOUR(dat_ve.created_at) as booking_hour'),
                DB::raw('COUNT(DISTINCT dat_ve.id) as total_bookings'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
                DB::raw('SUM(COALESCE(chi_tiet_dat_ve.gia_ve, chi_tiet_dat_ve.gia)) as total_revenue'),
                DB::raw('AVG(chi_tiet_dat_ve.gia_ve) as avg_ticket_price')
            )
            ->groupBy('booking_hour')
            ->orderBy('total_bookings', 'desc')
            ->get()
            ->map(function($item) {
                $item->time_slot = sprintf('%02d:00 - %02d:59', $item->booking_hour, $item->booking_hour);
                
                // Phân loại khung giờ
                if ($item->booking_hour >= 6 && $item->booking_hour < 12) {
                    $item->time_period = 'Sáng';
                } elseif ($item->booking_hour >= 12 && $item->booking_hour < 18) {
                    $item->time_period = 'Chiều';
                } elseif ($item->booking_hour >= 18 && $item->booking_hour < 22) {
                    $item->time_period = 'Tối';
                } else {
                    $item->time_period = 'Đêm/Khuya';
                }
                
                return $item;
            });
        }

        // Tìm khung giờ cao điểm nhất
        $peakHour = $peakHours->first();

        // Thống kê theo khung giờ rộng (sáng, chiều, tối, đêm)
        $periodStats = $peakHours->groupBy('time_period')->map(function($group, $period) {
            return [
                'period' => $period,
                'total_bookings' => $group->sum('total_bookings'),
                'total_tickets' => $group->sum('total_tickets'),
                'total_revenue' => $group->sum('total_revenue'),
                'avg_ticket_price' => $group->avg('avg_ticket_price')
            ];
        })->sortByDesc('total_bookings')->values();

        return response()->json([
            'peak_hours' => $peakHours,
            'peak_hour_details' => $peakHour,
            'period_statistics' => $periodStats,
            'period' => $period,
            'group_by' => $groupBy,
            'generated_at' => Carbon::now()->toISOString()
        ]);
    }

    /**
     * Thống kê chi tiết theo phim
     */
    public function movieStatistics(Request $request, Phim $movie)
    {
        $period = $request->get('period', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $service = new MovieStatisticsService();
        $statistics = $service->getMovieStatistics(
            $movie->id,
            $period,
            $startDate,
            $endDate
        );

        if ($request->expectsJson()) {
            return response()->json($statistics);
        }

        return view('admin.reports.movie-statistics', compact('statistics', 'movie'));
    }

    /**
     * Thống kê tổng hợp tất cả phim (Dashboard)
     */
    public function moviesStatisticsDashboard(Request $request)
    {
        $period = $request->get('period', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $sortBy = $request->get('sort_by', 'tickets');
        $sortOrder = $request->get('sort_order', 'desc');
        $limit = $request->get('limit');

        $service = new MovieStatisticsService();
        $data = $service->getAllMoviesStatistics(
            $period,
            $startDate,
            $endDate,
            $sortBy,
            $sortOrder,
            $limit
        );

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('admin.reports.movies-dashboard', compact('data'));
    }
}
