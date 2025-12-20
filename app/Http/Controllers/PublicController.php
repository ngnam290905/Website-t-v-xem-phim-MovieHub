<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use App\Models\SuatChieu;
use App\Models\Combo;
use App\Models\TinTuc;
use App\Models\LoaiGhe;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    /**
     * Display movies page
     */
    public function movies(Request $request)
    {
        $query = Phim::query();

        // Filter by status
        $status = $request->get('status', 'dang_chieu');
        if (in_array($status, ['dang_chieu', 'sap_chieu', 'ngung_chieu'])) {
            $query->where('trang_thai', $status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('ten_phim', 'like', "%{$search}%")
                  ->orWhere('dao_dien', 'like', "%{$search}%")
                  ->orWhere('dien_vien', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'ngay_khoi_chieu');
        $order = $request->get('order', 'desc');
        
        if ($sort === 'rating') {
            $query->orderBy('diem_danh_gia', $order);
        } elseif ($sort === 'name') {
            $query->orderBy('ten_phim', $order);
        } else {
            $query->orderBy('ngay_khoi_chieu', $order);
        }

        $movies = $query->paginate(12);

        return view('public.movies', compact('movies', 'status'));
    }

    /**
     * Display schedule/showtimes page
     */
    public function schedule(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $carbonDate = Carbon::parse($date);

        // Get showtimes for the selected date (only those that haven't ended)
        $showtimes = SuatChieu::where('trang_thai', 1)
            ->whereDate('thoi_gian_bat_dau', $date)
            ->where('thoi_gian_ket_thuc', '>', now()) // Only showtimes that haven't ended
            ->with(['phim', 'phongChieu'])
            ->orderBy('thoi_gian_bat_dau')
            ->get()
            ->groupBy(function($showtime) {
                return $showtime->phim->id;
            });

        // Get available dates (next 7 days)
        $availableDates = [];
        for ($i = 0; $i < 7; $i++) {
            $dateItem = now()->addDays($i);
            $availableDates[] = [
                'date' => $dateItem->format('Y-m-d'),
                'formatted' => $dateItem->format('d/m/Y'),
                'day_name' => $dateItem->format('l'),
                'is_today' => $dateItem->isToday(),
                'is_tomorrow' => $dateItem->isTomorrow(),
            ];
        }

        return view('public.schedule', compact('showtimes', 'availableDates', 'date'));
    }

    /**
     * Display combos page
     */
    public function combos()
    {
        $combos = Combo::where('trang_thai', 1)
            ->where(function($query) {
                $query->whereNull('ngay_bat_dau')
                      ->orWhere('ngay_bat_dau', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('ngay_ket_thuc')
                      ->orWhere('ngay_ket_thuc', '>=', now());
            })
            ->orderBy('combo_noi_bat', 'desc')
            ->orderBy('gia', 'asc')
            ->get();

        $featuredCombos = $combos->where('combo_noi_bat', true);
        $regularCombos = $combos->where('combo_noi_bat', false);

        return view('public.combos', compact('featuredCombos', 'regularCombos'));
    }

    /**
     * Display news page
     */
    public function news(Request $request)
    {
        $query = TinTuc::published();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('tieu_de', 'like', "%{$search}%")
                  ->orWhere('tom_tat', 'like', "%{$search}%")
                  ->orWhere('noi_dung', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('the_loai', $request->get('category'));
        }

        $news = $query->orderBy('ngay_dang', 'desc')
            ->paginate(9);

        $featuredNews = TinTuc::published()
            ->featured()
            ->orderBy('ngay_dang', 'desc')
            ->take(3)
            ->get();

        $categories = TinTuc::published()
            ->whereNotNull('the_loai')
            ->distinct()
            ->pluck('the_loai')
            ->filter()
            ->values();

        return view('public.news', compact('news', 'featuredNews', 'categories'));
    }

    /**
     * Display single news article
     */
    public function newsDetail($slug)
    {
        $article = TinTuc::where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Increment views
        $article->incrementViews();

        // Get related news
        $relatedNews = TinTuc::published()
            ->where('id', '!=', $article->id)
            ->where(function($query) use ($article) {
                if ($article->the_loai) {
                    $query->where('the_loai', $article->the_loai)
                          ->orWhere('tac_gia', $article->tac_gia);
                }
            })
            ->orderBy('ngay_dang', 'desc')
            ->take(4)
            ->get();

        return view('public.news-detail', compact('article', 'relatedNews'));
    }

    /**
     * Display pricing page
     */
    public function pricing()
    {
        // Get seat types with prices
        $seatTypes = LoaiGhe::orderBy('he_so_gia', 'asc')->get();
        
        // Get base price from a sample movie (or use default)
        $sampleMovie = Phim::where('trang_thai', 'dang_chieu')->first();
        $basePrice = $sampleMovie->gia_co_ban ?? 100000;
        
        // Calculate prices for each seat type
        $pricing = $seatTypes->map(function($seatType) use ($basePrice) {
            return [
                'name' => $seatType->ten_loai,
                'coefficient' => $seatType->he_so_gia ?? 1.0,
                'price' => round($basePrice * ($seatType->he_so_gia ?? 1.0)),
            ];
        });
        
        // Get time-based pricing rules
        $timeRules = DB::table('cau_hinh_he_so_thoi_gian')
            ->where('trang_thai', true)
            ->orderBy('he_so')
            ->get();
        
        // Get combo prices
        $combos = Combo::where('trang_thai', 1)
            ->where(function($query) {
                $query->whereNull('ngay_bat_dau')
                      ->orWhere('ngay_bat_dau', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('ngay_ket_thuc')
                      ->orWhere('ngay_ket_thuc', '>=', now());
            })
            ->orderBy('gia', 'asc')
            ->get();
        
        return view('public.pricing', compact('pricing', 'basePrice', 'timeRules', 'combos'));
    }
}

