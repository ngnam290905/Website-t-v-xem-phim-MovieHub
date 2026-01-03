<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Phim;
use App\Models\DatVe;
use App\Models\ChiTietDatVe;
use App\Models\NguoiDung;
use App\Models\SuatChieu;
use App\Models\ThanhToan;
use App\Models\Ghe;
use App\Models\LoaiGhe;
use App\Models\Combo;
use App\Models\Food;
use App\Models\ChiTietDatVeCombo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Dashboard tổng quan
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Xác định khoảng thời gian
        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        // Tổng doanh thu (vé + combo + đồ ăn)
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');
        
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $query = DB::table('dat_ve')
            ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','dat_ve.id'); })
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']]);

        $totalRevenue = $query->select(DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total'))
            ->value('total') ?? 0;

        // Số vé bán
        $totalTickets = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        // Tỷ lệ lấp đầy ghế
        $totalSeats = Ghe::where('trang_thai', 1)->count();
        $occupiedSeats = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
            ->distinct('chi_tiet_dat_ve.id_ghe')
            ->count('chi_tiet_dat_ve.id_ghe');
        
        $occupancyRate = $totalSeats > 0 ? round(($occupiedSeats / $totalSeats) * 100, 2) : 0;

        // Doanh thu đồ ăn
        $foodRevenue = DB::table('chi_tiet_dat_ve_combo')
            ->join('dat_ve', 'chi_tiet_dat_ve_combo.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
            ->sum(DB::raw('chi_tiet_dat_ve_combo.gia_ap_dung * COALESCE(chi_tiet_dat_ve_combo.so_luong,1)'));

        // Doanh thu theo tháng (12 tháng gần nhất)
        $revenueByMonth = $this->getRevenueByMonth();

        return view('admin.reports.dashboard', compact(
            'totalRevenue',
            'totalTickets',
            'occupancyRate',
            'foodRevenue',
            'revenueByMonth',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Báo cáo doanh thu
     */
    public function revenue(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $groupBy = $request->get('group_by', 'day'); // day, month

        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');
        
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $query = DB::table('dat_ve')
            ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','dat_ve.id'); })
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']]);

        if ($groupBy === 'month') {
            $revenueData = $query->select(
                DB::raw('YEAR(dat_ve.created_at) as year'),
                DB::raw('MONTH(dat_ve.created_at) as month'),
                DB::raw('DATE_FORMAT(dat_ve.created_at, "%Y-%m-01") as date'),
                DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_revenue'),
                DB::raw('COUNT(DISTINCT dat_ve.id) as booking_count')
            )
            ->groupBy('year', 'month', DB::raw('DATE_FORMAT(dat_ve.created_at, "%Y-%m-01")'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        } else {
            $revenueData = $query->select(
                DB::raw('DATE(dat_ve.created_at) as date'),
                DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_revenue'),
                DB::raw('COUNT(DISTINCT dat_ve.id) as booking_count')
            )
            ->groupBy(DB::raw('DATE(dat_ve.created_at)'))
            ->orderBy('date')
            ->get();
        }

        $totalRevenue = $revenueData->sum('total_revenue');
        $totalBookings = $revenueData->sum('booking_count');

        if ($request->expectsJson()) {
            return response()->json([
                'revenue_data' => $revenueData,
                'total_revenue' => $totalRevenue,
                'total_bookings' => $totalBookings,
                'period' => $period
            ]);
        }

        return view('admin.reports.revenue', compact(
            'revenueData',
            'totalRevenue',
            'totalBookings',
            'period',
            'startDate',
            'endDate',
            'groupBy'
        ));
    }

    /**
     * Báo cáo phim
     */
    public function movies(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');
        
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $moviesData = DB::table('phim')
            ->join('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->leftJoin('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','dat_ve.id'); })
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                'phim.id',
                'phim.ten_phim',
                'phim.poster',
                DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_revenue'),
                DB::raw('COUNT(DISTINCT chi_tiet_dat_ve.id) as total_tickets'),
                DB::raw('COUNT(DISTINCT dat_ve.id) as total_bookings')
            )
            ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Format poster URLs
        $moviesData = $moviesData->map(function($movie) {
            $phimModel = Phim::find($movie->id);
            $movie->poster_url = $phimModel ? ($phimModel->poster_url ?? $movie->poster) : ($movie->poster ?? asset('images/no-poster.svg'));
            return $movie;
        });

        return view('admin.reports.movies', compact(
            'moviesData',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Báo cáo suất chiếu
     */
    public function showtimes(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        // Tổng số suất chiếu
        $totalShowtimes = SuatChieu::whereBetween('thoi_gian_bat_dau', [$dateRange['start'], $dateRange['end']])
            ->where('trang_thai', 1)
            ->count();

        // Suất chiếu hiệu quả (tỷ lệ lấp đầy > 50%)
        $effectiveShowtimes = DB::table('suat_chieu')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->join('phong_chieu', 'suat_chieu.id_phong', '=', 'phong_chieu.id')
            ->leftJoin('ghe', 'phong_chieu.id', '=', 'ghe.id_phong')
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('suat_chieu.thoi_gian_bat_dau', [$dateRange['start'], $dateRange['end']])
            ->select(
                'suat_chieu.id',
                DB::raw('COUNT(DISTINCT chi_tiet_dat_ve.id) as tickets_sold'),
                DB::raw('COUNT(DISTINCT ghe.id) as total_seats')
            )
            ->groupBy('suat_chieu.id')
            ->havingRaw('(COUNT(DISTINCT chi_tiet_dat_ve.id) * 100.0 / NULLIF(COUNT(DISTINCT ghe.id), 0)) > 50')
            ->count();

        // Suất chiếu kém (tỷ lệ lấp đầy < 20%)
        $poorShowtimes = DB::table('suat_chieu')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->join('phong_chieu', 'suat_chieu.id_phong', '=', 'phong_chieu.id')
            ->leftJoin('ghe', 'phong_chieu.id', '=', 'ghe.id_phong')
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('suat_chieu.thoi_gian_bat_dau', [$dateRange['start'], $dateRange['end']])
            ->select(
                'suat_chieu.id',
                DB::raw('COUNT(DISTINCT chi_tiet_dat_ve.id) as tickets_sold'),
                DB::raw('COUNT(DISTINCT ghe.id) as total_seats')
            )
            ->groupBy('suat_chieu.id')
            ->havingRaw('(COUNT(DISTINCT chi_tiet_dat_ve.id) * 100.0 / NULLIF(COUNT(DISTINCT ghe.id), 0)) < 20')
            ->count();

        // Chi tiết suất chiếu
        $showtimesDetail = DB::table('suat_chieu')
            ->join('phim', 'suat_chieu.id_phim', '=', 'phim.id')
            ->join('phong_chieu', 'suat_chieu.id_phong', '=', 'phong_chieu.id')
            ->leftJoin('dat_ve', function($join) {
                $join->on('suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
                     ->where('dat_ve.trang_thai', '=', 1);
            })
            ->leftJoin('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->leftJoin('ghe', 'phong_chieu.id', '=', 'ghe.id_phong')
            ->whereBetween('suat_chieu.thoi_gian_bat_dau', [$dateRange['start'], $dateRange['end']])
            ->where('suat_chieu.trang_thai', 1)
            ->select(
                'suat_chieu.id',
                'phim.ten_phim',
                'phong_chieu.ten_phong',
                'suat_chieu.thoi_gian_bat_dau',
                DB::raw('COUNT(DISTINCT chi_tiet_dat_ve.id) as tickets_sold'),
                DB::raw('COUNT(DISTINCT ghe.id) as total_seats'),
                DB::raw('ROUND((COUNT(DISTINCT chi_tiet_dat_ve.id) * 100.0 / NULLIF(COUNT(DISTINCT ghe.id), 0)), 2) as occupancy_rate')
            )
            ->groupBy('suat_chieu.id', 'phim.ten_phim', 'phong_chieu.ten_phong', 'suat_chieu.thoi_gian_bat_dau')
            ->orderBy('occupancy_rate', 'desc')
            ->get();

        return view('admin.reports.showtimes', compact(
            'totalShowtimes',
            'effectiveShowtimes',
            'poorShowtimes',
            'showtimesDetail',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Báo cáo ghế
     */
    public function seats(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        // Thống kê theo loại ghế
        $seatTypes = LoaiGhe::with(['ghe'])->get()->map(function($seatType) use ($dateRange) {
            $totalSeats = $seatType->ghe->where('trang_thai', 1)->count();
            $usedSeats = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('ghe', 'chi_tiet_dat_ve.id_ghe', '=', 'ghe.id')
                ->where('ghe.id_loai', $seatType->id)
                ->where('dat_ve.trang_thai', 1)
                ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
                ->distinct('chi_tiet_dat_ve.id_ghe')
                ->count('chi_tiet_dat_ve.id_ghe');

            $usageRate = $totalSeats > 0 ? round(($usedSeats / $totalSeats) * 100, 2) : 0;

            return [
                'id' => $seatType->id,
                'ten_loai' => $seatType->ten_loai,
                'total_seats' => $totalSeats,
                'used_seats' => $usedSeats,
                'usage_rate' => $usageRate
            ];
        });

        // Tổng số ghế
        $totalSeats = Ghe::where('trang_thai', 1)->count();
        $totalUsedSeats = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
            ->distinct('chi_tiet_dat_ve.id_ghe')
            ->count('chi_tiet_dat_ve.id_ghe');

        $overallUsageRate = $totalSeats > 0 ? round(($totalUsedSeats / $totalSeats) * 100, 2) : 0;

        return view('admin.reports.seats', compact(
            'seatTypes',
            'totalSeats',
            'totalUsedSeats',
            'overallUsageRate',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Báo cáo đồ ăn
     */
    public function foods(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        // Combo bán chạy
        $topCombos = DB::table('chi_tiet_dat_ve_combo')
            ->join('dat_ve', 'chi_tiet_dat_ve_combo.id_dat_ve', '=', 'dat_ve.id')
            ->join('combo', 'chi_tiet_dat_ve_combo.id_combo', '=', 'combo.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                'combo.id',
                'combo.ten_combo',
                'combo.hinh_anh',
                DB::raw('SUM(chi_tiet_dat_ve_combo.so_luong) as total_quantity'),
                DB::raw('SUM(chi_tiet_dat_ve_combo.gia_ap_dung * chi_tiet_dat_ve_combo.so_luong) as total_revenue')
            )
            ->groupBy('combo.id', 'combo.ten_combo', 'combo.hinh_anh')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Doanh thu đồ ăn
        $foodRevenue = DB::table('chi_tiet_dat_ve_combo')
            ->join('dat_ve', 'chi_tiet_dat_ve_combo.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
            ->sum(DB::raw('chi_tiet_dat_ve_combo.gia_ap_dung * COALESCE(chi_tiet_dat_ve_combo.so_luong,1)'));

        // Tổng số combo đã bán
        $totalCombosSold = DB::table('chi_tiet_dat_ve_combo')
            ->join('dat_ve', 'chi_tiet_dat_ve_combo.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('chi_tiet_dat_ve_combo.so_luong');

        return view('admin.reports.foods', compact(
            'topCombos',
            'foodRevenue',
            'totalCombosSold',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Báo cáo thanh toán
     */
    public function payments(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        // Thống kê theo phương thức thanh toán
        $paymentMethods = DB::table('thanh_toan')
            ->join('dat_ve', 'thanh_toan.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->where('thanh_toan.trang_thai', 1)
            ->whereBetween('thanh_toan.thoi_gian', [$dateRange['start'], $dateRange['end']])
            ->select(
                'thanh_toan.phuong_thuc',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(thanh_toan.so_tien) as total_amount')
            )
            ->groupBy('thanh_toan.phuong_thuc')
            ->get()
            ->map(function($item) {
                $methodNames = [
                    'VNPay' => 'VNPay',
                    'MoMo' => 'MoMo',
                    'Chuyển khoản' => 'Chuyển khoản',
                    'Tiền mặt' => 'Tiền mặt',
                    'QR Code' => 'QR Code'
                ];
                $item->method_name = $methodNames[$item->phuong_thuc] ?? $item->phuong_thuc;
                return $item;
            });

        // Tổng số giao dịch
        $totalTransactions = ThanhToan::join('dat_ve', 'thanh_toan.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->where('thanh_toan.trang_thai', 1)
            ->whereBetween('thanh_toan.thoi_gian', [$dateRange['start'], $dateRange['end']])
            ->count();

        // Tổng doanh thu từ thanh toán
        $totalPaymentRevenue = ThanhToan::join('dat_ve', 'thanh_toan.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->where('thanh_toan.trang_thai', 1)
            ->whereBetween('thanh_toan.thoi_gian', [$dateRange['start'], $dateRange['end']])
            ->sum('thanh_toan.so_tien');

        // Chi tiết giao dịch
        $transactions = ThanhToan::with(['datVe.nguoiDung', 'datVe.suatChieu.phim'])
            ->join('dat_ve', 'thanh_toan.id_dat_ve', '=', 'dat_ve.id')
            ->where('dat_ve.trang_thai', 1)
            ->where('thanh_toan.trang_thai', 1)
            ->whereBetween('thanh_toan.thoi_gian', [$dateRange['start'], $dateRange['end']])
            ->select('thanh_toan.*')
            ->orderBy('thanh_toan.thoi_gian', 'desc')
            ->limit(100)
            ->get();

        return view('admin.reports.payments', compact(
            'paymentMethods',
            'totalTransactions',
            'totalPaymentRevenue',
            'transactions',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Xuất Excel
     */
    public function exportExcel(Request $request)
    {
        $type = $request->get('type', 'revenue');
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // TODO: Implement Excel export using PhpSpreadsheet or Laravel Excel
        return response()->json(['message' => 'Chức năng xuất Excel đang được phát triển']);
    }

    /**
     * Xuất PDF
     */
    public function exportPdf(Request $request)
    {
        $type = $request->get('type', 'revenue');
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // TODO: Implement PDF export using DomPDF or TCPDF
        return response()->json(['message' => 'Chức năng xuất PDF đang được phát triển']);
    }

    /**
     * Helper: Lấy khoảng thời gian
     */
    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay()
            ];
        }

        switch ($period) {
            case 'today':
                return [
                    'start' => Carbon::today()->startOfDay(),
                    'end' => Carbon::today()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => Carbon::now()->startOfWeek(),
                    'end' => Carbon::now()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth()
                ];
            case 'year':
                return [
                    'start' => Carbon::now()->startOfYear(),
                    'end' => Carbon::now()->endOfYear()
                ];
            default:
                return [
                    'start' => Carbon::now()->subYear()->startOfDay(),
                    'end' => Carbon::now()->endOfDay()
                ];
        }
    }

    /**
     * Lấy doanh thu theo tháng (12 tháng gần nhất)
     */
    private function getRevenueByMonth()
    {
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');
        
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        // Lấy dữ liệu 12 tháng gần nhất
        $revenueData = DB::table('dat_ve')
            ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','dat_ve.id'); })
            ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','dat_ve.id'); })
            ->where('dat_ve.trang_thai', 1)
            ->where('dat_ve.created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('YEAR(dat_ve.created_at) as year'),
                DB::raw('MONTH(dat_ve.created_at) as month'),
                DB::raw('DATE_FORMAT(dat_ve.created_at, "%Y-%m-01") as date'),
                DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_revenue')
            )
            ->groupBy('year', 'month', DB::raw('DATE_FORMAT(dat_ve.created_at, "%Y-%m-01")'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Tạo mảng đầy đủ 12 tháng
        $allMonths = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('m/Y');
            
            $monthData = $revenueData->first(function ($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });
            
            $allMonths[] = [
                'month' => $monthLabel,
                'date' => $monthKey,
                'revenue' => $monthData ? (float)$monthData->total_revenue : 0
            ];
        }

        return $allMonths;
    }
}
