<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiIntentService;
use App\Models\SuatChieu;
use App\Models\Phim;
use Carbon\Carbon;

class AiSearchController extends Controller
{
    protected $ai;

    public function __construct(AiIntentService $ai)
    {
        $this->ai = $ai;
    }

    public function search(Request $request)
    {
        $message = $request->input('message');
        if (!$message) return response()->json(['error' => 'message is required'], 400);

        $parsed = $this->ai->parseIntent($message);
        if (isset($parsed['error'])) {
            return response()->json(['error' => 'AI error', 'detail' => $parsed], 500);
        }

        if (!empty($parsed['function']) && $parsed['function'] === 'find_movie') {
            $args = $parsed['arguments'] ?? [];
            $title = $args['title'] ?? '';
            $limit = $args['limit'] ?? 5;
            if ($title) {
                $movies = $this->ai->findMoviesByTitle($title, $limit);
            } else {
                // fallback: use filters
                $q = Phim::query();
                if (!empty($args['genre'])) $q->where('the_loai', 'like', "%{$args['genre']}%");
                if (!empty($args['actor'])) $q->where('dien_vien', 'like', "%{$args['actor']}%");
                $movies = $q->take($limit)->get();
            }
            return response()->json(['type' => 'movies', 'data' => $movies]);
        }

        if (!empty($parsed['function']) && $parsed['function'] === 'find_showtime') {
            $args = $parsed['arguments'] ?? [];
            $date = $args['date'] ?? Carbon::now()->toDateString();
            $movieId = $args['movie_id'] ?? null;

            if (!$movieId && !empty($args['movie_title'])) {
                $candidates = $this->ai->findMoviesByTitle($args['movie_title'], 3);
                if ($candidates->count() === 1) $movieId = $candidates->first()->id;
                elseif ($candidates->count() > 1) {
                    // ambiguous - return candidates for user to choose
                    return response()->json(['type' => 'disambiguation', 'candidates' => $candidates]);
                }
            }

            $q = SuatChieu::query()->with(['phim', 'phongChieu']);
            $q->whereDate('thoi_gian_bat_dau', $date);
            if ($movieId) $q->where('id_phim', $movieId);
            // time_range, city, theater filters can be added here

            $shows = $q->orderBy('thoi_gian_bat_dau')->get();
            return response()->json(['type' => 'showtimes', 'data' => $shows]);
        }

        // fallback: assistant text
        $text = $parsed['text'] ?? 'Mình chưa hiểu, bạn muốn tìm phim hay suất chiếu?';
        return response()->json(['type' => 'text', 'data' => $text]);
    }
}
