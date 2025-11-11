<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ShowController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;

Route::prefix('api/v1')->group(function () {
    Route::get('shows/{showId}/seat-map', [ShowController::class, 'getSeatMap']);
    Route::get('movies/{movieId}/shows', [ShowController::class, 'getShowsByMovie']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('bookings', [BookingController::class, 'create']);
        Route::get('bookings/{id}', [BookingController::class, 'show']);
    });
    
    Route::post('payments/webhook', [PaymentController::class, 'webhook']);
    Route::get('payments/return', [PaymentController::class, 'return']);
});

