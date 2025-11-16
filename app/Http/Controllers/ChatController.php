<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiService;
use App\Models\AiTraining;
use App\Models\Phim;
use App\Models\SuatChieu;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function chat(Request $request)
    {
        $message = $request->input('message');

        // Kiểm tra training data
        $training = AiTraining::where('question', 'like', '%' . $message . '%')->first();
        if ($training) {
            return response()->json([
                'status' => true,
                'response' => $training->answer,
                'type' => 'training'
            ]);
        }

        // Gọi AI service
        $intentData = $this->aiService->analyzeIntent($message);

        if ($intentData['intent'] === 'movie_search') {
            $params = $intentData['params'] ?? [];
            $query = Phim::query();

            if (isset($params['genre'])) {
                $query->where('the_loai', 'like', '%' . $params['genre'] . '%');
            }
            if (isset($params['country'])) {
                $query->where('quoc_gia', 'like', '%' . $params['country'] . '%');
            }
            if (isset($params['name'])) {
                $query->where('ten_phim', 'like', '%' . $params['name'] . '%');
            }

            $movies = $query->get(['id', 'ten_phim', 'the_loai', 'quoc_gia', 'poster']);

            return response()->json([
                'status' => true,
                'movies' => $movies,
                'type' => 'movie_search'
            ]);
        }

        if ($intentData['intent'] === 'showtime_search') {
            $params = $intentData['params'] ?? [];
            $query = SuatChieu::with('phim:id,ten_phim')->whereHas('phim');

            if (isset($params['movie'])) {
                $query->whereHas('phim', function($q) use ($params) {
                    $q->where('ten_phim', 'like', '%' . $params['movie'] . '%');
                });
            }
            if (isset($params['time'])) {
                $query->whereTime('thoi_gian_bat_dau', $params['time']);
            }

            $showtimes = $query->get(['id', 'id_phim', 'thoi_gian_bat_dau', 'thoi_gian_ket_thuc']);

            return response()->json([
                'status' => true,
                'showtimes' => $showtimes,
                'type' => 'showtime_search'
            ]);
        }

        if ($intentData['action'] === 'open_booking') {
            $params = $intentData['params'] ?? [];
            $movieId = $params['movie_id'] ?? null;
            $showId = $params['show_id'] ?? null;

            if ($movieId && $showId) {
                $link = url("/booking/movie/{$movieId}/show/{$showId}");
                return response()->json([
                    'status' => true,
                    'link' => $link,
                    'type' => 'booking_link'
                ]);
            }
        }

        // Fallback
        return response()->json([
            'status' => false,
            'response' => 'Xin lỗi, tôi không hiểu câu hỏi của bạn.',
            'type' => 'unknown'
        ]);
    }
}
