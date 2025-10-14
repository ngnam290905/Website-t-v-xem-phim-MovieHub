<?php

namespace Database\Factories;

use App\Models\ThanhToan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ThanhToan>
 */
class ThanhToanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ThanhToan::class;
    public function definition(): array
    {
        return [
            'phuong_thuc' => $this->faker->randomElement(['Chuyển khoản', 'Thẻ tín dụng', 'Tiền mặt']),
            'so_tien' => $this->faker->numberBetween(100000, 500000),
            'trang_thai' => 1,  // Thành công
            'ghi_chu' => $this->faker->sentence(),
        ];
    }
}
