<?php

namespace App\Services;

use App\Models\Phim;
use App\Models\SuatChieu;
use App\Models\DatVe;
use App\Models\ChiTietDatVe;
use App\Models\ChiTietCombo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MovieStatisticsService
{
    /**
     * Lấy thống kê chi tiết cho một phim
     * 
     * @param int $movieId
     * @param string|null $period 'day', 'week', 'month', 'year', 'all' hoặc custom range
     * @param string|null $startDate Format: Y-m-d
     * @param string|null $endDate Format: Y-m-d
     * @return array
     */
    public function getMovieStatistics(int $movieId, ?string $period = 'all', ?string $startDate = null, ?string $endDate = null): array
    {
        $movie = Phim::findOrFail($movieId);
        
        // Xác định khoảng thời gian
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        // 1. Số lượng suất chiếu
        $showtimesQuery = SuatChieu::where('id_phim', $movieId);
        if ($dateRange['start']) {
            $showtimesQuery->where('thoi_gian_bat_dau', '>=', $dateRange['start']);
        }
        if ($dateRange['end']) {
            $showtimesQuery->where('thoi_gian_bat_dau', '<=', $dateRange['end']);
        }
        $totalShowtimes = $showtimesQuery->count();
        
        // 2. Tổng số vé đã bán (chỉ tính vé đã thanh toán, không hủy)
        $ticketsQuery = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->where('suat_chieu.id_phim', $movieId)
            ->where('dat_ve.trang_thai', 1); // Chỉ tính vé đã thanh toán
        
        if ($dateRange['start']) {
            $ticketsQuery->where('dat_ve.created_at', '>=', $dateRange['start']);
        }
        if ($dateRange['end']) {
            $ticketsQuery->where('dat_ve.created_at', '<=', $dateRange['end']);
        }
        
        $totalTicketsSold = $ticketsQuery->count();
        
        // 3. Doanh thu từ vé
        $seatRevenueQuery = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->where('suat_chieu.id_phim', $movieId)
            ->where('dat_ve.trang_thai', 1);
        
        if ($dateRange['start']) {
            $seatRevenueQuery->where('dat_ve.created_at', '>=', $dateRange['start']);
        }
        if ($dateRange['end']) {
            $seatRevenueQuery->where('dat_ve.created_at', '<=', $dateRange['end']);
        }
        
        $seatRevenue = (float) $seatRevenueQuery->sum('chi_tiet_dat_ve.gia');
        
        // 4. Doanh thu từ combo (bắp nước)
        $comboRevenueQuery = DB::table('chi_tiet_dat_ve_combo')
            ->join('dat_ve', 'chi_tiet_dat_ve_combo.id_dat_ve', '=', 'dat_ve.id')
            ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->where('suat_chieu.id_phim', $movieId)
            ->where('dat_ve.trang_thai', 1);
        
        if ($dateRange['start']) {
            $comboRevenueQuery->where('dat_ve.created_at', '>=', $dateRange['start']);
        }
        if ($dateRange['end']) {
            $comboRevenueQuery->where('dat_ve.created_at', '<=', $dateRange['end']);
        }
        
        $comboRevenue = (float) $comboRevenueQuery->sum(DB::raw('chi_tiet_dat_ve_combo.gia_ap_dung * COALESCE(chi_tiet_dat_ve_combo.so_luong, 1)'));
        
        // Tổng doanh thu
        $totalRevenue = $seatRevenue + $comboRevenue;
        
        // 5. Số lượng đơn đặt vé thành công
        $bookingsQuery = DatVe::join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->where('suat_chieu.id_phim', $movieId)
            ->where('dat_ve.trang_thai', 1);
        
        if ($dateRange['start']) {
            $bookingsQuery->where('dat_ve.created_at', '>=', $dateRange['start']);
        }
        if ($dateRange['end']) {
            $bookingsQuery->where('dat_ve.created_at', '<=', $dateRange['end']);
        }
        
        $totalBookings = $bookingsQuery->count();
        
        // 6. Tỷ lệ lấp đầy (occupancy rate)
        $occupancyRate = $totalShowtimes > 0 
            ? round(($totalTicketsSold / ($totalShowtimes * 100)) * 100, 2) // Giả định mỗi suất chiếu có 100 ghế
            : 0;
        
        // 7. Doanh thu trung bình mỗi vé
        $avgRevenuePerTicket = $totalTicketsSold > 0 
            ? round($totalRevenue / $totalTicketsSold, 2)
            : 0;
        
        // 8. Dữ liệu biểu đồ vé bán theo thời gian (nếu có khoảng thời gian)
        $ticketsByDate = [];
        if ($dateRange['start'] && $dateRange['end']) {
            $ticketsByDate = DB::table('chi_tiet_dat_ve')
                ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
                ->where('suat_chieu.id_phim', $movieId)
                ->where('dat_ve.trang_thai', 1)
                ->whereBetween('dat_ve.created_at', [$dateRange['start'], $dateRange['end']])
                ->select(
                    DB::raw('DATE(dat_ve.created_at) as date'),
                    DB::raw('COUNT(chi_tiet_dat_ve.id) as tickets_count')
                )
                ->groupBy(DB::raw('DATE(dat_ve.created_at)'))
                ->orderBy('date')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->date => $item->tickets_count];
                })
                ->toArray();
        }
        
        return [
            'movie' => [
                'id' => $movie->id,
                'ten_phim' => $movie->ten_phim,
                'poster' => $movie->poster_url ?? $movie->poster,
            ],
            'period' => $period,
            'date_range' => [
                'start' => $dateRange['start']?->format('Y-m-d'),
                'end' => $dateRange['end']?->format('Y-m-d'),
            ],
            'statistics' => [
                'total_showtimes' => $totalShowtimes,
                'total_tickets_sold' => $totalTicketsSold,
                'total_bookings' => $totalBookings,
                'seat_revenue' => $seatRevenue,
                'combo_revenue' => $comboRevenue,
                'total_revenue' => $totalRevenue,
                'occupancy_rate' => $occupancyRate,
                'avg_revenue_per_ticket' => $avgRevenuePerTicket,
            ],
            'chart_data' => [
                'tickets_by_date' => $ticketsByDate,
            ],
        ];
    }
    
    /**
     * Lấy thống kê tổng hợp cho tất cả phim
     * 
     * @param string|null $period
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $sortBy 'tickets', 'revenue', 'showtimes'
     * @param string|null $sortOrder 'asc', 'desc'
     * @param int|null $limit
     * @return array
     */
    public function getAllMoviesStatistics(
        ?string $period = 'all',
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $sortBy = 'revenue',
        ?string $sortOrder = 'desc',
        ?int $limit = null
    ): array {
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        // Subquery cho doanh thu vé
        $seatRevenueSub = ChiTietDatVe::select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');
        
        // Subquery cho doanh thu combo
        $comboRevenueSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong, 1)) as combo_total'))
            ->groupBy('id_dat_ve');
        
        // Query chính - sử dụng DB query builder để tránh vấn đề với leftJoin
        $query = DB::table('phim')
            ->leftJoin('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
            ->leftJoin('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->leftJoinSub($seatRevenueSub, 'seat_rev', function($join) {
                $join->on('seat_rev.id_dat_ve', '=', 'dat_ve.id');
            })
            ->leftJoinSub($comboRevenueSub, 'combo_rev', function($join) {
                $join->on('combo_rev.id_dat_ve', '=', 'dat_ve.id');
            })
            ->where(function($q) {
                $q->whereNull('dat_ve.id')
                  ->orWhere('dat_ve.trang_thai', '=', 1); // Chỉ tính vé đã thanh toán
            });
        
        // Áp dụng filter thời gian
        if ($dateRange['start']) {
            $query->where(function($q) use ($dateRange) {
                $q->where('suat_chieu.thoi_gian_bat_dau', '>=', $dateRange['start'])
                  ->orWhere('dat_ve.created_at', '>=', $dateRange['start']);
            });
        }
        if ($dateRange['end']) {
            $query->where(function($q) use ($dateRange) {
                $q->where('suat_chieu.thoi_gian_bat_dau', '<=', $dateRange['end'])
                  ->orWhere('dat_ve.created_at', '<=', $dateRange['end']);
            });
        }
        
        // Join với chi_tiet_dat_ve để đếm vé
        $query->leftJoin('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve');
        
        $movies = $query->select(
            'phim.id',
            'phim.ten_phim',
            'phim.poster',
            'phim.trang_thai',
            DB::raw('COUNT(DISTINCT suat_chieu.id) as total_showtimes'),
            DB::raw('COUNT(DISTINCT dat_ve.id) as total_bookings'),
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets_sold'),
            DB::raw('COALESCE(SUM(seat_rev.seat_total), 0) + COALESCE(SUM(combo_rev.combo_total), 0) as total_revenue')
        )
        ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster', 'phim.trang_thai');
        
        // Sắp xếp
        switch ($sortBy) {
            case 'tickets':
                $query->orderBy('total_tickets_sold', $sortOrder);
                break;
            case 'showtimes':
                $query->orderBy('total_showtimes', $sortOrder);
                break;
            case 'revenue':
            default:
                $query->orderBy('total_revenue', $sortOrder);
                break;
        }
        
        if ($limit) {
            $query->limit($limit);
        }
        
        $results = $query->get();
        
        // Format dữ liệu và lấy poster URL từ model
        $moviesData = $results->map(function($movie) {
            $phimModel = Phim::find($movie->id);
            return [
                'id' => $movie->id,
                'ten_phim' => $movie->ten_phim,
                'poster' => $phimModel ? ($phimModel->poster_url ?? $movie->poster) : $movie->poster,
                'trang_thai' => $movie->trang_thai,
                'total_showtimes' => (int) $movie->total_showtimes,
                'total_bookings' => (int) $movie->total_bookings,
                'total_tickets_sold' => (int) $movie->total_tickets_sold,
                'total_revenue' => (float) $movie->total_revenue,
            ];
        });
        
        return [
            'period' => $period,
            'date_range' => [
                'start' => $dateRange['start']?->format('Y-m-d'),
                'end' => $dateRange['end']?->format('Y-m-d'),
            ],
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'movies' => $moviesData,
            'summary' => [
                'total_movies' => $moviesData->count(),
                'total_showtimes' => $moviesData->sum('total_showtimes'),
                'total_tickets_sold' => $moviesData->sum('total_tickets_sold'),
                'total_revenue' => $moviesData->sum('total_revenue'),
            ],
        ];
    }
    
    /**
     * Xác định khoảng thời gian dựa trên period hoặc custom range
     * 
     * @param string|null $period
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function getDateRange(?string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        // Nếu có custom range, ưu tiên sử dụng
        if ($startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay(),
            ];
        }
        
        $now = Carbon::now();
        
        switch ($period) {
            case 'day':
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                ];
            case 'all':
            default:
                return [
                    'start' => null,
                    'end' => null,
                ];
        }
    }
}

