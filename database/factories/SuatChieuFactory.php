<?php

namespace Database\Factories;

use App\Models\SuatChieu;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SuatChieu>
 */
class SuatChieuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = SuatChieu::class;
    public function definition(): array
    {
        return [
            'id_phim' => 1,  // Giả sử phim ID=1 tồn tại
            'id_phong' => 1,  // Giả sử phòng ID=1 tồn tại
            'thoi_gian_bat_dau' => Carbon::now()->addHours(rand(2, 24)),  // Suất chiếu tương lai
            'trang_thai' => 1,  // Hoạt động
        ];
    }
}
