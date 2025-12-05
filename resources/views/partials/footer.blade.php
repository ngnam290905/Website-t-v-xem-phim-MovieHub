<footer class="bg-[#151822] border-t border-[#262833] mt-16">
  <div class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <!-- Company Info -->
      <div class="col-span-1 md:col-span-2">
        <div class="flex items-center gap-3 mb-4">
          <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-10 w-10 object-contain rounded" loading="lazy" width="40" height="40">
          <span class="text-xl font-bold gradient-text">MovieHub</span>
        </div>
        <p class="text-[#a6a6b0] text-sm leading-relaxed mb-4">
          Nền tảng đặt vé xem phim trực tuyến hàng đầu Việt Nam. 
          Trải nghiệm điện ảnh tuyệt vời với giá vé ưu đãi và dịch vụ chuyên nghiệp.
        </p>
        <p class="text-[#a6a6b0] text-sm leading-relaxed mb-4">
          Số nhà 12 Hòe Thị phường Phương Canh quận Nam Từ Liêm Hà Nội
        </p>
        <div class="flex gap-4">
          <a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300">
            <i class="fab fa-facebook text-xl"></i>
          </a>
          <a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300">
            <i class="fab fa-twitter text-xl"></i>
          </a>
          <a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300">
            <i class="fab fa-instagram text-xl"></i>
          </a>
          <a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300">
            <i class="fab fa-youtube text-xl"></i>
          </a>
        </div>
      </div>
      
      <!-- Quick Links -->
      <div>
        <h3 class="text-white font-semibold mb-4">Liên kết nhanh</h3>
        <ul class="space-y-2">
          <li><a href="{{ route('home') }}" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Trang chủ</a></li>
          <li><a href="{{ route('movies.now-showing') }}" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Phim đang chiếu</a></li>
          <li><a href="{{ route('movies.coming-soon') }}" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Phim sắp chiếu</a></li>
          <li><a href="{{ route('movies.showtimes') }}" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Lịch chiếu</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Khuyến mãi</a></li>
          <li><a href="{{ route('about') }}" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Giới thiệu</a></li>
        </ul>
      </div>
      
      <!-- Support -->
      <div>
        <h3 class="text-white font-semibold mb-4">Hỗ trợ</h3>
        <ul class="space-y-2">
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Trung tâm trợ giúp</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Liên hệ</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Điều khoản sử dụng</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Chính sách bảo mật</a></li>
        </ul>
      </div>
    </div>
    
    <div class="border-t border-[#262833] mt-8 pt-8 text-center">
      <p class="text-[#a6a6b0] text-sm">
        
      </p>
    </div>
  </div>
</footer>
