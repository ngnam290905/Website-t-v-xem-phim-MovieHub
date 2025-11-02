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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function advancedDashboard()
    {
        // Financial Overview
        $todayRevenue = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereDate('dat_ve.created_at', Carbon::today())
            ->sum('chi_tiet_dat_ve.gia_ve');

        $monthRevenue = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereMonth('dat_ve.created_at', Carbon::now()->month)
            ->whereYear('dat_ve.created_at', Carbon::now()->year)
            ->sum('chi_tiet_dat_ve.gia_ve');

        $yearRevenue = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereYear('dat_ve.created_at', Carbon::now()->year)
            ->sum('chi_tiet_dat_ve.gia_ve');

        // Customer Analytics
        $totalCustomers = NguoiDung::where('trang_thai', 1)->count();
        $newCustomersThisMonth = NguoiDung::where('trang_thai', 1)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $vipCustomers = NguoiDung::join('dat_ve', 'nguoi_dung.id', '=', 'dat_ve.id_nguoi_dung')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->select('nguoi_dung.id', 'nguoi_dung.ho_ten', 'nguoi_dung.email')
            ->selectRaw('SUM(chi_tiet_dat_ve.gia_ve) as total_spent')
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

        // Revenue by movie
        $revenueByMovie = $query->select(
            'phim.id',
            'phim.ten_phim',
            'phim.poster',
            DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue'),
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
            DB::raw('AVG(chi_tiet_dat_ve.gia_ve) as avg_ticket_price')
        )
        ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster')
        ->orderBy('total_revenue', 'desc')
        ->get();

        // Revenue by day
        $revenueByDay = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
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
                DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as daily_revenue'),
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

        // Top customers by spending
        $topCustomers = $query->select(
            'nguoi_dung.id',
            'nguoi_dung.ho_ten',
            'nguoi_dung.email',
            'nguoi_dung.sdt',
            'nguoi_dung.created_at as registration_date',
            DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_spent'),
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
            DB::raw('AVG(chi_tiet_dat_ve.gia_ve) as avg_ticket_price'),
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
                    ->sum('chi_tiet_dat_ve.gia_ve')
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
                DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
                DB::raw('AVG(chi_tiet_dat_ve.gia_ve) as avg_ticket_price'),
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
                DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as revenue')
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
}
