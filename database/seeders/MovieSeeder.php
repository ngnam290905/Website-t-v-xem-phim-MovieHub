<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = [
            [
                'ten_phim' => 'Hành Tinh Bí Ẩn',
                'do_dai' => 128,
                'poster' => 'https://image.tmdb.org/t/p/w342/2CAL2433ZeIihfX1Hb2139CX0pW.jpg',
                'mo_ta' => 'Cuộc phiêu lưu vũ trụ đầy kịch tính với những hiệu ứng hình ảnh tuyệt đẹp và cốt truyện hấp dẫn. Một nhóm phi hành gia dũng cảm khám phá những bí mật của vũ trụ và tìm kiếm hành tinh mới cho nhân loại.',
                'dao_dien' => 'Christopher Nolan',
                'dien_vien' => 'Matthew McConaughey, Anne Hathaway, Jessica Chastain, Michael Caine',
                'trailer' => 'https://www.youtube.com/watch?v=example1',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Săn Lùng Siêu Trộm',
                'do_dai' => 115,
                'poster' => 'https://image.tmdb.org/t/p/w342/62HCnUTziyWcpDaBO2i1DX17ljH.jpg',
                'mo_ta' => 'Cuộc truy đuổi gay cấn giữa cảnh sát và tên trộm thông minh nhất thế giới. Một tài xế taxi bình thường bị cuốn vào cuộc phiêu lưu đầy nguy hiểm.',
                'dao_dien' => 'Michael Mann',
                'dien_vien' => 'Tom Cruise, Jamie Foxx, Jada Pinkett Smith, Mark Ruffalo',
                'trailer' => 'https://www.youtube.com/watch?v=example2',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Vùng Đất Linh Hồn',
                'do_dai' => 102,
                'poster' => 'https://image.tmdb.org/t/p/w342/e1mjopzAS2KNsvpbpahQ1a6SkSn.jpg',
                'mo_ta' => 'Hành trình khám phá thế giới tâm linh đầy bí ẩn và kỳ diệu. Một câu chuyện về tình yêu vượt qua ranh giới giữa hai thế giới.',
                'dao_dien' => 'Guillermo del Toro',
                'dien_vien' => 'Sally Hawkins, Michael Shannon, Richard Jenkins, Octavia Spencer',
                'trailer' => 'https://www.youtube.com/watch?v=example3',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Cuộc Chiến Vũ Trụ',
                'do_dai' => 142,
                'poster' => 'https://image.tmdb.org/t/p/w342/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
                'mo_ta' => 'Trận chiến vũ trụ hoành tráng giữa các thế lực thiện và ác. Những siêu anh hùng đoàn kết để bảo vệ Trái Đất khỏi mối đe dọa từ vũ trụ.',
                'dao_dien' => 'Joss Whedon',
                'dien_vien' => 'Robert Downey Jr., Chris Evans, Mark Ruffalo, Chris Hemsworth',
                'trailer' => 'https://www.youtube.com/watch?v=example4',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Bí Mật Thời Gian',
                'do_dai' => 118,
                'poster' => 'https://image.tmdb.org/t/p/w342/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
                'mo_ta' => 'Câu chuyện về du hành thời gian và những hậu quả không lường trước. Một nhà khoa học phát minh ra cỗ máy thời gian và khám phá những bí mật của vũ trụ.',
                'dao_dien' => 'Christopher Nolan',
                'dien_vien' => 'Leonardo DiCaprio, Marion Cotillard, Tom Hardy, Cillian Murphy',
                'trailer' => 'https://www.youtube.com/watch?v=example5',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Hành Trình Tình Yêu',
                'do_dai' => 95,
                'poster' => 'https://image.tmdb.org/t/p/w342/6XYLiMxHAaCsoyrVo38LBWMw2p8.jpg',
                'mo_ta' => 'Câu chuyện tình yêu cảm động vượt qua mọi khó khăn của cuộc sống. Một cặp đôi trẻ gặp nhau trên chuyến tàu và trải qua 24 giờ đáng nhớ ở Vienna.',
                'dao_dien' => 'Richard Linklater',
                'dien_vien' => 'Ethan Hawke, Julie Delpy, Andrea Eckert, Hanno Pöschl',
                'trailer' => 'https://www.youtube.com/watch?v=example6',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Ma Trận Hành Động',
                'do_dai' => 136,
                'poster' => 'https://image.tmdb.org/t/p/w342/f89q3lefM2kSVVBDowNwQxIC7D9.jpg',
                'mo_ta' => 'Cuộc chiến giữa con người và máy móc trong thế giới ảo. Neo phải học cách sử dụng sức mạnh của mình để cứu nhân loại.',
                'dao_dien' => 'Lana Wachowski, Lilly Wachowski',
                'dien_vien' => 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss, Hugo Weaving',
                'trailer' => 'https://www.youtube.com/watch?v=example7',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Cuộc Sống Tuyệt Vời',
                'do_dai' => 130,
                'poster' => 'https://image.tmdb.org/t/p/w342/4u1vptE8aXuzEIAgH8Y8O7v6NLD.jpg',
                'mo_ta' => 'Câu chuyện về một người đàn ông muốn tự tử nhưng được một thiên thần cứu giúp. Anh ta được cho thấy cuộc sống sẽ như thế nào nếu anh ta chưa từng tồn tại.',
                'dao_dien' => 'Frank Capra',
                'dien_vien' => 'James Stewart, Donna Reed, Lionel Barrymore, Thomas Mitchell',
                'trailer' => 'https://www.youtube.com/watch?v=example8',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Vua Sư Tử',
                'do_dai' => 88,
                'poster' => 'https://image.tmdb.org/t/p/w342/sKCr78MXSLixwmZ8DyJLrpMsd15.jpg',
                'mo_ta' => 'Câu chuyện về chú sư tử con Simba học cách trở thành vua. Một bộ phim hoạt hình kinh điển với âm nhạc tuyệt vời.',
                'dao_dien' => 'Roger Allers, Rob Minkoff',
                'dien_vien' => 'Matthew Broderick, James Earl Jones, Jeremy Irons, Nathan Lane',
                'trailer' => 'https://www.youtube.com/watch?v=example9',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Titanic',
                'do_dai' => 194,
                'poster' => 'https://image.tmdb.org/t/p/w342/9xjZS2rlVxm8SFx8kPC3aIGCOYQ.jpg',
                'mo_ta' => 'Câu chuyện tình yêu cảm động giữa Jack và Rose trên con tàu Titanic huyền thoại. Một tác phẩm điện ảnh kinh điển với hiệu ứng đặc biệt tuyệt vời.',
                'dao_dien' => 'James Cameron',
                'dien_vien' => 'Leonardo DiCaprio, Kate Winslet, Billy Zane, Kathy Bates',
                'trailer' => 'https://www.youtube.com/watch?v=example10',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Forrest Gump',
                'do_dai' => 142,
                'poster' => 'https://image.tmdb.org/t/p/w342/arw2vcBveWOVZr6pxd9XTd1TdQa.jpg',
                'mo_ta' => 'Câu chuyện về cuộc đời của Forrest Gump, một người đàn ông có IQ thấp nhưng có trái tim vàng. Anh ta đã chứng kiến và tham gia vào nhiều sự kiện lịch sử quan trọng của nước Mỹ.',
                'dao_dien' => 'Robert Zemeckis',
                'dien_vien' => 'Tom Hanks, Robin Wright, Gary Sinise, Sally Field',
                'trailer' => 'https://www.youtube.com/watch?v=example11',
                'trang_thai' => 1
            ],
            [
                'ten_phim' => 'Pulp Fiction',
                'do_dai' => 154,
                'poster' => 'https://image.tmdb.org/t/p/w342/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg',
                'mo_ta' => 'Một bộ phim tội phạm với cốt truyện phi tuyến tính, kể về cuộc sống của những tên tội phạm ở Los Angeles. Một kiệt tác của điện ảnh thế giới.',
                'dao_dien' => 'Quentin Tarantino',
                'dien_vien' => 'John Travolta, Samuel L. Jackson, Uma Thurman, Bruce Willis',
                'trailer' => 'https://www.youtube.com/watch?v=example12',
                'trang_thai' => 1
            ]
        ];

        foreach ($movies as $movie) {
            Movie::create(array_merge($movie, [
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()
            ]));
        }
    }
}
