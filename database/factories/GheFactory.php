<?php

namespace Database\Factories;

use App\Models\Ghe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ghe>
 */
class GheFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Ghe::class;
    public function definition(): array
    {
        return [
            'id_phong' => 1,  // Giả sử phòng ID=1
            'so_ghe' => $this->faker->numberBetween(1, 100),  // Số ghế
            'loai_ghe' => 'thuong',  // Hoặc enum: thuong/vip
            'trang_thai' => 1,  // Trống
            'vi_tri' => $this->faker->randomElement(['A1', 'B2', 'C3']),
        ];
    }
}
