<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ThanhToan;
use App\Models\DatVe;
use App\Observers\ThanhToanObserver;
use App\Observers\DatVeObserver;

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
        
        // Đăng ký Observer cho DatVe - tự động tích điểm khi thanh toán
        DatVe::observe(DatVeObserver::class);
    }
}
