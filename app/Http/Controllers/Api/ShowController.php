<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Show;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShowController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    public function getSeatMap(int $showId): JsonResponse
    {
        $show = Show::with(['movie', 'room'])->findOrFail($showId);
        
        $seatMap = $this->bookingService->getSeatMap($show);
        
        return response()->json([
            'success' => true,
            'data' => [
                'show' => [
                    'id' => $show->id,
                    'movie' => [
                        'id' => $show->movie->id,
                        'title' => $show->movie->title,
                        'duration_minutes' => $show->movie->duration_minutes,
                    ],
                    'room' => [
                        'id' => $show->room->id,
                        'name' => $show->room->name,
                    ],
                    'start_at' => $show->start_at->toIso8601String(),
                    'end_at' => $show->end_at->toIso8601String(),
                    'base_price' => $show->base_price,
                ],
                'seats' => $seatMap,
            ],
        ]);
    }

    public function getShowsByMovie(int $movieId): JsonResponse
    {
        $shows = Show::with(['movie', 'room'])
            ->where('movie_id', $movieId)
            ->where('end_at', '>', now()) // Only shows that haven't ended
            ->orderBy('start_at')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $shows->map(function ($show) {
                return [
                    'id' => $show->id,
                    'movie_id' => $show->movie_id,
                    'room_id' => $show->room_id,
                    'room_name' => $show->room->name,
                    'start_at' => $show->start_at->toIso8601String(),
                    'end_at' => $show->end_at->toIso8601String(),
                    'base_price' => $show->base_price,
                ];
            }),
        ]);
    }
}

