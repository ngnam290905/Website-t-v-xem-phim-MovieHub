<?php

namespace Database\Factories;

use App\Models\Combo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Combo>
 */
class ComboFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Combo::class;
    public function definition(): array
    {
        return [
            'ten_combo' => $this->faker->randomElement(['Popcorn + Nước', 'Bắp + Bia', 'Combo Gia Đình']),
            'gia' => $this->faker->numberBetween(50000, 200000),
            'trang_thai' => 1,  // Hoạt động
            'mo_ta' => $this->faker->sentence(),
        ];
    }
}
