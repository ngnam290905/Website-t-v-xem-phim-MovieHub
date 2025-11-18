<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\BookingController;

Route::post('/chat', [ChatController::class, 'chat']);

Route::get('/booked-seats/{showtimeId}', [BookingController::class, 'getBookedSeats']);
Route::get('/showtime-seats/{showtimeId}', [BookingController::class, 'getShowtimeSeats']);
Route::get('/ticket/{id}', [BookingController::class, 'getTicket']);