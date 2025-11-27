<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ThanhToan;
<<<<<<< HEAD
use App\Models\DatVe;
use App\Observers\ThanhToanObserver;
use App\Observers\DatVeObserver;
=======
use App\Observers\ThanhToanObserver;
>>>>>>> origin/hoanganh

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
<<<<<<< HEAD
        // Đăng ký Observer cho ThanhToan
        ThanhToan::observe(ThanhToanObserver::class);
        
        // Đăng ký Observer cho DatVe - tự động tích điểm khi thanh toán
        DatVe::observe(DatVeObserver::class);
=======
        ThanhToan::observe(ThanhToanObserver::class);
>>>>>>> origin/hoanganh
    }
}
