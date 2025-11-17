<?php
namespace Database\Factories;

use App\Models\ChiTietCombo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChiTietComboFactory extends Factory
{
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
