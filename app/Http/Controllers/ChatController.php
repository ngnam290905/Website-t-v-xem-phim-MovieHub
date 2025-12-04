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
        $message = $request->input('message') ?? $request->input('text') ?? $request->input('content');
        $message = is_string($message) ? trim($message) : '';
        if ($message === '') {
            return response()->json([
                'status' => true,
                'response' => 'Bạn hãy nhập nội dung, ví dụ: "tìm phim hành động" hoặc "lịch chiếu hôm nay".',
                'type' => 'unknown'
            ]);
        }

        // Kiểm tra training data (an toàn nếu bảng chưa tồn tại)
        try {
            $training = AiTraining::where('question', 'like', '%' . $message . '%')->first();
            if ($training) {
                return response()->json([
                    'status' => true,
                    'response' => $training->answer,
                    'type' => 'training'
                ]);
            }
        } catch (\Throwable $e) {
            Log::info('AI training lookup skipped: '.$e->getMessage());
        }

        // Gọi AI service
        try {
            $intentData = $this->aiService->analyzeIntent($message);
        } catch (\Throwable $e) {
            Log::warning('AI analyze failed: '.$e->getMessage());
            $intentData = ['intent' => 'unknown'];
        }

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
            $query = SuatChieu::with(['phim:id,ten_phim', 'phongChieu:id,ten_phong'])
                ->whereHas('phim')
                ->where('thoi_gian_ket_thuc', '>', now()); // Only showtimes that haven't ended

            if (isset($params['movie'])) {
                $query->whereHas('phim', function($q) use ($params) {
                    $q->where('ten_phim', 'like', '%' . $params['movie'] . '%');
                });
            }
            if (isset($params['time'])) {
                $query->whereTime('thoi_gian_bat_dau', $params['time']);
            }

            $raw = $query->get(['id', 'id_phim', 'id_phong', 'thoi_gian_bat_dau', 'thoi_gian_ket_thuc']);
            $showtimes = $raw->map(function($s){
                return [
                    'id' => $s->id,
                    'movie' => optional($s->phim)->ten_phim,
                    'room' => optional($s->phongChieu)->ten_phong,
                    'start' => optional($s->thoi_gian_bat_dau)->format('d/m/Y H:i'),
                    'end' => optional($s->thoi_gian_ket_thuc)->format('d/m/Y H:i'),
                    // giữ trường cũ để tương thích render cũ
                    'phim' => ['ten_phim' => optional($s->phim)->ten_phim],
                    'thoi_gian_bat_dau' => optional($s->thoi_gian_bat_dau)->toIso8601String(),
                ];
            });

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

        // Fallback thân thiện (trả về status=true để UI không hiện lỗi hệ thống)
        return response()->json([
            'status' => true,
            'response' => 'Xin lỗi, tôi chưa hiểu. Bạn có thể thử: "tìm phim [thể loại]" hoặc "lịch chiếu [tên phim]" hoặc gõ "đặt vé".',
            'type' => 'unknown'
        ]);
    }
}
