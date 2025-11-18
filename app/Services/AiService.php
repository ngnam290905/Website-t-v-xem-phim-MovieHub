<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    public function analyzeIntent($message)
    {
        // Local quick heuristic if no API key or empty message
        if (!$this->apiKey || !is_string($message) || trim($message) === '') {
            return $this->localHeuristic($message);
        }

        $systemPrompt = 'Bạn là trợ lý AI cho hệ thống đặt vé rạp chiếu phim. Phân tích câu hỏi của người dùng và trả về JSON theo định dạng chính xác. Các intent có thể là:
        - Tìm phim: {"intent": "movie_search", "params": {"genre": "tên thể loại", "country": "tên quốc gia", "name": "tên phim"}}
        - Tìm suất chiếu: {"intent": "showtime_search", "params": {"movie": "tên phim", "time": "thời gian", "date": "ngày"}}
        - Đặt vé: {"action": "open_booking", "params": {"movie_id": số, "show_id": số}}
        - Nếu không khớp: {"intent": "unknown"}
        Chỉ trả về JSON, không thêm text khác.';

        $response = Http::timeout(15)->withHeaders([
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
            $content = $data['choices'][0]['message']['content'] ?? '';
            $parsed = $this->safeJson($content);
            if (is_array($parsed)) { return $parsed; }
            // If model returns non-JSON text, fallback
            return $this->localHeuristic($message);
        }

        // HTTP error fallback
        return $this->localHeuristic($message);
    }

    private function safeJson(?string $content)
    {
        if (!$content) return null;
        $content = trim($content);
        // Strip code fences if present
        $content = preg_replace('/^```(json)?/i', '', $content);
        $content = preg_replace('/```$/', '', $content);
        $content = trim($content);
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        // Try to extract first JSON object
        if (Str::contains($content, '{')) {
            $start = strpos($content, '{');
            $end = strrpos($content, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $maybe = substr($content, $start, $end - $start + 1);
                $decoded = json_decode($maybe, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
            }
        }
        return null;
    }

    private function localHeuristic(?string $message): array
    {
        $text = Str::lower($message ?? '');
        // Very lightweight rules
        if (Str::contains($text, ['suất chiếu', 'giờ chiếu', 'lịch chiếu'])) {
            return ['intent' => 'showtime_search', 'params' => []];
        }
        if (Str::contains($text, ['phim', 'tìm phim', 'movie'])) {
            return ['intent' => 'movie_search', 'params' => []];
        }
        if (Str::contains($text, ['đặt vé', 'booking'])) {
            return ['action' => 'open_booking', 'params' => []];
        }
        return ['intent' => 'unknown'];
    }
}