@extends('layouts.main')

@section('title', 'Giới Thiệu – MovieHub')

@section('content')
  <section class="bg-gradient-to-b from-[#0d0f14] to-[#121521] py-12 md:py-16 border-b border-[#262833]">
    <div class="max-w-5xl mx-auto px-4">
      <h1 class="text-3xl md:text-4xl font-extrabold mb-4 gradient-text">Giới Thiệu – MovieHub</h1>
      <p class="text-[#a6a6b0] text-base md:text-lg">MovieHub là nền tảng đặt vé xem phim trực tuyến giúp người dùng trải nghiệm điện ảnh một cách nhanh chóng, tiện lợi và hiện đại. Với giao diện thân thiện, tốc độ xử lý nhanh và hệ thống rạp liên kết rộng rãi, MovieHub mang đến cho bạn cách đặt vé chủ động – mọi lúc, mọi nơi.</p>
    </div>
  </section>

  <section class="py-10 md:py-14">
    <div class="max-w-5xl mx-auto px-4 space-y-10">
      <div>
        <h2 class="text-2xl md:text-3xl font-bold mb-3">Sứ mệnh của chúng tôi</h2>
        <ul class="list-disc pl-6 space-y-2 text-[#c8c8d0]">
          <li>Mang đến trải nghiệm đặt vé đơn giản, thuận tiện và minh bạch.</li>
          <li>Cung cấp thông tin phim đầy đủ – chính xác – cập nhật liên tục.</li>
          <li>Giúp người dùng tiết kiệm thời gian khi mua vé và chọn chỗ trước.</li>
          <li>Tạo nên một hệ sinh thái giải trí nơi mọi người có thể dễ dàng khám phá và thưởng thức những bộ phim yêu thích.</li>
        </ul>
      </div>

      <div class="space-y-6">
        <h2 class="text-2xl md:text-3xl font-bold">Điểm nổi bật của MovieHub</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="p-5 rounded-xl border border-[#262833] bg-[#11131a]">
            <div class="text-2xl mb-2">🎬</div>
            <h3 class="text-xl font-semibold mb-2">Danh sách phim đa dạng</h3>
            <p class="text-[#c8c8d0]">Từ bom tấn Hollywood, anime, phim Việt Nam đến các dòng phim nghệ thuật – tất cả đều được cập nhật nhanh chóng với trailer, mô tả, đánh giá và lịch chiếu đầy đủ.</p>
          </div>
          <div class="p-5 rounded-xl border border-[#262833] bg-[#11131a]">
            <div class="text-2xl mb-2">🏙️</div>
            <h3 class="text-xl font-semibold mb-2">Hệ thống rạp rộng khắp</h3>
            <p class="text-[#c8c8d0]">MovieHub hỗ trợ nhiều cụm rạp lớn, mang đến nhiều lựa chọn phòng chiếu, thời gian chiếu, định dạng (2D/3D/IMAX).</p>
          </div>
          <div class="p-5 rounded-xl border border-[#262833] bg-[#11131a]">
            <div class="text-2xl mb-2">🪑</div>
            <h3 class="text-xl font-semibold mb-2">Đặt vé và chọn ghế trực quan</h3>
            <p class="text-[#c8c8d0]">Giao diện sơ đồ ghế được thiết kế rõ ràng, giúp khách hàng xem tình trạng ghế trống theo thời gian thực và chọn vị trí ưng ý chỉ trong vài giây.</p>
          </div>
          <div class="p-5 rounded-xl border border-[#262833] bg-[#11131a]">
            <div class="text-2xl mb-2">💳</div>
            <h3 class="text-xl font-semibold mb-2">Thanh toán dễ dàng – bảo mật cao</h3>
            <p class="text-[#c8c8d0]">Hỗ trợ nhiều phương thức thanh toán nhanh chóng như VNPAY, Ví điện tử, ATM, thẻ quốc tế… đảm bảo an toàn tuyệt đối.</p>
          </div>
          <div class="p-5 rounded-xl border border-[#262833] bg-[#11131a] md:col-span-2">
            <div class="text-2xl mb-2">⭐</div>
            <h3 class="text-xl font-semibold mb-2">Khuyến mãi hấp dẫn</h3>
            <p class="text-[#c8c8d0]">MovieHub thường xuyên cập nhật các chương trình giảm giá, combo bắp nước, voucher ưu đãi cho thành viên.</p>
          </div>
        </div>
      </div>

      <div>
        <h2 class="text-2xl md:text-3xl font-bold mb-3">Tầm nhìn</h2>
        <p class="text-[#c8c8d0]">MovieHub hướng đến trở thành nền tảng đặt vé xem phim hàng đầu Việt Nam, không chỉ là nơi đặt vé mà còn là cộng đồng yêu điện ảnh – nơi mọi người có thể chia sẻ cảm xúc, đánh giá và cập nhật xu hướng phim mới nhất.</p>
      </div>

      <div>
        <h2 class="text-2xl md:text-3xl font-bold mb-3">Cam kết của chúng tôi</h2>
        <ul class="list-disc pl-6 space-y-2 text-[#c8c8d0]">
          <li>Thông tin rõ ràng – giá vé minh bạch</li>
          <li>Hỗ trợ khách hàng nhanh chóng (qua email, hotline và mạng xã hội)</li>
          <li>Luôn đổi mới để mang lại trải nghiệm tốt hơn mỗi ngày</li>
        </ul>
      </div>

      <div class="pt-2">
        <p class="text-[#c8c8d0] italic">MovieHub – Trải nghiệm điện ảnh trong tầm tay bạn.</p>
      </div>
    </div>
  </section>
@endsection
