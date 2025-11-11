<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiSearchController;

// API routes (stateless)
Route::post('/ai/search', [AiSearchController::class, 'search'])->name('api.ai.search');
