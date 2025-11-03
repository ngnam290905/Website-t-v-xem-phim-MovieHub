<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Phim;
use App\Models\DatVe;
use App\Models\ChiTietDatVe;
use App\Models\NguoiDung;
use App\Models\SuatChieu;
use App\Models\HangThanhVien;
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
            DB::raw('DATE(dat_ve.created_at) as date'),
            DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue'),
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets')
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
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
            DB::raw('COUNT(DISTINCT suat_chieu.id) as total_showtimes')
        )
        ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster')
        ->orderBy('total_tickets', 'desc')
        ->orderBy('total_revenue', 'desc')
        ->orderBy('total_showtimes', 'desc')
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
            'phim.poster',
            'suat_chieu.thoi_gian',
            'suat_chieu.ngay_chieu',
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
            DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue')
        )
        ->groupBy('suat_chieu.id', 'phim.ten_phim', 'phim.poster', 'suat_chieu.thoi_gian', 'suat_chieu.ngay_chieu')
        ->orderBy('total_tickets', 'desc')
        ->orderBy('total_revenue', 'desc')
        ->limit(intval($limit))
        ->get();

        return response()->json([
            'top_showtimes' => $topShowtimes,
            'period' => $period
        ]);
    }

    public function memberRevenue(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->join('nguoi_dung', 'dat_ve.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->leftJoin('hang_thanh_vien', 'nguoi_dung.id', '=', 'hang_thanh_vien.id_nguoi_dung')
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

        // Thống kê theo hạng thành viên
        $revenueByTier = $query->select(
            DB::raw('COALESCE(hang_thanh_vien.ten_hang, "Chưa có hạng") as member_tier'),
            DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue'),
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
            DB::raw('COUNT(DISTINCT dat_ve.id_nguoi_dung) as total_members')
        )
        ->groupBy('member_tier')
        ->orderBy('total_revenue', 'desc')
        ->get();

        // Thống kê tổng doanh số theo thành viên
        $totalMemberRevenue = $query->select(
            DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue'),
            DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
            DB::raw('COUNT(DISTINCT dat_ve.id_nguoi_dung) as total_members')
        )
        ->first();

        // Top thành viên chi tiêu nhiều nhất
        $topMemberSpenders = NguoiDung::join('dat_ve', 'nguoi_dung.id', '=', 'dat_ve.id_nguoi_dung')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->leftJoin('hang_thanh_vien', 'nguoi_dung.id', '=', 'hang_thanh_vien.id_nguoi_dung')
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
                'nguoi_dung.id',
                'nguoi_dung.ho_ten',
                'nguoi_dung.email',
                DB::raw('COALESCE(hang_thanh_vien.ten_hang, "Chưa có hạng") as member_tier'),
                DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_spent'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets')
            )
            ->groupBy('nguoi_dung.id', 'nguoi_dung.ho_ten', 'nguoi_dung.email', 'member_tier')
            ->orderBy('total_spent', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'revenue_by_tier' => $revenueByTier,
            'total_member_revenue' => $totalMemberRevenue,
            'top_member_spenders' => $topMemberSpenders,
            'period' => $period
        ]);
    }

    public function popularMoviesAndShowtimes(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $limit = $request->get('limit', 10);

        $baseQuery = function($query) use ($startDate, $endDate, $period) {
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
        };

        // Top phim được sử dụng nhiều nhất
        $topMovies = Phim::join('suat_chieu', 'phim.id', '=', 'suat_chieu.id_phim')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->where(function($q) use ($baseQuery) {
                $baseQuery($q);
            })
            ->select(
                'phim.id',
                'phim.ten_phim',
                'phim.poster',
                DB::raw('COUNT(DISTINCT suat_chieu.id) as total_showtimes'),
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
                DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue'),
                DB::raw('COUNT(DISTINCT dat_ve.id_nguoi_dung) as unique_customers')
            )
            ->groupBy('phim.id', 'phim.ten_phim', 'phim.poster')
            ->orderBy('total_tickets', 'desc')
            ->orderBy('total_revenue', 'desc')
            ->orderBy('total_showtimes', 'desc')
            ->limit(intval($limit))
            ->get();

        // Top suất chiếu được sử dụng nhiều nhất
        $topShowtimes = SuatChieu::join('phim', 'suat_chieu.id_phim', '=', 'phim.id')
            ->join('dat_ve', 'suat_chieu.id', '=', 'dat_ve.id_suat_chieu')
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->where(function($q) use ($baseQuery) {
                $baseQuery($q);
            })
            ->select(
                'suat_chieu.id',
                'phim.ten_phim',
                'phim.poster',
                'suat_chieu.ngay_chieu',
                'suat_chieu.thoi_gian',
                DB::raw('COUNT(chi_tiet_dat_ve.id) as total_tickets'),
                DB::raw('SUM(chi_tiet_dat_ve.gia_ve) as total_revenue'),
                DB::raw('COUNT(DISTINCT dat_ve.id_nguoi_dung) as unique_customers')
            )
            ->groupBy('suat_chieu.id', 'phim.ten_phim', 'phim.poster', 'suat_chieu.ngay_chieu', 'suat_chieu.thoi_gian')
            ->orderBy('total_tickets', 'desc')
            ->orderBy('total_revenue', 'desc')
            ->limit(intval($limit))
            ->get();

        return response()->json([
            'top_movies' => $topMovies,
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

    public function moviesAndShowtimesData(Request $request)
    {
        $period = $request->get('period', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('status');
        $phim = $request->get('phim');

        // Movies data
        $moviesQuery = Phim::with(['suatChieu' => function($query) {
            $query->orderBy('thoi_gian_bat_dau', 'desc');
        }]);

        if ($status) {
            $moviesQuery->where('trang_thai', $status);
        }

        if ($phim) {
            $moviesQuery->where('ten_phim', 'like', '%' . $phim . '%');
        }

        $movies = $moviesQuery->get()->map(function($movie) use ($startDate, $endDate, $period) {
            // Count showtimes
            $showtimesQuery = $movie->suatChieu();
            
            if ($startDate && $endDate) {
                $showtimesQuery->whereBetween('thoi_gian_bat_dau', [$startDate, $endDate]);
            } elseif ($period !== 'all') {
                switch ($period) {
                    case 'today':
                        $showtimesQuery->whereDate('thoi_gian_bat_dau', Carbon::today());
                        break;
                    case 'week':
                        $showtimesQuery->whereBetween('thoi_gian_bat_dau', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'month':
                        $showtimesQuery->whereMonth('thoi_gian_bat_dau', Carbon::now()->month)
                              ->whereYear('thoi_gian_bat_dau', Carbon::now()->year);
                        break;
                    case 'year':
                        $showtimesQuery->whereYear('thoi_gian_bat_dau', Carbon::now()->year);
                        break;
                }
            }

            $showtimesCount = $showtimesQuery->count();
            
            // Calculate revenue from bookings
            $revenue = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
                ->where('suat_chieu.id_phim', $movie->id)
                ->where('dat_ve.trang_thai', 1)
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    return $q->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
                })
                ->sum('chi_tiet_dat_ve.gia_ve');

            $ticketsSold = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
                ->where('suat_chieu.id_phim', $movie->id)
                ->where('dat_ve.trang_thai', 1)
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    return $q->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
                })
                ->count();

            return [
                'id' => $movie->id,
                'ten_phim' => $movie->ten_phim,
                'ten_goc' => $movie->ten_goc ?? '',
                'poster' => $movie->poster,
                'trang_thai' => $movie->trang_thai,
                'do_dai' => $movie->do_dai,
                'dao_dien' => $movie->dao_dien,
                'dien_vien' => $movie->dien_vien,
                'the_loai' => $movie->the_loai ?? '',
                'quoc_gia' => $movie->quoc_gia ?? '',
                'ngay_khoi_chieu' => $movie->ngay_khoi_chieu,
                'ngay_ket_thuc' => $movie->ngay_ket_thuc,
                'diem_danh_gia' => $movie->diem_danh_gia ?? 0,
                'so_luot_danh_gia' => $movie->so_luot_danh_gia ?? 0,
                'so_suat_chieu' => $showtimesCount,
                'tong_doanh_thu' => $revenue,
                'so_ve_ban' => $ticketsSold,
                'created_at' => $movie->created_at
            ];
        });

        // Showtimes data
        $showtimesQuery = SuatChieu::with(['phim', 'phongChieu']);

        if ($startDate && $endDate) {
            $showtimesQuery->whereBetween('thoi_gian_bat_dau', [$startDate, $endDate]);
        } elseif ($period !== 'all') {
            switch ($period) {
                case 'today':
                    $showtimesQuery->whereDate('thoi_gian_bat_dau', Carbon::today());
                    break;
                case 'week':
                    $showtimesQuery->whereBetween('thoi_gian_bat_dau', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $showtimesQuery->whereMonth('thoi_gian_bat_dau', Carbon::now()->month)
                          ->whereYear('thoi_gian_bat_dau', Carbon::now()->year);
                    break;
                case 'year':
                    $showtimesQuery->whereYear('thoi_gian_bat_dau', Carbon::now()->year);
                    break;
            }
        }

        if ($status) {
            $showtimesQuery->whereHas('phim', function($q) use ($status) {
                $q->where('trang_thai', $status);
            });
        }

        if ($phim) {
            $showtimesQuery->whereHas('phim', function($q) use ($phim) {
                $q->where('ten_phim', 'like', '%' . $phim . '%');
            });
        }

        $showtimes = $showtimesQuery->get()->map(function($showtime) {
            // Calculate tickets sold and revenue
            $ticketsSold = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->where('dat_ve.id_suat_chieu', $showtime->id)
                ->where('dat_ve.trang_thai', 1)
                ->count();

            $revenue = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->where('dat_ve.id_suat_chieu', $showtime->id)
                ->where('dat_ve.trang_thai', 1)
                ->sum('chi_tiet_dat_ve.gia_ve');

            $now = Carbon::now();
            $statusText = '';
            if ($showtime->thoi_gian_bat_dau > $now) {
                $statusText = 'Sắp chiếu';
            } elseif ($showtime->thoi_gian_ket_thuc < $now) {
                $statusText = 'Đã kết thúc';
            } else {
                $statusText = 'Đang chiếu';
            }

            return [
                'id' => $showtime->id,
                'ten_phim' => $showtime->phim->ten_phim ?? '',
                'ten_phong' => $showtime->phongChieu->ten_phong ?? '',
                'thoi_gian_bat_dau' => $showtime->thoi_gian_bat_dau,
                'thoi_gian_ket_thuc' => $showtime->thoi_gian_ket_thuc,
                'trang_thai' => $showtime->trang_thai,
                'status_text' => $statusText,
                'so_ve_ban' => $ticketsSold,
                'tong_doanh_thu' => $revenue,
                'created_at' => $showtime->created_at
            ];
        });

        // Statistics
        $stats = [
            'total_movies' => Phim::count(),
            'movies_by_status' => [
                'dang_chieu' => Phim::where('trang_thai', 'dang_chieu')->count(),
                'sap_chieu' => Phim::where('trang_thai', 'sap_chieu')->count(),
                'ngung_chieu' => Phim::where('trang_thai', 'ngung_chieu')->count(),
            ],
            'total_showtimes' => SuatChieu::count(),
            'showtimes_by_period' => [
                'coming' => SuatChieu::where('thoi_gian_bat_dau', '>', Carbon::now())->count(),
                'ongoing' => SuatChieu::where('thoi_gian_bat_dau', '<=', Carbon::now())
                    ->where('thoi_gian_ket_thuc', '>=', Carbon::now())->count(),
                'finished' => SuatChieu::where('thoi_gian_ket_thuc', '<', Carbon::now())->count(),
            ],
            'total_revenue' => ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->where('dat_ve.trang_thai', 1)
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    return $q->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
                })
                ->sum('chi_tiet_dat_ve.gia_ve'),
            'total_tickets_sold' => ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->where('dat_ve.trang_thai', 1)
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    return $q->whereBetween('dat_ve.created_at', [$startDate, $endDate]);
                })
                ->count(),
        ];

        return response()->json([
            'movies' => $movies,
            'showtimes' => $showtimes,
            'statistics' => $stats,
            'period' => $period,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'phim' => $phim
            ]
        ]);
    }

    public function bookingsData(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('status');
        $phim = $request->get('phim');
        $nguoiDung = $request->get('nguoi_dung');
        $limit = $request->get('limit', 50);

        $query = DatVe::with([
            'nguoiDung',
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe',
            'chiTietCombo.combo',
            'khuyenMai'
        ]);

        // Filter by status
        if ($status !== null && $status !== '') {
            $query->where('trang_thai', $status);
        }

        // Filter by movie
        if ($phim) {
            $query->whereHas('suatChieu.phim', function ($q) use ($phim) {
                $q->where('ten_phim', 'like', '%' . $phim . '%');
            });
        }

        // Filter by customer
        if ($nguoiDung) {
            $query->whereHas('nguoiDung', function ($q) use ($nguoiDung) {
                $q->where('ho_ten', 'like', '%' . $nguoiDung . '%');
            });
        }

        // Filter by date
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            switch ($period) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', Carbon::now()->year);
                    break;
            }
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->limit(intval($limit))
            ->get()
            ->map(function($booking) {
                // Calculate total amount
                $seatTotal = ChiTietDatVe::where('id_dat_ve', $booking->id)
                    ->sum('gia_ve');
                
                $comboTotal = 0;
                if (DB::getSchemaBuilder()->hasTable('chi_tiet_dat_ve_combo')) {
                    $comboTotal = DB::table('chi_tiet_dat_ve_combo')
                        ->where('id_dat_ve', $booking->id)
                        ->sum(DB::raw('gia_ap_dung * COALESCE(so_luong, 1)'));
                }

                $totalAmount = $seatTotal + $comboTotal;

                // Get seat labels
                $seatLabels = $booking->chiTietDatVe->map(function($ct) {
                    return $ct->ghe ? $ct->ghe->so_ghe : null;
                })->filter()->implode(', ');

                // Get combo labels
                $comboLabels = '';
                if ($booking->chiTietCombo) {
                    $comboLabels = $booking->chiTietCombo->map(function($ct) {
                        $name = $ct->combo ? $ct->combo->ten : '';
                        $qty = $ct->so_luong ? ' × ' . $ct->so_luong : '';
                        return $name . $qty;
                    })->filter()->implode(', ');
                }

                return [
                    'id' => $booking->id,
                    'customer_name' => $booking->nguoiDung->ho_ten ?? '',
                    'customer_email' => $booking->nguoiDung->email ?? '',
                    'movie_name' => $booking->suatChieu->phim->ten_phim ?? '',
                    'showtime_date' => $booking->suatChieu ? $booking->suatChieu->thoi_gian_bat_dau : null,
                    'room_name' => $booking->suatChieu->phongChieu->ten_phong ?? '',
                    'seat_labels' => $seatLabels,
                    'combo_labels' => $comboLabels,
                    'promotion_code' => $booking->khuyenMai->ma_km ?? '',
                    'total_amount' => $totalAmount,
                    'status' => $booking->trang_thai,
                    'status_text' => $this->getBookingStatusText($booking->trang_thai),
                    'created_at' => $booking->created_at
                ];
            });

        // Statistics
        $baseQuery = DatVe::query();
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            switch ($period) {
                case 'today':
                    $baseQuery->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $baseQuery->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $baseQuery->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'year':
                    $baseQuery->whereYear('created_at', Carbon::now()->year);
                    break;
            }
        }

        $stats = [
            'total_bookings' => (int) $baseQuery->count(),
            'pending' => (int) (clone $baseQuery)->where('trang_thai', 0)->count(),
            'confirmed' => (int) (clone $baseQuery)->where('trang_thai', 1)->count(),
            'canceled' => (int) (clone $baseQuery)->where('trang_thai', 2)->count(),
            'request_cancel' => (int) (clone $baseQuery)->where('trang_thai', 3)->count(),
            'total_revenue' => ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
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
                ->sum('chi_tiet_dat_ve.gia_ve'),
            'total_tickets' => ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
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
                ->count()
        ];

        return response()->json([
            'bookings' => $bookings,
            'statistics' => $stats,
            'period' => $period,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'phim' => $phim,
                'nguoi_dung' => $nguoiDung
            ]
        ]);
    }

    private function getBookingStatusText($status)
    {
        switch ($status) {
            case 0:
                return 'Chờ xác nhận';
            case 1:
                return 'Đã xác nhận';
            case 2:
                return 'Đã hủy';
            case 3:
                return 'Yêu cầu hủy';
            default:
                return 'Không xác định';
        }
    }
}
