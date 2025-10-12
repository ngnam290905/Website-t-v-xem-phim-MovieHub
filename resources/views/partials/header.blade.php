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

<header class="bg-[#1b1d24] border-b border-[#262833] sticky top-0 z-50 backdrop-blur-sm bg-[#1b1d24]/95">
  <div class=" w-full px-6 py-2 flex items-center justify-between gap-6">
    <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group">
      <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-16 w-16 object-contain rounded transition-all duration-300 group-hover:brightness-125 group-hover:scale-105">
      <span class="text-2xl font-semibold text-white group-hover:text-orange-400 transition-all duration-300">MovieHub</span>
    </a>

  <nav class="hidden md:flex items-center gap-8 text-[15px] px-2 py-2 whitespace-nowrap">
      <a href="{{ route('home') }}" class="flex items-center gap-2 hover:text-orange-400 transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
        </svg>
        <span>Trang chủ</span>
      </a>
      <a href="#phim" class="relative flex items-center gap-2 hover:text-orange-400 transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
        </svg>
        <span>Phim</span>
        <span class="absolute -top-3 -right-6 text-xs bg-orange-500 text-white rounded-full px-2 py-0.5 font-bold animate-pulse">🔥 Hot</span>
      </a>
      <a href="#gio-ve" class="flex items-center gap-2 hover:text-orange-400 transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
        </svg>
        <span>Giờ vé</span>
      </a>
      <a href="{{ route('mini-game') }}" class="relative flex items-center gap-2 hover:text-orange-400 transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Mini Game</span>
        <span class="absolute -top-3 -right-6 text-xs bg-green-500 text-white rounded-full px-2 py-0.5 font-bold animate-pulse">🎰 Mới</span>
      </a>
      <div class="relative group">
        <button class="inline-flex items-center gap-2 hover:text-[#F53003] transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
          </svg>
          <span>Thể loại</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.188l3.71-3.958a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div class="absolute left-0 mt-3 hidden group-hover:block bg-[#0f0f12] border border-[#262833] rounded-md shadow-xl min-w-[180px] p-2">
          <a href="#" class="block px-3 py-2 rounded hover:bg-[#222533]">Hành động</a>
          <a href="#" class="block px-3 py-2 rounded hover:bg-[#222533]">Tình cảm</a>
          <a href="#" class="block px-3 py-2 rounded hover:bg-[#222533]">Hài</a>
          <a href="#" class="block px-3 py-2 rounded hover:bg-[#222533]">Kinh dị</a>
        </div>
      </div>
    </nav>

    <div class="flex items-center gap-4 text-[15px]">
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
        <a href="#ve" class="hidden sm:inline-flex items-center gap-2 hover:text-[#F53003] transition-colors">
          <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#1b1d24] font-bold">✓</span>
          Vé
        </a>
        <a href="#login" class="inline-flex items-center gap-2 hover:text-[#F53003] transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/><path fill-rule="evenodd" d="M2 20.25C2 16.245 5.134 13 9 13h6c3.866 0 7 3.245 7 7.25v.75H2v-.75z" clip-rule="evenodd"/></svg>
          Đăng nhập
        </a>
        
      </div>
    </div>
  </div>
</header>

<script>
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
</script>

