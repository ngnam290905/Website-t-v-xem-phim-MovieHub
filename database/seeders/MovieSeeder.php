<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Phim;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = [
            [
                'ten_phim' => 'Avengers: Endgame',
                'do_dai' => 181,
                'mo_ta' => 'Sau sự kiện tàn phá của Avengers: Infinity War, vũ trụ đang trong tình trạng đổ nát. Với sự giúp đỡ của các đồng minh còn lại, Avengers tập hợp lại một lần nữa để đảo ngược hành động của Thanos và khôi phục lại trật tự cho vũ trụ.',
                'dao_dien' => 'Anthony Russo, Joe Russo',
                'dien_vien' => 'Robert Downey Jr., Chris Evans, Mark Ruffalo, Chris Hemsworth, Scarlett Johansson',
                'trailer' => 'https://www.youtube.com/watch?v=TcMBFSGVi1c',
                'trang_thai' => 1,
            ],
            [
                'ten_phim' => 'Spider-Man: No Way Home',
                'do_dai' => 148,
                'mo_ta' => 'Với danh tính của Spider-Man đã bị tiết lộ, Peter Parker yêu cầu Doctor Strange giúp đỡ. Khi một phép thuật đi sai, Peter phải khám phá ý nghĩa thực sự của việc là Spider-Man.',
                'dao_dien' => 'Jon Watts',
                'dien_vien' => 'Tom Holland, Zendaya, Benedict Cumberbatch, Jacob Batalon',
                'trailer' => 'https://www.youtube.com/watch?v=JfVOs4VSpmA',
                'trang_thai' => 1,
            ],
            [
                'ten_phim' => 'Top Gun: Maverick',
                'do_dai' => 131,
                'mo_ta' => 'Sau hơn ba mươi năm phục vụ như một trong những phi công hàng đầu của Hải quân, Pete "Maverick" Mitchell vẫn ở đúng nơi anh thuộc về, đẩy ranh giới như một phi công thử nghiệm dũng cảm.',
                'dao_dien' => 'Joseph Kosinski',
                'dien_vien' => 'Tom Cruise, Miles Teller, Jennifer Connelly, Jon Hamm',
                'trailer' => 'https://www.youtube.com/watch?v=qSqVVswa420',
                'trang_thai' => 1,
            ],
            [
                'ten_phim' => 'The Batman',
                'do_dai' => 176,
                'mo_ta' => 'Khi một kẻ giết người nhắm vào giới thượng lưu của Gotham với một loạt các âm mưu tàn bạo, Batman phải điều tra lịch sử đen tối của thành phố và tự hỏi liệu gia đình mình có liên quan gì đến tất cả.',
                'dao_dien' => 'Matt Reeves',
                'dien_vien' => 'Robert Pattinson, Zoë Kravitz, Paul Dano, Jeffrey Wright',
                'trailer' => 'https://www.youtube.com/watch?v=mqqft2x_Aa4',
                'trang_thai' => 1,
            ],
            [
                'ten_phim' => 'Black Widow',
                'do_dai' => 134,
                'mo_ta' => 'Natasha Romanoff, còn được gọi là Black Widow, phải đối mặt với những phần đen tối nhất trong hồ sơ của cô khi một âm mưu nguy hiểm liên quan đến quá khứ của cô nổi lên.',
                'dao_dien' => 'Cate Shortland',
                'dien_vien' => 'Scarlett Johansson, Florence Pugh, David Harbour, Rachel Weisz',
                'trailer' => 'https://www.youtube.com/watch?v=ybji16u608U',
                'trang_thai' => 0,
            ],
        ];

        foreach ($movies as $movie) {
            Phim::create($movie);
        }
    }
}