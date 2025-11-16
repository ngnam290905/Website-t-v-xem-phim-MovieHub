<header class="bg-[#1b1d24] border-b border-[#262833]">
  <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-6">
    <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
      <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-20 w-20 object-contain rounded">
      <span class="text-2xl font-semibold">MovieHub</span>
    </a>

    <nav class="hidden md:flex items-center gap-10 text-[15px]">
      <a href="{{ route('home') }}" class="hover:text-[#F53003] transition">Trang chủ</a>
      <a href="#phim" class="hover:text-[#F53003] transition">Phim</a>
      <a href="#gio-ve" class="hover:text-[#F53003] transition">Giờ vé</a>
      <div class="relative group">
        <button class="inline-flex items-center gap-2 hover:text-[#F53003] transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm2 3h8l-4 5-4-5z"/></svg>
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
      <a href="#ve" class="hidden sm:inline-flex items-center gap-2 hover:text-[#F53003]">
        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#1b1d24] font-bold">✓</span>
        Vé
      </a>
      @auth
        <div class="flex items-center gap-4">
          <div class="relative group">
            <button class="flex items-center gap-2 hover:text-[#F53003] transition">
              <div class="w-8 h-8 bg-[#F53003] rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">{{ strtoupper(substr(auth()->user()->ho_ten, 0, 1)) }}</span>
              </div>
              <span class="hidden sm:inline text-sm">{{ auth()->user()->ho_ten }}</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
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
              <a href="{{ route('user.booking-history') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-[#222533] transition">
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
              <div class="border-t border-[#262833] my-2"></div>
              @if(in_array(optional(auth()->user()->vaiTro)->ten, ['admin', 'staff']))
                <a href="{{ optional(auth()->user()->vaiTro)->ten === 'admin' ? route('admin.dashboard') : route('staff.movies.index') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-[#222533] transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                  <span>Quản trị</span>
                </a>
              @endif
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