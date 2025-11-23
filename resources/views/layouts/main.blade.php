<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MovieHub - Đặt vé xem phim')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('meta')
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css','resources/js/app.js'])
    @else
      <script src="https://cdn.tailwindcss.com" defer></script>
    @endif
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
      .hero-carousel {
        position: relative;
      }
      
      .hero-slide {
        transition: opacity 0.5s ease-in-out;
      }
      
      .hero-slide.active {
        opacity: 1;
      }
      
      .movie-card {
        transition: all 0.3s ease;
      }
      
      .movie-card:hover {
        transform: translateY(-8px);
      }
      
      .movie-overlay {
        transition: all 0.3s ease;
      }
      
      .movie-img {
        transition: transform 0.3s ease;
      }
      
      .movie-card:hover .movie-img {
        transform: scale(1.05);
      }
      
      .animate-fade-in {
        animation: fadeIn 0.5s ease-in-out;
      }
      
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }
      
      .animate-slide-up {
        animation: slideUp 0.6s ease-out;
      }
      
      @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
      }
      
      .animate-bounce-in {
        animation: bounceIn 0.8s ease-out;
      }
      
      @keyframes bounceIn {
        0% { opacity: 0; transform: scale(0.3); }
        50% { opacity: 1; transform: scale(1.05); }
        70% { transform: scale(0.9); }
        100% { opacity: 1; transform: scale(1); }
      }
      
      .gradient-text {
        background: linear-gradient(135deg, #F53003, #FF6B35, #F7931E);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
      }
      
      .glass-effect {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }
      
      .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
      }
      
      .scrollbar-hide::-webkit-scrollbar {
        display: none;
      }
    </style>
  </head>
  <body class="min-h-screen bg-[#0d0f14] text-white">
    <!-- Header -->
    <header class="bg-[#151822] border-b border-[#262833] sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-6">
        <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
          <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-12 w-12 object-contain rounded" width="48" height="48">
          <span class="text-xl font-bold gradient-text">MovieHub</span>
        </a>
        
        <!-- Navigation -->
        <nav class="hidden md:flex items-center gap-6">
          <a href="{{ route('home') }}" class="text-white hover:text-[#F53003] transition-colors duration-300 font-medium">Trang chủ</a>
          <a href="{{ route('movies.index') }}" class="text-white hover:text-[#F53003] transition-colors duration-300 font-medium">Phim</a>
          <a href="{{ route('movies.showtimes') }}" class="text-white hover:text-[#F53003] transition-colors duration-300 font-medium">Lịch chiếu</a>
          <a href="#" class="text-white hover:text-[#F53003] transition-colors duration-300 font-medium">Khuyến mãi</a>
        </nav>
        
        <!-- User Actions -->
        <div class="flex items-center gap-4">
          <a href="{{ route('mini-game') }}" class="text-white hover:text-[#F53003] transition-colors duration-300">
            <i class="fas fa-gamepad text-lg"></i>
          </a>
          
          @auth
            @if(auth()->user()->vaiTro && auth()->user()->vaiTro->ten === 'admin')
              <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-[#F53003] transition-colors duration-300">
                <i class="fas fa-cog text-lg"></i>
              </a>
            @elseif(auth()->user()->vaiTro && auth()->user()->vaiTro->ten === 'staff')
              <a href="{{ route('staff.dashboard') }}" class="text-white hover:text-[#F53003] transition-colors duration-300">
                <i class="fas fa-user-tie text-lg"></i>
              </a>
            @endif
            
            <form method="POST" action="{{ route('logout') }}" class="inline">
              @csrf
              <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
                Đăng xuất
              </button>
            </form>
          @else
            <a href="{{ route('login.form') }}" class="bg-[#F53003] hover:bg-[#e02a00] text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
              Đăng nhập
            </a>
          @endauth
        </div>
        
        <!-- Mobile Menu Button -->
        <button class="md:hidden text-white hover:text-[#F53003] transition-colors duration-300">
          <i class="fas fa-bars text-xl"></i>
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen">
      @yield('content')
    </main>

    <!-- Footer -->
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
            © {{ date('Y') }} MovieHub. Tất cả quyền được bảo lưu.
          </p>
        </div>
      </div>
    </footer>

    <!-- Scripts -->
    <script>
      // Hero Carousel
      document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.hero-slide');
        let currentSlide = 0;
        
        function showSlide(index) {
          slides.forEach((slide, i) => {
            slide.classList.toggle('opacity-0', i !== index);
            slide.classList.toggle('opacity-100', i === index);
          });
        }
        
        function nextSlide() {
          currentSlide = (currentSlide + 1) % slides.length;
          showSlide(currentSlide);
        }
        
        // Auto-advance slides every 5 seconds
        setInterval(nextSlide, 5000);
        
        // Initialize first slide
        showSlide(0);
      });
      
      // Smooth scrolling for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });
      
      // Add animation classes on scroll
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in');
          }
        });
      }, observerOptions);
      
      // Observe movie cards
      document.querySelectorAll('.movie-card').forEach(card => {
        observer.observe(card);
      });
    </script>
    
    @yield('scripts')
  </body>
</html>
