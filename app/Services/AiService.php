<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    public function analyzeIntent($message)
    {
        $systemPrompt = 'Bạn là trợ lý AI cho hệ thống đặt vé rạp chiếu phim. Phân tích câu hỏi của người dùng và trả về JSON theo định dạng chính xác. Các intent có thể là:
        - Tìm phim: {"intent": "movie_search", "params": {"genre": "tên thể loại", "country": "tên quốc gia", "name": "tên phim"}}
        - Tìm suất chiếu: {"intent": "showtime_search", "params": {"movie": "tên phim", "time": "thời gian", "date": "ngày"}}
        - Đặt vé: {"action": "open_booking", "params": {"movie_id": số, "show_id": số}}
        - Nếu không khớp: {"intent": "unknown"}
        Chỉ trả về JSON, không thêm text khác.';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => 300,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'];
            return json_decode($content, true);
        }

        return ['intent' => 'error'];
    }
}