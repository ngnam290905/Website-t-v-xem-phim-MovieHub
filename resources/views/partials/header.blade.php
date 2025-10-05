<header class="bg-[#1b1d24] border-b border-[#262833]">
  <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-6">
    <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
      <img src="/logo.png" alt="MovieHub" class="h-12 w-12 object-contain rounded">
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
      <a href="#login" class="inline-flex items-center gap-2 hover:text-[#F53003]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/><path fill-rule="evenodd" d="M2 20.25C2 16.245 5.134 13 9 13h6c3.866 0 7 3.245 7 7.25v.75H2v-.75z" clip-rule="evenodd"/></svg>
        Đăng nhập
      </a>
      <a href="#register" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-[#F53003] text-[#ff7a5f] hover:bg-[#2a2d3a]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/><path d="M12 14c-4.418 0-8 3.134-8 7v1h9.5a6.5 6.5 0 000-13H12z"/></svg>
        Đăng ký
      </a>
    </div>
  </div>
</header>

