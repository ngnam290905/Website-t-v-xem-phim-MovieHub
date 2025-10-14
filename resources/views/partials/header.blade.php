<!-- Notification Banner -->
<div class="bg-gradient-to-r from-[#F53003] to-orange-400 text-white py-2 px-4 text-center text-sm font-medium animate-pulse">
  <div class="max-w-7xl mx-auto flex items-center justify-center gap-2">
    <span>🎁</span>
    <span>Giảm 20% cho thành viên mới! Đăng ký ngay để nhận ưu đãi đặc biệt</span>
    <button class="ml-2 bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full text-xs transition-all duration-300">
      Đăng ký ngay
    </button>
  </div>
</div>

<header class="border-b border-[#262833] sticky top-0 z-50 backdrop-blur-md bg-[#1b1d24]/95 shadow-lg">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-6">
    <!-- Logo -->
    <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group">
      <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-12 w-12 object-contain rounded transition-all duration-300 group-hover:brightness-125 group-hover:scale-105">
      <span class="text-2xl font-bold text-white group-hover:text-orange-400 transition-all duration-300">MovieHub</span>
    </a>

    <!-- Search Bar -->
    <div class="hidden lg:flex flex-1 max-w-md mx-8">
      <div class="relative w-full">
        <input type="text" placeholder="Tìm kiếm phim, diễn viên, đạo diễn..." 
               class="w-full px-4 py-2.5 pl-12 bg-[#2a2d3a] border border-[#3a3d4a] rounded-full text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003] focus:ring-2 focus:ring-[#F53003]/20 transition-all duration-300">
        <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#a6a6b0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <button class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-[#F53003] hover:bg-[#ff4d4d] text-white px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-300">
          Tìm
        </button>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="hidden md:flex items-center gap-6 text-[16px] font-medium">
      <a href="{{ route('home') }}" class="flex items-center gap-2 hover:text-orange-400 transition-all duration-300 px-3 py-2 rounded-lg hover:bg-[#2a2d3a]">
        <span class="text-lg">🏠</span>
        <span>Trang chủ</span>
      </a>
      
      <div class="relative group">
        <a href="#phim" class="flex items-center gap-2 hover:text-orange-400 transition-all duration-300 px-3 py-2 rounded-lg hover:bg-[#2a2d3a]">
          <span class="text-lg">🎬</span>
          <span>Phim</span>
          <span class="text-xs bg-orange-500 text-white rounded-full px-2 py-0.5 font-bold animate-pulse">Hot</span>
          <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </a>
        <div class="absolute left-0 mt-2 w-64 bg-[#0f0f12] border border-[#262833] rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
          <div class="p-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-semibold text-[#F53003] mb-2">Thể loại</h3>
                <div class="space-y-1">
                  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-[#222533] text-sm transition-colors">🎭 Hành động</a>
                  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-[#222533] text-sm transition-colors">💕 Tình cảm</a>
                  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-[#222533] text-sm transition-colors">😂 Hài</a>
                  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-[#222533] text-sm transition-colors">👻 Kinh dị</a>
                </div>
              </div>
              <div>
                <h3 class="text-sm font-semibold text-[#F53003] mb-2">Quốc gia</h3>
                <div class="space-y-1">
                  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-[#222533] text-sm transition-colors">🇺🇸 Hollywood</a>
                  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-[#222533] text-sm transition-colors">🇰🇷 Hàn Quốc</a>
                  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-[#222533] text-sm transition-colors">🇯🇵 Nhật Bản</a>
                  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-[#222533] text-sm transition-colors">🇻🇳 Việt Nam</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <a href="#gio-ve" class="flex items-center gap-2 hover:text-orange-400 transition-all duration-300 px-3 py-2 rounded-lg hover:bg-[#2a2d3a]">
        <span class="text-lg">🧾</span>
        <span>Giờ vé</span>
      </a>
      
      <a href="{{ route('mini-game') }}" class="relative flex items-center gap-2 hover:text-orange-400 transition-all duration-300 px-3 py-2 rounded-lg hover:bg-[#2a2d3a]">
        <span class="text-lg">🎰</span>
        <span>Mini Game</span>
        <span class="absolute -top-2 -right-2 text-xs bg-green-500 text-white rounded-full px-2 py-0.5 font-bold animate-pulse">Mới</span>
      </a>
    </nav>


    <!-- Mobile Menu Button -->
    <button class="md:hidden p-2 rounded-lg bg-[#2a2d3a] hover:bg-[#3a3d4a] transition-all duration-300" id="mobile-menu-btn">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>

    <!-- Right Side Actions -->
    <div class="flex items-center gap-3">
      <!-- Notifications -->
      <div class="relative notification-dropdown">
        <button class="p-2 rounded-lg bg-[#2a2d3a] hover:bg-[#3a3d4a] transition-all duration-300 relative notification-bell" title="Thông báo">
          <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
          </svg>
          <span class="notification-badge absolute -top-1 -right-1 bg-[#F53003] text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse">4</span>
        </button>
        @include('partials.notification-system')
      </div>
      
      <!-- User Profile (when logged in) -->
      <div class="relative group hidden" id="user-profile">
        <button class="flex items-center gap-2 p-2 rounded-lg bg-[#2a2d3a] hover:bg-[#3a3d4a] transition-all duration-300">
          <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=32&h=32&fit=crop&crop=face" alt="User" class="w-8 h-8 rounded-full border-2 border-[#F53003]">
          <span class="text-white text-sm">John Doe</span>
          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
        <div class="absolute right-0 mt-2 w-48 bg-[#1b1d24] border border-[#262833] rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
          <div class="py-2">
            <a href="#" class="block px-4 py-2 text-white hover:bg-[#2a2d3a] transition-colors">👤 Hồ sơ</a>
            <a href="#" class="block px-4 py-2 text-white hover:bg-[#2a2d3a] transition-colors">🎫 Vé của tôi</a>
            <a href="#" class="block px-4 py-2 text-white hover:bg-[#2a2d3a] transition-colors">⚙️ Cài đặt</a>
            <hr class="border-[#262833] my-2">
            <a href="#" class="block px-4 py-2 text-white hover:bg-[#2a2d3a] transition-colors">🚪 Đăng xuất</a>
          </div>
        </div>
      </div>
      
      <!-- Login/Register (when not logged in) -->
      <div id="auth-buttons" class="flex items-center gap-3">
        <a href="#ve" class="hidden sm:inline-flex items-center gap-2 hover:text-[#F53003] transition-colors px-3 py-2 rounded-lg hover:bg-[#2a2d3a]">
          <span class="text-lg">🎫</span>
          <span>Vé</span>
        </a>
        <a href="{{ route('login.form') }}" class="inline-flex items-center gap-2 hover:text-[#F53003] transition-colors px-3 py-2 rounded-lg hover:bg-[#2a2d3a]">
          <span class="text-lg">👤</span>
          <span>Đăng nhập</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Mobile Menu Drawer -->
  <div id="mobile-menu" class="fixed inset-0 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden">
    <div class="fixed inset-0 bg-black bg-opacity-50" id="mobile-menu-overlay"></div>
    <div class="fixed top-0 left-0 h-full w-80 bg-[#1b1d24] border-r border-[#262833] shadow-2xl">
      <div class="flex items-center justify-between p-4 border-b border-[#262833]">
        <div class="flex items-center gap-3">
          <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-10 w-10 object-contain rounded">
          <span class="text-xl font-bold text-white">MovieHub</span>
        </div>
        <button id="mobile-menu-close" class="p-2 rounded-lg bg-[#2a2d3a] hover:bg-[#3a3d4a] transition-all duration-300">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      
      <!-- Mobile Search -->
      <div class="p-4 border-b border-[#262833]">
        <div class="relative">
          <input type="text" placeholder="Tìm kiếm phim..." 
                 class="w-full px-4 py-3 pl-12 bg-[#2a2d3a] border border-[#3a3d4a] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
          <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#a6a6b0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
      </div>
      
      <!-- Mobile Navigation -->
      <nav class="p-4 space-y-2">
        <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#2a2d3a] transition-colors">
          <span class="text-xl">🏠</span>
          <span class="text-white font-medium">Trang chủ</span>
        </a>
        <a href="#phim" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#2a2d3a] transition-colors">
          <span class="text-xl">🎬</span>
          <span class="text-white font-medium">Phim</span>
          <span class="text-xs bg-orange-500 text-white rounded-full px-2 py-1 font-bold">Hot</span>
        </a>
        <a href="#gio-ve" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#2a2d3a] transition-colors">
          <span class="text-xl">🧾</span>
          <span class="text-white font-medium">Giờ vé</span>
        </a>
        <a href="{{ route('mini-game') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#2a2d3a] transition-colors">
          <span class="text-xl">🎰</span>
          <span class="text-white font-medium">Mini Game</span>
          <span class="text-xs bg-green-500 text-white rounded-full px-2 py-1 font-bold">Mới</span>
        </a>
      </nav>

    <div class="flex items-center gap-4 text-[15px]">
      <a href="#ve" class="hidden sm:inline-flex items-center gap-2 hover:text-[#F53003]">
        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#1b1d24] font-bold">✓</span>
        Vé
      </a>
      @auth
        <div class="flex items-center gap-4">
          <span class="text-sm">Xin chào, {{ auth()->user()->ho_ten }}</span>
          
          @if(in_array(optional(auth()->user()->vaiTro)->ten, ['admin', 'staff']))
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-[#F53003] hover:bg-[#e02a00] transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              Quản trị
            </a>
          @endif
          
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 hover:text-[#F53003]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
              Đăng xuất
            </button>
          </form>
        </div>
      @else
        <a href="{{ route('login.form') }}" class="inline-flex items-center gap-2 hover:text-[#F53003]">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/><path fill-rule="evenodd" d="M2 20.25C2 16.245 5.134 13 9 13h6c3.866 0 7 3.245 7 7.25v.75H2v-.75z" clip-rule="evenodd"/></svg>
          Đăng nhập
        </a>
        <a href="{{ route('register.form') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-[#F53003] text-[#ff7a5f] hover:bg-[#2a2d3a]">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/><path d="M12 14c-4.418 0-8 3.134-8 7v1h9.5a6.5 6.5 0 000-13H12z"/></svg>
          Đăng ký
        </a>
      @endauth

    </div>
  </div>
</header>

<script>
  // Mobile menu functionality
  document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');

    function openMobileMenu() {
      mobileMenu.classList.remove('-translate-x-full');
      document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
      mobileMenu.classList.add('-translate-x-full');
      document.body.style.overflow = 'auto';
    }

    mobileMenuBtn.addEventListener('click', openMobileMenu);
    mobileMenuClose.addEventListener('click', closeMobileMenu);
    mobileMenuOverlay.addEventListener('click', closeMobileMenu);

    // Close mobile menu on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && !mobileMenu.classList.contains('-translate-x-full')) {
        closeMobileMenu();
      }
    });
  });

  // Simulate user login/logout (for demo purposes)
  function toggleUserState() {
    const userProfile = document.getElementById('user-profile');
    const authButtons = document.getElementById('auth-buttons');
    
    if (userProfile.classList.contains('hidden')) {
      userProfile.classList.remove('hidden');
      authButtons.classList.add('hidden');
    } else {
      userProfile.classList.add('hidden');
      authButtons.classList.remove('hidden');
    }
  }
  
  // Auto-hide notification banner after 10 seconds
  setTimeout(() => {
    const banner = document.querySelector('.bg-gradient-to-r.from-\\[\\#F53003\\]');
    if (banner) {
      banner.style.transition = 'opacity 0.5s ease-out';
      banner.style.opacity = '0';
      setTimeout(() => {
        banner.style.display = 'none';
      }, 500);
    }
  }, 10000);

  // Search functionality
  document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('input[placeholder*="Tìm kiếm"]');
    searchInputs.forEach(input => {
      input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          const searchTerm = this.value.trim();
          if (searchTerm) {
            // Implement search functionality here
            console.log('Searching for:', searchTerm);
          }
        }
      });
    });
  });
</script>

