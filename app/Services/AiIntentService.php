<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Phim;
use Carbon\Carbon;

class AiIntentService
{
    protected $openaiKey;
    protected $chatModel = 'gpt-4o-mini';
    protected $embedModel = 'text-embedding-3-small';

    public function __construct()
    {
        $this->openaiKey = config('services.openai.key');
    }

    /**
     * Call OpenAI Chat Completions with function-calling to parse intent.
     * Returns array: ['function' => name, 'arguments' => array]
     */
    public function parseIntent(string $message): array
    {
        $functions = config('openai_functions.functions');

        $resp = Http::withToken($this->openaiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->chatModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'Bạn là một trợ lý trích xuất tham số cho ứng dụng tìm phim và suất chiếu. Trả về function call với JSON argument phù hợp.'],
                    ['role' => 'user', 'content' => $message],
                ],
                'functions' => $functions,
                'function_call' => 'auto',
                'temperature' => 0,
            ]);

        if (!$resp->successful()) {
            return ['error' => true, 'message' => 'OpenAI request failed', 'status' => $resp->status()];
        }

        $body = $resp->json();
        $choice = $body['choices'][0] ?? null;
        if (!$choice) return ['error' => true, 'message' => 'No choice returned'];

        $messageObj = $choice['message'] ?? [];
        if (isset($messageObj['function_call'])) {
            $fn = $messageObj['function_call']['name'];
            $argsRaw = $messageObj['function_call']['arguments'] ?? '{}';
            $args = json_decode($argsRaw, true) ?? [];
            return ['function' => $fn, 'arguments' => $args];
        }

        // fallback: return assistant text
        return ['function' => null, 'text' => $messageObj['content'] ?? ''];
    }

    /**
     * Request embedding for a piece of text
     */
    public function getEmbedding(string $text): ?array
    {
        $resp = Http::withToken($this->openaiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => $this->embedModel,
                'input' => $text,
            ]);

        if (!$resp->successful()) return null;
        $body = $resp->json();
        return $body['data'][0]['embedding'] ?? null;
    }

    /**
     * Build embeddings for all movies and store in storage/app/movie_embeddings.json
     */
    public function buildMovieEmbeddings(): array
    {
        $movies = Phim::select('id', 'ten_phim', 'ten_goc', 'mo_ta')->get();
        $out = [];

        foreach ($movies as $m) {
            $text = $m->ten_phim;
            if (empty($text) && $m->ten_goc) $text = $m->ten_goc;
            if (empty($text) && $m->mo_ta) $text = substr($m->mo_ta, 0, 200);

            $embedding = $this->getEmbedding($text);
            if ($embedding) {
                $out[] = [
                    'movie_id' => $m->id,
                    'title' => $m->ten_phim,
                    'embedding' => $embedding,
                ];
            }
            // small sleep to be gentle with API
            usleep(200000);
        }

        Storage::disk('local')->put('movie_embeddings.json', json_encode($out));
        return $out;
    }

    /**
     * Try to find movies by title: exact/like then embeddings fallback
     */
    public function findMoviesByTitle(string $title, int $limit = 5)
    {
        $q = Phim::query();
        $q->where('ten_phim', 'like', "%{$title}%")
            ->orWhere('ten_goc', 'like', "%{$title}%");

        $res = $q->take($limit)->get();
        if ($res->count() > 0) return $res;

        // embeddings fallback
        $embedding = $this->getEmbedding($title);
        if (!$embedding) return collect();

        $candidates = $this->findMovieCandidatesByEmbedding($embedding, $limit);
        if (empty($candidates)) return collect();

        $ids = array_column($candidates, 'movie_id');
        return Phim::whereIn('id', $ids)->get();
    }

    protected function findMovieCandidatesByEmbedding(array $embedding, int $limit = 5): array
    {
        $json = Storage::disk('local')->get('movie_embeddings.json') ?? '[]';
        $rows = json_decode($json, true) ?: [];

        $scores = [];
        foreach ($rows as $r) {
            if (!isset($r['embedding'])) continue;
            $sim = $this->cosineSimilarity($embedding, $r['embedding']);
            $scores[] = ['movie_id' => $r['movie_id'], 'title' => $r['title'], 'score' => $sim];
        }

        usort($scores, function ($a, $b) { return $b['score'] <=> $a['score']; });
        return array_slice($scores, 0, $limit);
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $na = 0.0;
        $nb = 0.0;
        $len = min(count($a), count($b));
        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $na += $a[$i] * $a[$i];
            $nb += $b[$i] * $b[$i];
        }
        if ($na == 0 || $nb == 0) return 0.0;
        return $dot / (sqrt($na) * sqrt($nb));
    }
}
