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

        // Get hot movies
        $hotMovies = Phim::where('hot', true)
            ->whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->take(5)
            ->get();

        // Get now showing movies
        $nowShowing = Phim::where('trang_thai', 'dang_chieu')
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->take(6)
            ->get();

        // Get coming soon movies
        $comingSoon = Phim::where('trang_thai', 'sap_chieu')
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->take(6)
            ->get();

        return view('home', [
            'allMovies' => $allMovies,
            'hotMovies' => $hotMovies,
            'nowShowing' => $nowShowing,
            'comingSoon' => $comingSoon
        ]);
    }
}
