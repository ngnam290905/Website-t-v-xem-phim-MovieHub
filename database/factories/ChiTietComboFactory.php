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
            'id_dat_ve' => $this->faker->numberBetween(1, 10),
            'id_combo' => $this->faker->numberBetween(1, 5),
            'so_luong' => $this->faker->numberBetween(1, 3),
            'gia_ap_dung' => $this->faker->randomElement([45000, 50000, 60000, 70000]),
        ];
    }
}
