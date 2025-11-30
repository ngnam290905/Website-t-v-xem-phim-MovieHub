<?php

namespace Database\Seeders;

use App\Models\TinTuc;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TinTucSeeder extends Seeder
{
    public function run(): void
    {
        $news = [
            [
                'tieu_de' => 'Top 10 phim hay nhất năm 2024',
                'tom_tat' => 'Danh sách những bộ phim đáng xem nhất trong năm 2024, được đánh giá cao bởi cả giới chuyên môn và khán giả.',
                'noi_dung' => '<p>Năm 2024 đã mang đến cho chúng ta nhiều tác phẩm điện ảnh xuất sắc. Từ những bộ phim hành động đầy kịch tính đến những câu chuyện tình cảm sâu sắc, danh sách này sẽ giúp bạn không bỏ lỡ những bộ phim hay nhất.</p><p>Mỗi bộ phim đều có những điểm nổi bật riêng, từ diễn xuất của diễn viên đến kỹ xảo hình ảnh và cốt truyện hấp dẫn.</p>',
                'tac_gia' => 'MovieHub Editorial',
                'the_loai' => 'Đánh giá phim',
                'noi_bat' => true,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(2),
            ],
            [
                'tieu_de' => 'Hướng dẫn đặt vé online tại MovieHub',
                'tom_tat' => 'Cách đặt vé xem phim trực tuyến nhanh chóng và tiện lợi tại MovieHub chỉ với vài bước đơn giản.',
                'noi_dung' => '<p>Đặt vé online tại MovieHub rất đơn giản và nhanh chóng. Bạn chỉ cần thực hiện các bước sau:</p><ol><li>Chọn phim bạn muốn xem</li><li>Chọn ngày và suất chiếu</li><li>Chọn ghế ngồi</li><li>Chọn combo (nếu muốn)</li><li>Thanh toán và nhận vé</li></ol><p>Vé sẽ được gửi qua email và có thể xem trên điện thoại.</p>',
                'tac_gia' => 'MovieHub Support',
                'the_loai' => 'Hướng dẫn',
                'noi_bat' => true,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(5),
            ],
            [
                'tieu_de' => 'Combo bắp nước mới - Ưu đãi đặc biệt',
                'tom_tat' => 'MovieHub giới thiệu các combo bắp nước mới với giá ưu đãi đặc biệt, tiết kiệm lên đến 30%.',
                'noi_dung' => '<p>Chúng tôi vui mừng giới thiệu các combo bắp nước mới với nhiều lựa chọn hấp dẫn:</p><ul><li>Combo Đôi: 1 Bắp lớn + 2 Nước - Giảm 15%</li><li>Combo Gia Đình: 2 Bắp lớn + 4 Nước - Giảm 20%</li><li>Combo VIP: Bao gồm snack mix - Giảm 30%</li></ul><p>Ưu đãi có hiệu lực đến hết tháng này.</p>',
                'tac_gia' => 'MovieHub Marketing',
                'the_loai' => 'Khuyến mãi',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(1),
            ],
            [
                'tieu_de' => 'Lịch chiếu phim tuần này',
                'tom_tat' => 'Cập nhật lịch chiếu các bộ phim hot nhất tuần này tại MovieHub với nhiều suất chiếu đa dạng.',
                'noi_dung' => '<p>Tuần này chúng tôi có nhiều bộ phim hấp dẫn đang chiếu:</p><p>Với nhiều suất chiếu từ sáng đến tối, bạn có thể dễ dàng chọn thời gian phù hợp để xem phim yêu thích.</p>',
                'tac_gia' => 'MovieHub Admin',
                'the_loai' => 'Thông báo',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(3),
            ],
            [
                'tieu_de' => 'Công nghệ IMAX - Trải nghiệm xem phim đỉnh cao',
                'tom_tat' => 'Tìm hiểu về công nghệ IMAX và những ưu điểm vượt trội khi xem phim tại phòng chiếu IMAX.',
                'noi_dung' => '<p>IMAX là công nghệ chiếu phim tiên tiến với màn hình lớn và âm thanh vòm sống động. Khi xem phim tại phòng IMAX, bạn sẽ được trải nghiệm:</p><ul><li>Hình ảnh sắc nét với độ phân giải cao</li><li>Âm thanh vòm 7.1 sống động</li><li>Ghế ngồi thoải mái với góc nhìn tối ưu</li></ul>',
                'tac_gia' => 'MovieHub Tech',
                'the_loai' => 'Công nghệ',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(7),
            ],
            [
                'tieu_de' => 'Chương trình thành viên MovieHub - Tích điểm đổi quà',
                'tom_tat' => 'Tham gia chương trình thành viên để tích điểm mỗi lần đặt vé và đổi lấy nhiều ưu đãi hấp dẫn.',
                'noi_dung' => '<p>Chương trình thành viên MovieHub mang đến nhiều lợi ích:</p><ul><li>Tích điểm mỗi lần đặt vé</li><li>Đổi điểm lấy vé miễn phí</li><li>Giảm giá đặc biệt cho thành viên</li><li>Ưu tiên đặt vé cho các suất chiếu hot</li></ul>',
                'tac_gia' => 'MovieHub Loyalty',
                'the_loai' => 'Khuyến mãi',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(10),
            ],
        ];

        foreach ($news as $item) {
            TinTuc::create($item);
        }
    }
}

