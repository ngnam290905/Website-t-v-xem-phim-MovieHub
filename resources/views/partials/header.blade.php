<header id="main-header" class="bg-transparent sticky top-0 z-50 transition-all duration-300">
  <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between gap-4">
    <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
      <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-12 w-12 object-contain rounded">
      <span class="text-xl font-semibold whitespace-nowrap">MovieHub</span>
    </a>

    <!-- Search Bar -->
    <div class="hidden lg:flex flex-1 max-w-md mx-4">
      <form action="{{ route('movies.index') }}" method="GET" class="w-full relative">
        <input 
          type="text" 
          name="search" 
          id="header-search"
          value="{{ request('search') }}"
          placeholder="Tìm kiếm phim, đạo diễn, diễn viên..." 
          class="w-full px-3 py-1.5 pl-9 bg-[#151822] border border-[#262833] rounded-lg text-white text-sm placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003] transition-colors"
        >
        <i class="fas fa-search absolute left-2.5 top-1/2 transform -translate-y-1/2 text-[#a6a6b0] text-sm"></i>
      </form>
    </div>

    <!-- Desktop Navigation -->
    <nav class="hidden lg:flex items-center gap-4 text-sm whitespace-nowrap">
      <a href="{{ route('home') }}" class="hover:text-[#F53003] transition flex items-center gap-1">
        <i class="fas fa-home text-xs"></i>
        <span>Trang chủ</span>
      </a>
      <a href="{{ route('movies.showtimes') }}" class="hover:text-[#F53003] transition flex items-center gap-1">
        <i class="fas fa-calendar-alt text-xs"></i>
        <span>Lịch chiếu</span>
      </a>
      <a href="{{ route('public.news') }}" class="hover:text-[#F53003] transition flex items-center gap-1">
        <i class="fas fa-newspaper text-xs"></i>
        <span>Tin tức</span>
      </a>
      <a href="{{ route('public.pricing') }}" class="hover:text-[#F53003] transition flex items-center gap-1">
        <i class="fas fa-tags text-xs"></i>
        <span>Giá vé</span>
      </a>
      <a href="{{ route('about') }}" class="hover:text-[#F53003] transition flex items-center gap-1">
        <i class="fas fa-info-circle text-xs"></i>
        <span>Giới thiệu</span>
      </a>
    </nav>

    <!-- Mobile Menu Button -->
    <button id="mobile-menu-btn" class="lg:hidden text-[#a6a6b0] hover:text-[#F53003] transition">
      <i class="fas fa-bars text-xl"></i>
    </button>

    <div class="flex items-center gap-2 text-sm whitespace-nowrap">
      <!-- Mobile Search Button -->
      <button id="mobile-search-btn" class="lg:hidden text-[#a6a6b0] hover:text-[#F53003] transition">
        <i class="fas fa-search text-xl"></i>
      </button>

      <!-- Quick Booking Button -->
      <a href="{{ route('booking.index') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-[#F53003] to-[#ff7849] text-white rounded-lg font-medium text-sm hover:shadow-lg hover:shadow-[#F53003]/50 transition-all">
        <i class="fas fa-ticket-alt text-xs"></i>
        <span>Mua vé nhanh</span>
      </a>

      @auth
        <div class="relative group">
          <button class="flex items-center gap-1.5 hover:text-[#F53003] transition">
            <div class="w-7 h-7 bg-[#F53003] rounded-full flex items-center justify-center">
              <span class="text-white text-xs font-medium">{{ strtoupper(substr(auth()->user()->ho_ten, 0, 1)) }}</span>
            </div>
            <span class="hidden sm:inline text-sm">{{ auth()->user()->ho_ten }}</span>
            @if(auth()->user()->la_thanh_vien)
              <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-yellow-600 to-orange-600 text-white">
                <i class="fas fa-crown text-xs"></i>
                Thành viên
              </span>
            @endif
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.188l3.71-3.958a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
          </button>

          <div class="absolute right-0 mt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 bg-[#1b1d24] border border-[#262833] rounded-md shadow-xl min-w-[200px] py-2 z-50">
            <a href="{{ route('user.profile') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-[#222533] transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <span>Tài khoản</span>
            </a>
            <a href="{{ route('user.bookings') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-[#222533] transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
              </svg>
              <span>Vé của tôi</span>
            </a>
            <a href="{{ route('user.edit-profile') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-[#222533] transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              <span>Chỉnh sửa hồ sơ</span>
            </a>

            @if(auth()->user() && auth()->user()->vaiTro && in_array(auth()->user()->vaiTro->ten, ['admin', 'staff']))
              <div class="border-t border-[#262833] my-2"></div>
              <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-[#222533] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span>Quản trị</span>
              </a>
            @endif

            <div class="border-t border-[#262833] my-2"></div>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
              @csrf
              <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 hover:bg-[#222533] transition text-left">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Đăng xuất</span>
              </button>
            </form>
          </div>
        </div>
      @else
        <a href="{{ route('login.form') }}" class="inline-flex items-center gap-1.5 hover:text-[#F53003] text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/>
            <path fill-rule="evenodd" d="M2 20.25C2 16.245 5.134 13 9 13h6c3.866 0 7 3.245 7 7.25v.75H2v-.75z" clip-rule="evenodd"/>
          </svg>
          Đăng nhập
        </a>
        <a href="{{ route('register.form') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#F53003] text-[#ff7a5f] hover:bg-[#2a2d3a] text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/>
            <path d="M12 14c-4.418 0-8 3.134-8 7v1h9.5a6.5 6.5 0 000-13H12z"/>
          </svg>
          Đăng ký
        </a>
      @endauth
    </div>
  </div>

  <!-- Mobile Search Bar -->
  <div id="mobile-search" class="hidden lg:hidden border-t border-[#262833] px-4 py-3 bg-transparent">
    <form action="{{ route('movies.index') }}" method="GET" class="relative">
      <input 
        type="text" 
        name="search" 
        value="{{ request('search') }}"
        placeholder="Tìm kiếm phim..." 
        class="w-full px-4 py-2 pl-10 bg-[#151822] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]"
      >
      <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-[#a6a6b0]"></i>
      <button type="button" id="mobile-search-close" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-[#a6a6b0] hover:text-white">
        <i class="fas fa-times"></i>
      </button>
    </form>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden lg:hidden border-t border-[#262833] bg-transparent">
    <nav class="flex flex-col px-4 py-4 space-y-1">
      <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#222533] transition text-white">
        <i class="fas fa-home text-[#F53003]"></i>
        <span>Trang chủ</span>
      </a>
      <a href="{{ route('movies.showtimes') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#222533] transition text-white">
        <i class="fas fa-calendar-alt text-[#F53003]"></i>
        <span>Lịch chiếu</span>
      </a>
      <a href="{{ route('public.news') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#222533] transition text-white">
        <i class="fas fa-newspaper text-[#F53003]"></i>
        <span>Tin tức</span>
      </a>
      <a href="{{ route('public.pricing') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#222533] transition text-white">
        <i class="fas fa-tags text-[#F53003]"></i>
        <span>Giá vé</span>
      </a>
      <a href="{{ route('about') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#222533] transition text-white">
        <i class="fas fa-info-circle text-[#F53003]"></i>
        <span>Giới thiệu</span>
      </a>
    </nav>
  </div>
</header>

<script>
  // Mobile search toggle
  document.getElementById('mobile-search-btn')?.addEventListener('click', function() {
    const mobileSearch = document.getElementById('mobile-search');
    const mobileMenu = document.getElementById('mobile-menu');
    mobileSearch.classList.toggle('hidden');
    mobileMenu.classList.add('hidden'); // Close menu when opening search
    
    // Update background based on scroll position
    if (window.scrollY > 40) {
      mobileSearch.classList.add('bg-[#1b1d24]');
      mobileSearch.classList.remove('bg-transparent');
    } else {
      mobileSearch.classList.remove('bg-[#1b1d24]');
      mobileSearch.classList.add('bg-transparent');
    }
  });

  document.getElementById('mobile-search-close')?.addEventListener('click', function() {
    document.getElementById('mobile-search').classList.add('hidden');
  });

  // Mobile menu toggle
  document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileSearch = document.getElementById('mobile-search');
    mobileMenu.classList.toggle('hidden');
    mobileSearch.classList.add('hidden'); // Close search when opening menu
    
    // Update background based on scroll position
    if (window.scrollY > 40) {
      mobileMenu.classList.add('bg-[#1b1d24]');
      mobileMenu.classList.remove('bg-transparent');
    } else {
      mobileMenu.classList.remove('bg-[#1b1d24]');
      mobileMenu.classList.add('bg-transparent');
    }
  });

  // Function to update header background
  function updateHeaderBackground() {
    const header = document.getElementById('main-header');
    const mobileSearch = document.getElementById('mobile-search');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (window.scrollY > 40) {
      // Add black background when scrolled
      header.classList.add('bg-[#1b1d24]', 'border-b', 'border-[#262833]', 'shadow-lg', 'shadow-black/20');
      header.classList.remove('bg-transparent');
      if (mobileSearch && !mobileSearch.classList.contains('hidden')) {
        mobileSearch.classList.add('bg-[#1b1d24]');
        mobileSearch.classList.remove('bg-transparent');
      }
      if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.add('bg-[#1b1d24]');
        mobileMenu.classList.remove('bg-transparent');
      }
    } else {
      // Remove background when at top
      header.classList.remove('bg-[#1b1d24]', 'border-b', 'border-[#262833]', 'shadow-lg', 'shadow-black/20');
      header.classList.add('bg-transparent');
      if (mobileSearch) {
        mobileSearch.classList.remove('bg-[#1b1d24]');
        mobileSearch.classList.add('bg-transparent');
      }
      if (mobileMenu) {
        mobileMenu.classList.remove('bg-[#1b1d24]');
        mobileMenu.classList.add('bg-transparent');
      }
    }
  }

  // Header background on scroll
  window.addEventListener('scroll', updateHeaderBackground);
  
  // Check scroll position on page load
  updateHeaderBackground();
</script>