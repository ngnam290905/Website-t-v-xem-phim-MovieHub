<?php

namespace App\Observers;

use App\Models\ThanhToan;

class ThanhToanObserver
{
    /**
     * Handle the ThanhToan "created" event.
     */
    public function created(ThanhToan $thanhToan): void
    {
        // Implement logic after a payment is created, if needed.
    }

    /**
     * Handle the ThanhToan "updated" event.
     */
    public function updated(ThanhToan $thanhToan): void
    {
        // Implement logic when a payment is updated, if needed.
    }

    /**
     * Handle the ThanhToan "deleted" event.
     */
    public function deleted(ThanhToan $thanhToan): void
    {
        // Implement logic when a payment is deleted, if needed.
    }

    /**
     * Handle the ThanhToan "restored" event.
     */
    public function restored(ThanhToan $thanhToan): void
    {
        // Implement logic when a payment is restored, if needed.
    }

    /**
     * Handle the ThanhToan "force deleted" event.
     */
    public function forceDeleted(ThanhToan $thanhToan): void
    {
        // Implement logic when a payment is force deleted, if needed.
    }
}
