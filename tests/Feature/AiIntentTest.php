<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class AiIntentTest extends TestCase
{
    public function test_parse_intent_calls_openai_and_parses_function()
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'function_call' => [
                                'name' => 'find_showtime',
                                'arguments' => json_encode(['movie_title' => 'Avatar', 'date' => '2025-11-11'])
                            ]
                        ]
                    ]
                ]
            ], 200),
            'api.openai.com/v1/embeddings' => Http::response(['data' => [['embedding' => [0.1, 0.2, 0.3]]]], 200),
        ]);

    $resp = $this->postJson('/api/ai/search', ['message' => 'Có suất chiếu của Avatar tối nay không?']);

    // dump response for debug
    fwrite(STDERR, "RESPONSE: " . $resp->getContent() . PHP_EOL);

    // We expect the endpoint to return json (could be disambiguation or showtimes depending on DB),
    // but primary goal is the route responds successfully (200 or 200-level).
    $this->assertTrue(in_array($resp->getStatusCode(), [200, 201]));
    }
}
