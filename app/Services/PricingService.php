<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\Show;
use App\Models\Combo;

class PricingService
{
    private const TYPE_MULTIPLIERS = [
        'STANDARD' => 1.0,
        'VIP' => 1.2,
        'COUPLE' => 1.5,
    ];

    public function calculateSeatPrice(Show $show, Seat $seat): float
    {
        $basePrice = $show->base_price;
        $multiplier = self::TYPE_MULTIPLIERS[$seat->type] ?? 1.0;
        
        return round($basePrice * $multiplier, 2);
    }

    public function calculateBookingSubtotal(array $seatPrices, array $comboItems = []): float
    {
        $seatTotal = array_sum($seatPrices);
        
        $comboTotal = 0;
        foreach ($comboItems as $item) {
            $combo = Combo::find($item['combo_id']);
            if ($combo && $combo->is_active) {
                $qty = $item['qty'] ?? 1;
                $comboTotal += $combo->price * $qty;
            }
        }
        
        return round($seatTotal + $comboTotal, 2);
    }

    public function calculateDiscount(float $subtotal, $discountRules = []): float
    {
        $discount = 0;
        
        foreach ($discountRules as $rule) {
            if (isset($rule['type']) && isset($rule['value'])) {
                if ($rule['type'] === 'percentage') {
                    $discount += $subtotal * ($rule['value'] / 100);
                } elseif ($rule['type'] === 'fixed') {
                    $discount += $rule['value'];
                }
            }
        }
        
        return round(min($discount, $subtotal), 2);
    }

    public function calculateTotal(float $subtotal, float $discount): float
    {
        return round(max(0, $subtotal - $discount), 2);
    }
}

