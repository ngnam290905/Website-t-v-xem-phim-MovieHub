<?php

namespace Database\Factories;

use App\Models\ChiTietDatVe;
use App\Models\DatVe;
use App\Models\Ghe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChiTietDatVe>
 */
class ChiTietDatVeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ChiTietDatVe::class;
    public function definition(): array
    {
        return [
            'id_dat_ve' => DatVe::factory(),  // Tạo hoặc link với booking
            'id_ghe' => Ghe::factory(),       // Tạo hoặc link với ghế
            'gia' => $this->faker->numberBetween(50000, 200000),
        ];
    }
}
