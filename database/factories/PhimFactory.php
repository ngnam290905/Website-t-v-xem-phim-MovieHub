<?php

namespace Database\Factories;

use App\Models\Phim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Phim>
 */
class PhimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Phim::class;
    public function definition(): array
    {
        return [
           'ten_phim' => $this->faker->sentence(3),  // Tên phim ngẫu nhiên
           'the_loai' => $this->faker->randomElement(['Hành động', 'Hài hước', 'Kinh dị', 'Lãng mạn']),
            'do_dai' => $this->faker->numberBetween(90, 180),  // Phút
            'mo_ta' => $this->faker->paragraph(),
            'poster' => $this->faker->imageUrl(300, 400),  // URL poster giả
            'trang_thai' => 1,  // Hoạt động
            
        ];
    }
}
