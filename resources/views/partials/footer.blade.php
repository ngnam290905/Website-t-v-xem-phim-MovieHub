<footer class="bg-[#1a1d24] border-t border-[#262833] mt-16">
  <div class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
      <!-- Company Info -->
      <div class="col-span-1 md:col-span-2 lg:col-span-2">
        <div class="flex items-center gap-3 mb-4">
          <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-12 w-12 object-contain rounded" loading="lazy" width="48" height="48">
          <span class="text-2xl font-bold text-white">MovieHub</span>
        </div>
        <p class="text-[#a6a6b0] text-sm leading-relaxed mb-4">
          Nền tảng đặt vé xem phim trực tuyến hàng đầu Việt Nam. 
          Trải nghiệm điện ảnh tuyệt vời với giá vé ưu đãi và dịch vụ chuyên nghiệp.
        </p>
        <p class="text-[#a6a6b0] text-sm leading-relaxed mb-4">
          <i class="fas fa-map-marker-alt text-[#F53003] mr-2"></i>
          Số nhà 12 Hòe Thị, phường Phương Canh, quận Nam Từ Liêm, Hà Nội
        </p>
        <p class="text-[#a6a6b0] text-sm mb-4">
          <i class="fas fa-phone text-[#F53003] mr-2"></i>
          Hotline: 1900 1234
        </p>
        <p class="text-[#a6a6b0] text-sm mb-6">
          <i class="fas fa-envelope text-[#F53003] mr-2"></i>
          Email: support@moviehub.vn
        </p>
        
        <!-- Social Media -->
        <div class="flex gap-4 mb-6">
          <a href="#" target="_blank" class="w-10 h-10 rounded-full bg-[#262833] hover:bg-[#F53003] text-white flex items-center justify-center transition-all duration-300">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" target="_blank" class="w-10 h-10 rounded-full bg-[#262833] hover:bg-[#F53003] text-white flex items-center justify-center transition-all duration-300">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="#" target="_blank" class="w-10 h-10 rounded-full bg-[#262833] hover:bg-[#F53003] text-white flex items-center justify-center transition-all duration-300">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="#" target="_blank" class="w-10 h-10 rounded-full bg-[#262833] hover:bg-[#F53003] text-white flex items-center justify-center transition-all duration-300">
            <i class="fab fa-youtube"></i>
          </a>
          <a href="#" target="_blank" class="w-10 h-10 rounded-full bg-[#262833] hover:bg-[#F53003] text-white flex items-center justify-center transition-all duration-300">
            <i class="fab fa-tiktok"></i>
          </a>
        </div>

        <!-- Partner Logos -->
        <div class="mt-6">
          <p class="text-[#a6a6b0] text-xs mb-3">Đối tác thanh toán</p>
          <div class="flex items-center gap-3">
            <div class="px-3 py-2 bg-white/5 rounded border border-[#262833] text-xs text-[#a6a6b0]">VNPay</div>
            <div class="px-3 py-2 bg-white/5 rounded border border-[#262833] text-xs text-[#a6a6b0]">Momo</div>
            <div class="px-3 py-2 bg-white/5 rounded border border-[#262833] text-xs text-[#a6a6b0]">ZaloPay</div>
          </div>
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
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm flex items-center gap-2"><i class="fas fa-question-circle text-xs"></i> Trung tâm trợ giúp</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm flex items-center gap-2"><i class="fas fa-phone text-xs"></i> Liên hệ</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm flex items-center gap-2"><i class="fas fa-file-contract text-xs"></i> Điều khoản sử dụng</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm flex items-center gap-2"><i class="fas fa-shield-alt text-xs"></i> Chính sách bảo mật</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm flex items-center gap-2"><i class="fas fa-ticket-alt text-xs"></i> Chính sách đặt vé</a></li>
        </ul>
      </div>

      <!-- Legal & Policies -->
      <div>
        <h3 class="text-white font-semibold mb-4">Pháp lý</h3>
        <ul class="space-y-2">
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Điều khoản dịch vụ</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Chính sách hoàn tiền</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Quy định đổi vé</a></li>
          <li><a href="#" class="text-[#a6a6b0] hover:text-[#F53003] transition-colors duration-300 text-sm">Chính sách cookie</a></li>
        </ul>
      </div>
    </div>
    
    <div class="border-t border-[#262833] mt-8 pt-8">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-[#a6a6b0] text-sm text-center md:text-left">
          © {{ date('Y') }} MovieHub Cinema. Tất cả quyền được bảo lưu.
        </p>
        <div class="flex items-center gap-4 text-sm text-[#a6a6b0]">
          <span>Giấy phép: 123456789</span>
          <span>•</span>
          <span>Mã số thuế: 0123456789</span>
        </div>
      </div>
    </div>
  </div>
</footer>
