<?php

namespace Database\Factories;

use App\Models\ChiTietCombo;
use App\Models\Combo;
use App\Models\DatVe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChiTietCombo>
 */
class ChiTietComboFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ChiTietCombo::class;
    public function definition(): array
    {
        return [
            'id_dat_ve' => DatVe::factory(),  // Link với booking
            'id_combo' => Combo::factory(),   // Link với combo
            'so_luong' => $this->faker->numberBetween(1, 5),  // Số lượng
            'gia' => $this->faker->numberBetween(50000, 150000),
        ];
    }
}
