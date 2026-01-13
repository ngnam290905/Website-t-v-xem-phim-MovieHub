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
                'tieu_de' => 'Top 10 phim hay nhất năm 2025',
                'tom_tat' => 'Danh sách những bộ phim đáng xem nhất trong năm 2024, được đánh giá cao bởi cả giới chuyên môn và khán giả.',
                'noi_dung' => '<p>Năm 2025 đã mang đến cho chúng ta nhiều tác phẩm điện ảnh xuất sắc. Từ những bộ phim hành động đầy kịch tính đến những câu chuyện tình cảm sâu sắc, danh sách này sẽ giúp bạn không bỏ lỡ những bộ phim hay nhất.</p><p>Mỗi bộ phim đều có những điểm nổi bật riêng, từ diễn xuất của diễn viên đến kỹ xảo hình ảnh và cốt truyện hấp dẫn. Các tác phẩm như "Dune: Part Two", "Oppenheimer" và "Spider-Man: Across the Spider-Verse" đã tạo nên những dấu ấn đặc biệt trong lòng khán giả.</p><p>Không chỉ dừng lại ở giải trí, những bộ phim này còn mang đến những thông điệp ý nghĩa về cuộc sống, tình yêu và nhân sinh quan sâu sắc.</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1200&q=80',
                'tac_gia' => 'MovieHub Editorial',
                'the_loai' => 'Đánh giá phim',
                'noi_bat' => true,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(2),
            ],
            [
                'tieu_de' => 'Hướng dẫn đặt vé online tại MovieHub',
                'tom_tat' => 'Cách đặt vé xem phim trực tuyến nhanh chóng và tiện lợi tại MovieHub chỉ với vài bước đơn giản.',
                'noi_dung' => '<p>Đặt vé online tại MovieHub rất đơn giản và nhanh chóng. Bạn chỉ cần thực hiện các bước sau:</p><ol><li><strong>Chọn phim:</strong> Duyệt qua danh sách phim đang chiếu và chọn bộ phim bạn muốn xem</li><li><strong>Chọn ngày và suất chiếu:</strong> Xem lịch chiếu và chọn thời gian phù hợp với lịch trình của bạn</li><li><strong>Chọn ghế ngồi:</strong> Chọn vị trí ghế ưa thích trên sơ đồ phòng chiếu</li><li><strong>Chọn combo (tùy chọn):</strong> Thêm bắp nước hoặc snack để trải nghiệm hoàn hảo hơn</li><li><strong>Thanh toán:</strong> Chọn phương thức thanh toán và hoàn tất đơn hàng</li><li><strong>Nhận vé:</strong> Vé sẽ được gửi qua email và có thể xem trên điện thoại</li></ol><p>Với hệ thống đặt vé online hiện đại, bạn có thể đặt vé mọi lúc mọi nơi mà không cần đến rạp.</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=1200&q=80',
                'tac_gia' => 'MovieHub Support',
                'the_loai' => 'Hướng dẫn',
                'noi_bat' => true,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(5),
            ],
            [
                'tieu_de' => 'Combo bắp nước mới - Ưu đãi đặc biệt',
                'tom_tat' => 'MovieHub giới thiệu các combo bắp nước mới với giá ưu đãi đặc biệt, tiết kiệm lên đến 30%.',
                'noi_dung' => '<p>Chúng tôi vui mừng giới thiệu các combo bắp nước mới với nhiều lựa chọn hấp dẫn:</p><ul><li><strong>Combo Đôi:</strong> 1 Bắp lớn + 2 Nước - Giảm 15% - Phù hợp cho 2 người</li><li><strong>Combo Gia Đình:</strong> 2 Bắp lớn + 4 Nước - Giảm 20% - Lý tưởng cho gia đình 4 người</li><li><strong>Combo VIP:</strong> Bao gồm snack mix, bắp và nước cao cấp - Giảm 30% - Trải nghiệm cao cấp</li><li><strong>Combo Student:</strong> Bắp vừa + 1 Nước - Giảm 25% dành cho học sinh, sinh viên</li></ul><p>Ưu đãi có hiệu lực đến hết tháng này. Đặt vé ngay để không bỏ lỡ cơ hội tiết kiệm!</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1512149177596-f817c7ef5d4c?w=1200&q=80',
                'tac_gia' => 'MovieHub Marketing',
                'the_loai' => 'Khuyến mãi',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(1),
            ],
            [
                'tieu_de' => 'Lịch chiếu phim tuần này - Những bộ phim hot nhất',
                'tom_tat' => 'Cập nhật lịch chiếu các bộ phim hot nhất tuần này tại MovieHub với nhiều suất chiếu đa dạng từ sáng đến tối.',
                'noi_dung' => '<p>Tuần này chúng tôi có nhiều bộ phim hấp dẫn đang chiếu với lịch chiếu linh hoạt:</p><p><strong>Phim đang hot:</strong></p><ul><li>Đêm Tối Và Ánh Sáng - Suất chiếu: 10:00, 13:00, 16:00, 19:00, 22:00</li><li>Người Hùng Cứu Thế - Suất chiếu: 11:00, 14:00, 17:00, 20:00</li><li>Hành Trình Về Phương Đông - Suất chiếu: 09:00, 12:00, 15:00, 18:00, 21:00</li><li>Siêu Nhân 2024 - Suất chiếu: 10:30, 13:30, 16:30, 19:30, 22:30</li></ul><p>Với nhiều suất chiếu từ sáng đến tối, bạn có thể dễ dàng chọn thời gian phù hợp để xem phim yêu thích. Đặt vé sớm để có ghế ngồi đẹp!</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1200&q=80',
                'tac_gia' => 'MovieHub Admin',
                'the_loai' => 'Thông báo',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(3),
            ],
            [
                'tieu_de' => 'Công nghệ IMAX - Trải nghiệm xem phim đỉnh cao',
                'tom_tat' => 'Tìm hiểu về công nghệ IMAX và những ưu điểm vượt trội khi xem phim tại phòng chiếu IMAX.',
                'noi_dung' => '<p>IMAX là công nghệ chiếu phim tiên tiến với màn hình lớn và âm thanh vòm sống động. Khi xem phim tại phòng IMAX, bạn sẽ được trải nghiệm:</p><ul><li><strong>Hình ảnh sắc nét:</strong> Độ phân giải cao gấp 10 lần so với phòng chiếu thông thường</li><li><strong>Âm thanh vòm 7.1:</strong> Hệ thống âm thanh vòm sống động, mang đến trải nghiệm chân thực như đang ở trong phim</li><li><strong>Ghế ngồi thoải mái:</strong> Thiết kế góc nhìn tối ưu, không gian rộng rãi</li><li><strong>Màn hình cong:</strong> Tạo hiệu ứng bao quanh, thu hút toàn bộ tầm nhìn</li></ul><p>IMAX đặc biệt phù hợp cho các bộ phim hành động, khoa học viễn tưởng và phim tài liệu. Trải nghiệm IMAX tại MovieHub ngay hôm nay!</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=1200&q=80',
                'tac_gia' => 'MovieHub Tech',
                'the_loai' => 'Công nghệ',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(7),
            ],
            [
                'tieu_de' => 'Chương trình thành viên MovieHub - Tích điểm đổi quà',
                'tom_tat' => 'Tham gia chương trình thành viên để tích điểm mỗi lần đặt vé và đổi lấy nhiều ưu đãi hấp dẫn.',
                'noi_dung' => '<p>Chương trình thành viên MovieHub mang đến nhiều lợi ích đặc biệt:</p><ul><li><strong>Tích điểm tự động:</strong> Mỗi lần đặt vé, bạn sẽ tích điểm tương ứng với giá trị đơn hàng</li><li><strong>Đổi điểm lấy vé miễn phí:</strong> Sử dụng điểm tích lũy để đổi vé xem phim miễn phí</li><li><strong>Giảm giá đặc biệt:</strong> Thành viên được hưởng mức giảm giá ưu đãi cho combo và snack</li><li><strong>Ưu tiên đặt vé:</strong> Được ưu tiên đặt vé cho các suất chiếu hot, phim bom tấn</li><li><strong>Thông báo sớm:</strong> Nhận thông tin về phim mới và ưu đãi trước người khác</li><li><strong>Sinh nhật đặc biệt:</strong> Nhận voucher đặc biệt vào ngày sinh nhật</li></ul><p>Đăng ký thành viên hoàn toàn miễn phí ngay hôm nay!</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=1200&q=80',
                'tac_gia' => 'MovieHub Loyalty',
                'the_loai' => 'Khuyến mãi',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(10),
            ],
            [
                'tieu_de' => 'Những điều cần biết về phòng chiếu VIP tại MovieHub',
                'tom_tat' => 'Khám phá không gian sang trọng và tiện nghi tại phòng chiếu VIP với ghế massage và dịch vụ cao cấp.',
                'noi_dung' => '<p>Phòng chiếu VIP tại MovieHub mang đến trải nghiệm xem phim cao cấp nhất:</p><ul><li><strong>Ghế massage cao cấp:</strong> Ghế có chức năng massage tự động, điều chỉnh nhiệt độ và độ nghiêng</li><li><strong>Không gian riêng tư:</strong> Khoảng cách giữa các ghế rộng rãi, đảm bảo không gian cá nhân</li><li><strong>Dịch vụ ăn uống tại chỗ:</strong> Gọi món trực tiếp tại ghế, phục vụ tận nơi</li><li><strong>Chăn và gối cao cấp:</strong> Trải nghiệm thoải mái như ở nhà</li><li><strong>Màn hình và âm thanh cao cấp:</strong> Chất lượng hình ảnh và âm thanh tối ưu</li></ul><p>Phòng VIP phù hợp cho các dịp đặc biệt, hẹn hò hoặc muốn thưởng thức phim trong không gian riêng tư và sang trọng.</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200&q=80',
                'tac_gia' => 'MovieHub Editorial',
                'the_loai' => 'Dịch vụ',
                'noi_bat' => true,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(4),
            ],
            [
                'tieu_de' => 'Xu hướng điện ảnh năm 2024: Từ AI đến siêu anh hùng',
                'tom_tat' => 'Khám phá các xu hướng điện ảnh đang thịnh hành năm 2024, từ công nghệ AI đến các bộ phim siêu anh hùng.',
                'noi_dung' => '<p>Năm 2024 chứng kiến nhiều xu hướng điện ảnh thú vị:</p><p><strong>1. Công nghệ AI trong sản xuất phim:</strong> Nhiều studio bắt đầu sử dụng AI trong quá trình sản xuất, từ tạo hiệu ứng đến viết kịch bản.</p><p><strong>2. Phim siêu anh hùng tiếp tục thống trị:</strong> Các vũ trụ điện ảnh như Marvel và DC tiếp tục mở rộng với nhiều tác phẩm mới.</p><p><strong>3. Phim độc lập và phim quốc tế:</strong> Khán giả quan tâm nhiều hơn đến các tác phẩm độc lập và phim từ các quốc gia khác nhau.</p><p><strong>4. Hồi sinh các franchise cổ điển:</strong> Nhiều bộ phim cổ điển được làm lại hoặc có phần tiếp theo.</p><p><strong>5. Trải nghiệm xem phim tại nhà vs rạp:</strong> Sự cân bằng mới giữa streaming và trải nghiệm tại rạp chiếu.</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1579373903781-fd5c0c30c4cd?w=1200&q=80',
                'tac_gia' => 'MovieHub Editorial',
                'the_loai' => 'Đánh giá phim',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(6),
            ],
            [
                'tieu_de' => 'Tối ưu hóa trải nghiệm xem phim 3D tại rạp',
                'tom_tat' => 'Bí quyết để có trải nghiệm xem phim 3D tốt nhất, từ việc chọn ghế ngồi đến cách đeo kính 3D đúng cách.',
                'noi_dung' => '<p>Để có trải nghiệm xem phim 3D tốt nhất, bạn nên:</p><ul><li><strong>Chọn ghế ngồi phù hợp:</strong> Vị trí tốt nhất là ở giữa phòng chiếu, cách màn hình khoảng 2/3 chiều dài phòng</li><li><strong>Đeo kính 3D đúng cách:</strong> Đảm bảo kính vừa vặn, không bị lệch và sạch sẽ</li><li><strong>Cho mắt nghỉ ngơi:</strong> Nếu cảm thấy mỏi mắt, hãy nhắm mắt vài giây hoặc tạm thời tháo kính</li><li><strong>Giữ khoảng cách phù hợp:</strong> Không ngồi quá gần màn hình để tránh căng thẳng cho mắt</li><li><strong>Điều chỉnh kính nếu cần:</strong> Nếu hình ảnh bị mờ hoặc nhòe, hãy điều chỉnh kính hoặc đổi kính khác</li></ul><p>Với những mẹo này, bạn sẽ có trải nghiệm xem phim 3D tuyệt vời nhất!</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1535016120720-40c646be5580?w=1200&q=80',
                'tac_gia' => 'MovieHub Support',
                'the_loai' => 'Hướng dẫn',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(8),
            ],
            [
                'tieu_de' => 'Phim Việt Nam 2024: Những tác phẩm đáng chờ đợi',
                'tom_tat' => 'Tổng hợp những bộ phim Việt Nam sắp ra mắt trong năm 2024, từ phim tình cảm đến hành động.',
                'noi_dung' => '<p>Năm 2024 hứa hẹn mang đến nhiều tác phẩm điện ảnh Việt Nam đáng chờ đợi:</p><p><strong>Phim tình cảm:</strong> Các bộ phim tình cảm lãng mạn với cốt truyện mới mẻ, diễn xuất tự nhiên của các diễn viên trẻ.</p><p><strong>Phim hành động:</strong> Những bộ phim hành động với kỹ xảo được đầu tư, mang đến trải nghiệm điện ảnh hoành tráng.</p><p><strong>Phim hài:</strong> Các tác phẩm hài hước, giải trí phù hợp cho cả gia đình.</p><p><strong>Phim tâm lý - xã hội:</strong> Những câu chuyện về cuộc sống, xã hội với góc nhìn sâu sắc.</p><p>Điện ảnh Việt Nam đang trên đà phát triển mạnh mẽ, đặc biệt là về mặt kỹ thuật và kể chuyện. Hãy ủng hộ phim Việt!</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=1200&q=80',
                'tac_gia' => 'MovieHub Editorial',
                'the_loai' => 'Đánh giá phim',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(9),
            ],
            [
                'tieu_de' => 'Quy tắc ứng xử tại rạp chiếu phim',
                'tom_tat' => 'Những quy tắc cần tuân thủ khi xem phim tại rạp để có trải nghiệm tốt nhất cho bản thân và người xung quanh.',
                'noi_dung' => '<p>Để mọi người đều có trải nghiệm xem phim tốt nhất, hãy tuân thủ những quy tắc sau:</p><ul><li><strong>Tắt điện thoại:</strong> Tắt chuông và đặt chế độ im lặng, không nhắn tin hoặc gọi điện trong phòng chiếu</li><li><strong>Đến đúng giờ:</strong> Đến sớm 10-15 phút để tìm ghế và chuẩn bị, không làm phiền người khác</li><li><strong>Giữ im lặng:</strong> Không nói chuyện to, bình luận về phim trong khi đang chiếu</li><li><strong>Không quay phim/chụp ảnh:</strong> Tôn trọng bản quyền và không làm phân tâm người xem</li><li><strong>Vứt rác đúng nơi:</strong> Mang rác ra ngoài hoặc để trong thùng rác được đặt sẵn</li><li><strong>Chăm sóc trẻ em:</strong> Đảm bảo trẻ em ngồi yên, không chạy nhảy hoặc làm phiền người khác</li></ul><p>Hãy cùng tạo môi trường văn minh tại rạp chiếu phim!</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1464207687429-7505649dae38?w=1200&q=80',
                'tac_gia' => 'MovieHub Admin',
                'the_loai' => 'Thông báo',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(11),
            ],
            [
                'tieu_de' => 'Công nghệ Dolby Atmos tại MovieHub - Âm thanh vòm sống động',
                'tom_tat' => 'Tìm hiểu về công nghệ âm thanh Dolby Atmos tiên tiến, mang đến trải nghiệm nghe nhìn sống động như thật.',
                'noi_dung' => '<p>Dolby Atmos là công nghệ âm thanh vòm tiên tiến được trang bị tại các phòng chiếu cao cấp của MovieHub:</p><ul><li><strong>Âm thanh đa hướng:</strong> Hệ thống loa được bố trí khắp phòng, bao gồm cả trần nhà, tạo âm thanh bao quanh 360 độ</li><li><strong>Độ chính xác cao:</strong> Mỗi âm thanh được định vị chính xác trong không gian 3D</li><li><strong>Trải nghiệm sống động:</strong> Cảm giác như đang ở ngay trong khung cảnh của phim</li><li><strong>Tương thích tốt:</strong> Tự động điều chỉnh để phù hợp với từng phòng chiếu</li></ul><p>Dolby Atmos đặc biệt ấn tượng với các bộ phim hành động, khoa học viễn tưởng và phim nhạc kịch. Mỗi tiếng động đều được tái tạo một cách chân thực và sống động.</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?w=1200&q=80',
                'tac_gia' => 'MovieHub Tech',
                'the_loai' => 'Công nghệ',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(12),
            ],
            [
                'tieu_de' => 'Ưu đãi đặc biệt cuối tuần - Giảm 20% cho tất cả suất chiếu',
                'tom_tat' => 'Cuối tuần này, MovieHub áp dụng ưu đãi giảm 20% cho tất cả các suất chiếu từ thứ 6 đến chủ nhật.',
                'noi_dung' => '<p>Ưu đãi đặc biệt cuối tuần dành cho tất cả khách hàng:</p><ul><li><strong>Giảm 20%:</strong> Áp dụng cho tất cả vé xem phim các suất chiếu từ thứ 6 đến chủ nhật</li><li><strong>Không giới hạn:</strong> Áp dụng cho tất cả phim đang chiếu, không loại trừ</li><li><strong>Combo kèm theo:</strong> Giảm thêm 15% khi mua combo bắp nước</li><li><strong>Đặt vé online:</strong> Ưu đãi chỉ áp dụng khi đặt vé trước qua website hoặc app</li></ul><p>Ưu đãi có hiệu lực từ 00:00 thứ 6 đến 23:59 chủ nhật hàng tuần. Đặt vé ngay để không bỏ lỡ cơ hội tiết kiệm!</p><p><em>* Ưu đãi không áp dụng đồng thời với các chương trình khuyến mãi khác.</em></p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1478720568477-1526209eb7d2?w=1200&q=80',
                'tac_gia' => 'MovieHub Marketing',
                'the_loai' => 'Khuyến mãi',
                'noi_bat' => true,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(13),
            ],
            [
                'tieu_de' => 'Lịch sử và sự phát triển của công nghệ chiếu phim',
                'tom_tat' => 'Hành trình từ những buổi chiếu phim đầu tiên đến công nghệ chiếu phim hiện đại ngày nay.',
                'noi_dung' => '<p>Lịch sử của công nghệ chiếu phim đã trải qua nhiều giai đoạn phát triển:</p><p><strong>1895 - Thời kỳ sơ khai:</strong> Anh em nhà Lumière trình chiếu bộ phim đầu tiên, mở ra kỷ nguyên điện ảnh.</p><p><strong>1927 - Phim có tiếng:</strong> "The Jazz Singer" trở thành bộ phim có tiếng đầu tiên, thay đổi hoàn toàn ngành công nghiệp điện ảnh.</p><p><strong>1935 - Phim màu:</strong> Công nghệ Technicolor mang đến những bộ phim màu đầu tiên.</p><p><strong>1950s - Màn hình rộng:</strong> CinemaScope và các công nghệ màn hình rộng ra đời để cạnh tranh với TV.</p><p><strong>1970s - Âm thanh vòm:</strong> Dolby Stereo và công nghệ âm thanh vòm xuất hiện.</p><p><strong>2000s - Kỹ thuật số:</strong> Chuyển đổi từ phim sang chiếu phim kỹ thuật số (DCP).</p><p><strong>Hiện tại:</strong> IMAX, Dolby Atmos, và các công nghệ 3D, 4D hiện đại.</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1200&q=80',
                'tac_gia' => 'MovieHub Editorial',
                'the_loai' => 'Công nghệ',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(14),
            ],
            [
                'tieu_de' => 'Cách chọn phim phù hợp cho từng độ tuổi',
                'tom_tat' => 'Hướng dẫn chọn phim phù hợp với độ tuổi để có trải nghiệm xem phim an toàn và phù hợp cho mọi thành viên trong gia đình.',
                'noi_dung' => '<p>Việc chọn phim phù hợp với độ tuổi rất quan trọng để đảm bảo trải nghiệm tốt và phù hợp:</p><ul><li><strong>Phim P (mọi lứa tuổi):</strong> Phù hợp cho trẻ em và cả gia đình, không có nội dung bạo lực hoặc không phù hợp</li><li><strong>Phim C13 (13+):</strong> Có thể có một số cảnh bạo lực nhẹ hoặc ngôn ngữ, phù hợp từ 13 tuổi trở lên</li><li><strong>Phim C16 (16+):</strong> Có nội dung bạo lực, kinh dị hoặc ngôn ngữ mạnh hơn, chỉ dành cho 16 tuổi trở lên</li><li><strong>Phim C18 (18+):</strong> Nội dung người lớn, bạo lực mạnh, chỉ dành cho 18 tuổi trở lên</li></ul><p>Luôn kiểm tra phân loại độ tuổi và đọc mô tả phim trước khi đưa trẻ em đi xem. Tại MovieHub, chúng tôi luôn hiển thị rõ ràng phân loại độ tuổi cho từng bộ phim.</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=1200&q=80',
                'tac_gia' => 'MovieHub Support',
                'the_loai' => 'Hướng dẫn',
                'noi_bat' => false,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(15),
            ],
            [
                'tieu_de' => 'MovieHub mở rạp chiếu mới tại trung tâm thành phố',
                'tom_tat' => 'Rạp chiếu phim MovieHub mới với 8 phòng chiếu hiện đại, trang bị công nghệ mới nhất tại trung tâm thành phố.',
                'noi_dung' => '<p>Chúng tôi vui mừng thông báo về việc mở rạp chiếu MovieHub mới tại trung tâm thành phố:</p><p><strong>Địa điểm:</strong> Tầng 3-4, Trung tâm Thương mại ABC, đường XYZ</p><p><strong>Quy mô:</strong> 8 phòng chiếu với tổng cộng 1,200 ghế ngồi</p><p><strong>Trang thiết bị:</strong></p><ul><li>2 phòng IMAX với công nghệ tiên tiến nhất</li><li>2 phòng VIP với ghế massage</li><li>4 phòng chiếu thường với màn hình LED và âm thanh Dolby Atmos</li></ul><p><strong>Tiện ích:</strong> Khu vực ăn uống rộng rãi, khu vui chơi cho trẻ em, bãi đỗ xe miễn phí</p><p><strong>Khai trương:</strong> Ngày 15 tháng này với nhiều ưu đãi đặc biệt cho 100 khách hàng đầu tiên.</p><p>Hẹn gặp bạn tại rạp chiếu mới của chúng tôi!</p>',
                'hinh_anh' => 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=1200&q=80',
                'tac_gia' => 'MovieHub Admin',
                'the_loai' => 'Thông báo',
                'noi_bat' => true,
                'trang_thai' => true,
                'ngay_dang' => now()->subDays(16),
            ],
        ];

        foreach ($news as $item) {
            // Tạo slug nếu chưa có
            if (!isset($item['slug'])) {
                $item['slug'] = \Illuminate\Support\Str::slug($item['tieu_de']);
            }
            
            // Kiểm tra xem bài viết đã tồn tại chưa (theo slug)
            $existing = TinTuc::where('slug', $item['slug'])->first();
            if (!$existing) {
                TinTuc::create($item);
            }
        }
    }
}

