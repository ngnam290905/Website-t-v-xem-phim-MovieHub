<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MovieHub - Đặt vé xem phim')</title>
    @yield('meta')
    <!-- Image Enhancements -->
    <link rel="stylesheet" href="{{ asset('css/image-enhancements.css') }}">
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css','resources/js/app.js'])
    @else
      <script src="https://cdn.tailwindcss.com"></script>
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
  <body class="min-h-screen bg-gradient-to-b from-[#050814] via-[#0a0f1a] to-[#101827] text-white">
    <!-- Header -->
    <header id="main-header" class="fixed top-0 left-0 right-0 z-50 w-full border-b border-[#262833]/30 backdrop-blur-md bg-gradient-to-b from-[#050814]/95 to-[#101827]/95 transition-all duration-300 shadow-lg">
      <div class="max-w-7xl mx-auto px-4">
        <!-- Main Navbar -->
        <div class="h-16 flex items-center justify-between gap-6">
          <!-- Left: Logo + MovieHub -->
          <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group">
            <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-10 w-10 object-contain rounded transition-transform duration-300 group-hover:scale-110">
            <span class="text-xl font-bold gradient-text flex items-center gap-2">
              <i class="fas fa-film text-[#FF784E]"></i>
              MovieHub
            </span>
          </a>
          
          <!-- Center: Navigation Menu -->
          <nav class="hidden md:flex items-center gap-8 flex-1 justify-center">
            <a href="{{ route('home') }}" class="text-white/90 hover:text-[#FF784E] transition-all duration-300 font-medium text-sm relative group">
              <i class="fas fa-home mr-1.5"></i>
              Trang chủ
              <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#FF784E] transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="{{ route('public.movies') }}" class="text-white/90 hover:text-[#FF784E] transition-all duration-300 font-medium text-sm relative group">
              <i class="fas fa-video mr-1.5"></i>
              Phim
              <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#FF784E] transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="{{ route('public.schedule') }}" class="text-white/90 hover:text-[#FF784E] transition-all duration-300 font-medium text-sm relative group">
              <i class="fas fa-calendar-alt mr-1.5"></i>
              Lịch chiếu
              <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#FF784E] transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="{{ route('public.combos') }}" class="text-white/90 hover:text-[#FF784E] transition-all duration-300 font-medium text-sm relative group">
              <i class="fas fa-box mr-1.5"></i>
              Combo
              <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#FF784E] transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="{{ route('public.news') }}" class="text-white/90 hover:text-[#FF784E] transition-all duration-300 font-medium text-sm relative group">
              <i class="fas fa-newspaper mr-1.5"></i>
              Tin tức
              <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#FF784E] transition-all duration-300 group-hover:w-full"></span>
            </a>
          </nav>
          
          <!-- Right: User Actions -->
          <div class="flex items-center gap-3">
            @auth
              <a href="{{ route('booking.tickets') }}" class="hidden sm:flex items-center gap-2 text-white/80 hover:text-[#FF784E] hover:bg-white/5 px-3 py-2 rounded-lg transition-all duration-300 text-sm font-medium group">
                <i class="fas fa-ticket-alt"></i>
                <span>Vé của tôi</span>
              </a>
              <div class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/5 transition-all duration-300 group">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#FF784E] to-[#FFB25E] flex items-center justify-center text-white font-semibold text-sm">
                  {{ strtoupper(substr(auth()->user()->ho_ten ?? 'U', 0, 1)) }}
                </div>
                <span class="hidden lg:block text-white/90 text-sm font-medium">{{ auth()->user()->ho_ten ?? 'User' }}</span>
              </div>
              @if(in_array(optional(auth()->user()->vaiTro)->ten, ['admin', 'staff']))
                <a href="{{ route('admin.dashboard') }}" class="hidden sm:flex items-center gap-2 bg-[#FF784E]/20 hover:bg-[#FF784E]/30 text-[#FF784E] px-3 py-2 rounded-lg transition-all duration-300 text-sm font-medium border border-[#FF784E]/30">
                  <i class="fas fa-cog"></i>
                  <span>Quản trị</span>
                </a>
              @endif
              <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-white/70 hover:text-white hover:bg-white/5 px-3 py-2 rounded-lg transition-all duration-300 text-sm font-medium border border-white/10 hover:border-white/20">
                  <i class="fas fa-sign-out-alt mr-1.5"></i>
                  <span class="hidden sm:inline">Đăng xuất</span>
                </button>
              </form>
            @else
              <a href="{{ route('login.form') }}" class="bg-gradient-to-r from-[#FF784E] to-[#FFB25E] hover:from-[#FF784E]/90 hover:to-[#FFB25E]/90 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-300 text-sm shadow-lg hover:shadow-[#FF784E]/50">
                <i class="fas fa-sign-in-alt mr-1.5"></i>
                Đăng nhập
              </a>
            @endauth
          </div>
          
          <!-- Mobile Menu Button -->
          <button id="mobile-menu-btn" class="md:hidden text-white hover:text-[#FF784E] transition-colors duration-300">
            <i class="fas fa-bars text-xl"></i>
          </button>
        </div>
        
        <!-- Search Bar -->
        <div class="h-14 border-t border-[#262833]/30 flex items-center gap-3 py-2">
          <div class="flex-1 relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-[#a6a6b0]"></i>
            <input type="text" id="search-input" placeholder="🔍 Tìm phim, rạp, diễn viên..." 
                   class="w-full pl-12 pr-4 py-2.5 bg-[#1b1d24]/50 border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#FF784E] focus:ring-2 focus:ring-[#FF784E]/20 transition-all duration-300">
            <div id="search-results" class="hidden absolute top-full left-0 right-0 mt-2 bg-[#1b1d24] border border-[#262833] rounded-lg shadow-xl max-h-96 overflow-y-auto z-50"></div>
          </div>
          <button class="hidden sm:flex items-center gap-2 px-4 py-2.5 bg-[#1b1d24]/50 border border-[#262833] rounded-lg text-white/90 hover:text-[#FF784E] hover:border-[#FF784E]/50 transition-all duration-300 text-sm font-medium">
            <i class="fas fa-map-marker-alt"></i>
            <span>📍 Chọn rạp</span>
          </button>
          <button class="hidden sm:flex items-center gap-2 px-4 py-2.5 bg-[#1b1d24]/50 border border-[#262833] rounded-lg text-white/90 hover:text-[#FF784E] hover:border-[#FF784E]/50 transition-all duration-300 text-sm font-medium">
            <i class="fas fa-calendar"></i>
            <span>📅 Hôm nay</span>
            <i class="fas fa-chevron-down text-xs ml-1"></i>
          </button>
        </div>
      </div>
    </header>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="fixed inset-0 z-40 bg-[#050814]/95 backdrop-blur-md transform translate-x-full transition-transform duration-300 md:hidden">
      <div class="flex flex-col h-full">
        <div class="flex items-center justify-between p-4 border-b border-[#262833]">
          <span class="text-xl font-bold text-white">Menu</span>
          <button id="mobile-menu-close" class="text-white hover:text-[#FF784E] transition-colors">
            <i class="fas fa-times text-2xl"></i>
          </button>
        </div>
        <nav class="flex-1 overflow-y-auto p-4 space-y-2">
          <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#1b1d24] border border-[#262833] text-white hover:border-[#FF784E] transition-all">
            <i class="fas fa-home text-[#FF784E]"></i>
            <span>Trang chủ</span>
          </a>
          <a href="{{ route('public.movies') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#1b1d24] border border-[#262833] text-white hover:border-[#FF784E] transition-all">
            <i class="fas fa-video text-[#FF784E]"></i>
            <span>Phim</span>
          </a>
          <a href="{{ route('public.schedule') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#1b1d24] border border-[#262833] text-white hover:border-[#FF784E] transition-all">
            <i class="fas fa-calendar-alt text-[#FF784E]"></i>
            <span>Lịch chiếu</span>
          </a>
          <a href="{{ route('public.combos') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#1b1d24] border border-[#262833] text-white hover:border-[#FF784E] transition-all">
            <i class="fas fa-box text-[#FF784E]"></i>
            <span>Combo</span>
          </a>
          <a href="{{ route('public.news') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#1b1d24] border border-[#262833] text-white hover:border-[#FF784E] transition-all">
            <i class="fas fa-newspaper text-[#FF784E]"></i>
            <span>Tin tức</span>
          </a>
          @auth
            <a href="{{ route('booking.tickets') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#1b1d24] border border-[#262833] text-white hover:border-[#FF784E] transition-all">
              <i class="fas fa-ticket-alt text-[#FF784E]"></i>
              <span>Vé của tôi</span>
            </a>
          @else
            <a href="{{ route('login.form') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-[#FF784E] to-[#FFB25E] text-white font-semibold">
              <i class="fas fa-sign-in-alt"></i>
              <span>Đăng nhập</span>
            </a>
          @endauth
        </nav>
      </div>
    </div>

    <!-- Main Content -->
    <main class="min-h-screen pt-[130px]">
      @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-b from-[#101827] to-[#050814] border-t border-[#262833] mt-16">
      <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <!-- Company Info -->
          <div class="lg:col-span-2">
            <div class="flex items-center gap-3 mb-4">
              <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-12 w-12 object-contain rounded">
              <span class="text-2xl font-bold gradient-text flex items-center gap-2">
                <i class="fas fa-film text-[#FF784E]"></i>
                MovieHub
              </span>
            </div>
            <p class="text-[#a6a6b0] text-sm leading-relaxed mb-6 max-w-md">
              Nền tảng đặt vé xem phim trực tuyến hàng đầu Việt Nam. 
              Trải nghiệm điện ảnh tuyệt vời với giá vé ưu đãi và dịch vụ chuyên nghiệp.
            </p>
            <div class="flex gap-4 mb-6">
              <a href="#" class="w-10 h-10 rounded-full bg-[#1b1d24] border border-[#262833] flex items-center justify-center text-[#a6a6b0] hover:text-[#FF784E] hover:border-[#FF784E] transition-all duration-300">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="#" class="w-10 h-10 rounded-full bg-[#1b1d24] border border-[#262833] flex items-center justify-center text-[#a6a6b0] hover:text-[#FF784E] hover:border-[#FF784E] transition-all duration-300">
                <i class="fab fa-twitter"></i>
              </a>
              <a href="#" class="w-10 h-10 rounded-full bg-[#1b1d24] border border-[#262833] flex items-center justify-center text-[#a6a6b0] hover:text-[#FF784E] hover:border-[#FF784E] transition-all duration-300">
                <i class="fab fa-instagram"></i>
              </a>
              <a href="#" class="w-10 h-10 rounded-full bg-[#1b1d24] border border-[#262833] flex items-center justify-center text-[#a6a6b0] hover:text-[#FF784E] hover:border-[#FF784E] transition-all duration-300">
                <i class="fab fa-youtube"></i>
              </a>
              <a href="#" class="w-10 h-10 rounded-full bg-[#1b1d24] border border-[#262833] flex items-center justify-center text-[#a6a6b0] hover:text-[#FF784E] hover:border-[#FF784E] transition-all duration-300">
                <i class="fab fa-tiktok"></i>
              </a>
            </div>
            <div class="flex items-center gap-2 text-sm text-[#a6a6b0]">
              <i class="fas fa-mobile-alt text-[#FF784E]"></i>
              <span>Tải app</span>
              <div class="flex gap-2 ml-2">
                <a href="#" class="px-3 py-1.5 bg-[#1b1d24] border border-[#262833] rounded-lg hover:border-[#FF784E] transition-all duration-300">
                  <i class="fab fa-apple"></i>
                </a>
                <a href="#" class="px-3 py-1.5 bg-[#1b1d24] border border-[#262833] rounded-lg hover:border-[#FF784E] transition-all duration-300">
                  <i class="fab fa-google-play"></i>
                </a>
              </div>
            </div>
          </div>
          
          <!-- Quick Links -->
          <div>
            <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
              <i class="fas fa-link text-[#FF784E] text-sm"></i>
              Liên kết nhanh
            </h3>
            <ul class="space-y-2.5">
              <li><a href="{{ route('home') }}" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Trang chủ
              </a></li>
              <li><a href="{{ route('public.movies') }}" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Phim đang chiếu
              </a></li>
              <li><a href="{{ route('public.schedule') }}" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Lịch chiếu
              </a></li>
              <li><a href="{{ route('public.combos') }}" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Combo
              </a></li>
              <li><a href="{{ route('public.news') }}" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Tin tức
              </a></li>
            </ul>
          </div>
          
          <!-- Support -->
          <div>
            <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
              <i class="fas fa-headset text-[#FF784E] text-sm"></i>
              Hỗ trợ
            </h3>
            <ul class="space-y-2.5">
              <li><a href="#" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Trung tâm trợ giúp
              </a></li>
              <li><a href="#" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Liên hệ
              </a></li>
              <li><a href="#" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Điều khoản sử dụng
              </a></li>
              <li><a href="#" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Chính sách bảo mật
              </a></li>
              <li><a href="#" class="text-[#a6a6b0] hover:text-[#FF784E] transition-colors duration-300 text-sm flex items-center gap-2 group">
                <i class="fas fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                Giới thiệu
              </a></li>
            </ul>
          </div>
        </div>
        
        <div class="border-t border-[#262833] mt-12 pt-8">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <p class="text-[#a6a6b0] text-sm text-center md:text-left">
              © {{ date('Y') }} MovieHub. Tất cả quyền được bảo lưu.
            </p>
            <div class="flex items-center justify-center gap-6 text-sm text-[#a6a6b0]">
              <span>Được phát triển bởi</span>
              <span class="text-[#FF784E] font-semibold">MovieHub Team</span>
            </div>
          </div>
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
      
      // Header scroll effect
      const header = document.getElementById('main-header');
      if (header) {
        window.addEventListener('scroll', () => {
          const currentScroll = window.pageYOffset;
          if (currentScroll > 10) {
            header.classList.add('shadow-lg');
          } else {
            header.classList.remove('shadow-lg');
          }
        });
      }
      
      // Search functionality
      const searchInput = document.getElementById('search-input');
      const searchResults = document.getElementById('search-results');
      let searchTimeout;
      
      if (searchInput && searchResults) {
        searchInput.addEventListener('input', function(e) {
          const query = e.target.value.trim();
          
          clearTimeout(searchTimeout);
          
          if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
          }
          
          searchTimeout = setTimeout(async () => {
            try {
              const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
              const movies = await response.json();
              
              if (movies.length > 0) {
                searchResults.innerHTML = movies.slice(0, 5).map(movie => `
                  <a href="/movies/${movie.id}" class="flex items-center gap-3 p-3 hover:bg-[#262833] transition-colors border-b border-[#262833] last:border-0">
                    <img src="${movie.poster_url || '/images/no-poster.svg'}" alt="${movie.ten_phim}" class="w-12 h-16 object-cover rounded">
                    <div class="flex-1">
                      <h4 class="text-white font-semibold text-sm">${movie.ten_phim}</h4>
                      <p class="text-[#a6a6b0] text-xs mt-1">${movie.the_loai || 'N/A'}</p>
                    </div>
                  </a>
                `).join('');
                searchResults.classList.remove('hidden');
              } else {
                searchResults.innerHTML = '<div class="p-4 text-center text-[#a6a6b0] text-sm">Không tìm thấy kết quả</div>';
                searchResults.classList.remove('hidden');
              }
            } catch (error) {
              console.error('Search error:', error);
            }
          }, 300);
        });
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
          if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
          }
        });
      }
      
      // Mobile menu toggle
      const mobileMenuBtn = document.getElementById('mobile-menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');
      const mobileMenuClose = document.getElementById('mobile-menu-close');
      
      if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
          mobileMenu.classList.remove('translate-x-full');
        });
      }
      
      if (mobileMenuClose && mobileMenu) {
        mobileMenuClose.addEventListener('click', () => {
          mobileMenu.classList.add('translate-x-full');
        });
      }
      
      // Close mobile menu when clicking outside
      if (mobileMenu) {
        mobileMenu.addEventListener('click', (e) => {
          if (e.target === mobileMenu) {
            mobileMenu.classList.add('translate-x-full');
          }
        });
      }
      
      // Genre chip toggle
      document.querySelectorAll('.genre-chip').forEach(chip => {
        chip.addEventListener('click', function() {
          this.classList.toggle('bg-[#FF784E]');
          this.classList.toggle('text-white');
          this.classList.toggle('border-[#FF784E]');
        });
      });
      
      // Rating button toggle
      document.querySelectorAll('.rating-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          document.querySelectorAll('.rating-btn').forEach(b => {
            b.classList.remove('bg-[#FF784E]', 'text-white', 'border-[#FF784E]');
            b.classList.add('bg-[#151822]', 'text-white/70', 'border-[#262833]');
          });
          this.classList.add('bg-[#FF784E]', 'text-white', 'border-[#FF784E]');
          this.classList.remove('bg-[#151822]', 'text-white/70', 'border-[#262833]');
        });
      });
      
      // Subtle bounce animation for floating CTA
      const style = document.createElement('style');
      style.textContent = `
        @keyframes bounce-subtle {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-5px); }
        }
        .animate-bounce-subtle {
          animation: bounce-subtle 2s ease-in-out infinite;
        }
      `;
      document.head.appendChild(style);
    </script>
    
    @yield('scripts')
  </body>
</html>
