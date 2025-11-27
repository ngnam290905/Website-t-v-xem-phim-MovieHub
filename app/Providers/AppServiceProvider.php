<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ThanhToan;
use App\Observers\ThanhToanObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đăng ký Observer cho ThanhToan
        ThanhToan::observe(ThanhToanObserver::class);
    }
}
