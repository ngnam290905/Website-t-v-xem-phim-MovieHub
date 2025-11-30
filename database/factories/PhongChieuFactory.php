<?php

namespace Database\Factories;

use App\Models\PhongChieu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PhongChieu>
 */
class PhongChieuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = PhongChieu::class;
    public function definition(): array
    {
        return [
            'ten_phong' => $this->faker->randomElement(['Phòng 1', 'Phòng 2', 'Phòng VIP']),
            'so_ghe' => $this->faker->numberBetween(50, 200),
            'loai_phong' => $this->faker->randomElement(['2D', '3D', 'IMAX']),
            'trang_thai' => 1,  // Hoạt động
            'mo_ta' => $this->faker->sentence(),
        ];
    }
}
