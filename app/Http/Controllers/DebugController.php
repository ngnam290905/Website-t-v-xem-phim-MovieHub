<?php

namespace App\Http\Controllers;

use App\Models\SuatChieu;
use App\Models\Phim;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DebugController extends Controller
{
    public function checkShowtimes(Request $request)
    {
        $movieId = $request->get('movie_id');
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $data = [
            'current_time' => now()->format('Y-m-d H:i:s'),
            'selected_date' => $date,
            'query_conditions' => [
                'movie_id' => $movieId,
                'trang_thai' => 1,
                'thoi_gian_bat_dau >' => now()->format('Y-m-d H:i:s'),
                'thoi_gian_bat_dau <=' => now()->addDays(7)->format('Y-m-d H:i:s'),
            ]
        ];
        
        if ($movieId) {
            $query = SuatChieu::where('id_phim', $movieId)
                ->where('trang_thai', 1)
                ->where('thoi_gian_bat_dau', '>', now())
                ->where('thoi_gian_bat_dau', '<=', now()->addDays(7));
            
            $data['total_showtimes'] = $query->count();
            $data['showtimes'] = $query->get()->map(function($st) {
                return [
                    'id' => $st->id,
                    'thoi_gian_bat_dau' => $st->thoi_gian_bat_dau->format('Y-m-d H:i:s'),
                    'phong_chieu' => $st->phongChieu->ten_phong ?? 'N/A',
                    'trang_thai' => $st->trang_thai,
                ];
            });
            
            // Check by date
            $byDate = SuatChieu::where('id_phim', $movieId)
                ->where('trang_thai', 1)
                ->whereDate('thoi_gian_bat_dau', $date)
                ->where('thoi_gian_bat_dau', '>', now())
                ->get();
            
            $data['showtimes_for_date'] = $byDate->map(function($st) {
                return [
                    'id' => $st->id,
                    'thoi_gian_bat_dau' => $st->thoi_gian_bat_dau->format('Y-m-d H:i:s'),
                    'phong_chieu' => $st->phongChieu->ten_phong ?? 'N/A',
                ];
            });
        } else {
            // All showtimes
            $all = SuatChieu::where('trang_thai', 1)
                ->where('thoi_gian_bat_dau', '>', now())
                ->where('thoi_gian_bat_dau', '<=', now()->addDays(7))
                ->with('phim')
                ->get();
            
            $data['all_showtimes'] = $all->map(function($st) {
                return [
                    'id' => $st->id,
                    'phim' => $st->phim->ten_phim ?? 'N/A',
                    'thoi_gian_bat_dau' => $st->thoi_gian_bat_dau->format('Y-m-d H:i:s'),
                    'phong_chieu' => $st->phongChieu->ten_phong ?? 'N/A',
                ];
            });
        }
        
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }
}

