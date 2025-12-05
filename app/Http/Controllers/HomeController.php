<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index()
    {
        // Get all movies with pagination
        $allMovies = Phim::orderBy('ngay_khoi_chieu', 'desc')->paginate(12);

        // Get hot movies - robust fallback if columns/values differ
        $hotMovies = collect();
        if (\Illuminate\Support\Facades\Schema::hasColumn('phim', 'hot')) {
            $hotMovies = Phim::where('hot', true)
                ->orderBy('ngay_khoi_chieu', 'desc')
                ->take(5)
                ->get();
        }
        if ($hotMovies->isEmpty()) {
            $query = Phim::query();
            if (\Illuminate\Support\Facades\Schema::hasColumn('phim', 'trang_thai')) {
                $query->where(function ($q) {
                    $q->where('trang_thai', 1)
                      ->orWhereIn('trang_thai', ['dang_chieu', 'sap_chieu']);
                });
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('phim', 'ngay_khoi_chieu')) {
                $query->orderBy('ngay_khoi_chieu', 'desc');
            } else {
                $query->orderByDesc('created_at');
            }
            $hotMovies = $query->take(5)->get();
        }

        // Get now showing movies: match admin definition strictly (status only)
        $nowQuery = Phim::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('phim', 'trang_thai')) {
            $nowQuery->where('trang_thai', 'dang_chieu');
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('phim', 'ngay_khoi_chieu')) {
            $nowQuery->orderBy('ngay_khoi_chieu', 'desc');
        } else {
            $nowQuery->orderByDesc('created_at');
        }
        $nowShowing = $nowQuery->take(6)->get();

        // Get coming soon movies (robust)
        $soonQuery = Phim::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('phim', 'trang_thai')) {
            $soonQuery->where(function ($q) {
                $q->where('trang_thai', 'sap_chieu')
                  ->orWhere('trang_thai', 0);
            });
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('phim', 'ngay_khoi_chieu')) {
            $soonQuery->orderBy('ngay_khoi_chieu', 'desc');
        } else {
            $soonQuery->orderByDesc('created_at');
        }
        $comingSoon = $soonQuery->take(6)->get();

        return view('home', [
            'allMovies' => $allMovies,
            'hotMovies' => $hotMovies,
            'nowShowing' => $nowShowing,
            'comingSoon' => $comingSoon
        ]);
    }
}
