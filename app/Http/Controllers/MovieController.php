<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\SuatChieu;
use App\Models\PhongChieu;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index()
    {
        // Get featured movies for hero carousel
        $featuredMovies = Movie::active()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Get all active movies for movie grid
        $movies = Movie::active()
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get hot movies (most recent)
        $hotMovies = Movie::active()
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
        
        // Get coming soon movies (you can add a release_date field later)
        $comingSoonMovies = Movie::active()
            ->where('trang_thai', true)
            ->orderBy('created_at', 'asc')
            ->take(6)
            ->get();

        return view('home', compact('featuredMovies', 'movies', 'hotMovies', 'comingSoonMovies'));
    }

    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        
        // Get suat chieu for this movie
        $suatChieu = SuatChieu::with(['phongChieu'])
            ->where('id_phim', $id)
            ->where('trang_thai', true)
            ->where('thoi_gian_bat_dau', '>=', now())
            ->orderBy('thoi_gian_bat_dau')
            ->get();
        
        // Get related movies (same genre or director)
        $relatedMovies = Movie::active()
            ->where('id', '!=', $id)
            ->where(function($query) use ($movie) {
                $query->where('dao_dien', 'like', '%' . $movie->dao_dien . '%')
                      ->orWhere('dien_vien', 'like', '%' . $movie->dien_vien . '%');
            })
            ->take(4)
            ->get();

        return view('movie-detail', compact('movie', 'relatedMovies', 'suatChieu'));
    }

    public function getMovies()
    {
        $movies = Movie::active()
            ->select('id', 'ten_phim', 'poster', 'do_dai', 'dao_dien', 'dien_vien', 'mo_ta')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $movies
        ]);
    }

    public function getFeaturedMovies()
    {
        $featuredMovies = Movie::active()
            ->select('id', 'ten_phim', 'poster', 'mo_ta', 'dao_dien', 'dien_vien', 'trailer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $featuredMovies
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $movies = Movie::active()
            ->where(function($q) use ($query) {
                $q->where('ten_phim', 'like', '%' . $query . '%')
                  ->orWhere('dao_dien', 'like', '%' . $query . '%')
                  ->orWhere('dien_vien', 'like', '%' . $query . '%')
                  ->orWhere('mo_ta', 'like', '%' . $query . '%');
            })
            ->select('id', 'ten_phim', 'poster', 'do_dai', 'dao_dien', 'dien_vien', 'mo_ta')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $movies,
            'query' => $query
        ]);
    }

    public function getSuatChieu($movieId)
    {
        $suatChieu = SuatChieu::with(['phongChieu'])
            ->where('id_phim', $movieId)
            ->where('trang_thai', true)
            ->where('thoi_gian_bat_dau', '>=', now())
            ->orderBy('thoi_gian_bat_dau')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $suatChieu
        ]);
    }

    public function getPhongChieu()
    {
        $phongChieu = PhongChieu::where('trang_thai', true)->get();

        return response()->json([
            'success' => true,
            'data' => $phongChieu
        ]);
    }
}
