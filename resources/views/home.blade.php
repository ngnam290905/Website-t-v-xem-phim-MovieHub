@extends('layouts.app')

@section('title', 'MovieHub - Đặt vé xem phim')
@section('meta')
<meta name="description" content="Đặt vé xem phim trực tuyến, chọn rạp, giờ chiếu, ghế nhanh chóng tại MovieHub.">
<meta name="keywords" content="đặt vé phim, moviehub, rạp chiếu phim, phim mới, phim hot">
@endsection

@section('content')

  <!-- Hero Banner với Carousel và Sidebar -->
  <section class="relative mb-8 lg:mb-12">
    <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
      <!-- Sidebar - Responsive -->
      <aside class="lg:sticky lg:top-6 w-full lg:w-64 shrink-0 order-2 lg:order-1">
        <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-3 lg:p-3">
          <h3 class="font-semibold mb-2 text-white text-sm">Danh mục</h3>
          <nav class="flex flex-row lg:flex-col gap-1 text-xs overflow-x-auto lg:overflow-x-visible">
            <a href="#now" class="px-2 py-1.5 rounded hover:bg-[#222533] text-white whitespace-nowrap transition-all duration-300 hover:scale-105">🎬 Đang chiếu</a>
            <a href="#coming" class="px-2 py-1.5 rounded hover:bg-[#222533] text-white whitespace-nowrap transition-all duration-300 hover:scale-105">⏳ Sắp chiếu</a>
            <a href="#hot" class="px-2 py-1.5 rounded hover:bg-[#222533] text-white whitespace-nowrap transition-all duration-300 hover:scale-105">🔥 Phim hot</a>
          </nav>
        </div>
      </aside>
      
      <!-- Hero Carousel - Responsive -->
      <div class="flex-1 relative h-[60vh] sm:h-[70vh] lg:h-[80vh] xl:h-[85vh] overflow-hidden rounded-xl order-1 lg:order-2">
        <div class="hero-carousel relative h-full">
          @foreach($featuredMovies as $index => $movie)
          <!-- Slide {{ $index + 1 }} -->
          <div class="hero-slide absolute inset-0 bg-gradient-to-r from-black/95 via-black/70 to-transparent z-10 {{ $index === 0 ? '' : 'opacity-0' }}">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat hero-bg" 
                 style="background-image: url('{{ str_replace('/w342/', '/w1920/', $movie->poster) }}');"></div>
            <div class="relative z-20 h-full flex flex-col justify-between">
              <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full h-full flex flex-col justify-between">
                <!-- Top Section - Title Only -->
                <div class="pt-8">
                  <div class="max-w-2xl lg:max-w-3xl">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-white mb-1 leading-tight">
                      {{ $movie->ten_phim }}
                    </h1>
                    <h2 class="text-sm sm:text-base md:text-lg text-green-400 font-medium">
                      {{ $movie->dao_dien }}
                    </h2>
                  </div>
                </div>
                
                <!-- Bottom Section - All Info -->
                <div class="pb-8">
                  <div class="max-w-2xl lg:max-w-3xl">
                    <!-- Metadata Buttons -->
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                      <button class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-white/30 transition-all duration-300">
                        {{ $movie->do_dai }} phút
                      </button>
                      <button class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-white/30 transition-all duration-300">
                        {{ $movie->created_at->format('Y') }}
                      </button>
                      <button class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-white/30 transition-all duration-300">
                        Phim mới
                      </button>
                      @if($movie->trailer)
                      <a href="{{ $movie->trailer }}" target="_blank" class="bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:scale-105 transition-all duration-300">
                        Trailer
                      </a>
                      @endif
                    </div>
                    
                    <!-- Genre -->
                    <div class="mb-3">
                      <span class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                        {{ $movie->dien_vien }}
                      </span>
                    </div>
                    
                    <!-- Synopsis -->
                    <p class="text-xs sm:text-sm md:text-base text-white/90 mb-4 leading-relaxed max-w-xl">
                      {{ Str::limit($movie->mo_ta, 200) }}
                    </p>
                    
                    <!-- Play Button -->
                    <div class="flex items-center gap-4">
                      <a href="{{ route('movie-detail', $movie->id) }}" class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center hover:scale-110 transition-all duration-300 shadow-lg hover:shadow-green-500/25">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M8 5v14l11-7z"/>
                        </svg>
                      </a>
                      <div class="text-white/80 text-xs">
                        <div class="font-medium">Xem ngay</div>
                        <div class="text-xs opacity-70">Phim mới nhất</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
          
          <!-- Slide 2 -->
          <div class="hero-slide absolute inset-0 bg-gradient-to-r from-black/95 via-black/70 to-transparent z-10 opacity-0">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat hero-bg" 
                 style="background-image: url('https://image.tmdb.org/t/p/w1920/62HCnUTziyWcpDaBO2i1DX17ljH.jpg');"></div>
            <div class="relative z-20 h-full flex flex-col justify-between">
              <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full h-full flex flex-col justify-between">
                <!-- Top Section - Title Only -->
                <div class="pt-8">
                  <div class="max-w-2xl lg:max-w-3xl">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-white mb-1 leading-tight">
                      Săn Lùng Siêu Trộm
                    </h1>
                    <h2 class="text-sm sm:text-base md:text-lg text-green-400 font-medium">
                      The Ultimate Heist
                    </h2>
                  </div>
                </div>
                
                <!-- Bottom Section - All Info -->
                <div class="pb-8">
                  <div class="max-w-2xl lg:max-w-3xl">
                    <!-- Metadata Buttons -->
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                      <button class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-white/30 transition-all duration-300">
                        IMDb 7.8
                      </button>
                      <button class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-white/30 transition-all duration-300">
                        2024
                      </button>
                      <button class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-white/30 transition-all duration-300">
                        Phần 1
                      </button>
                      <button class="bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:scale-105 transition-all duration-300">
                        Trailer
                      </button>
                    </div>
                    
                    <!-- Genre -->
                    <div class="mb-3">
                      <span class="bg-gradient-to-r from-red-500 to-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                        Hành động, Tội phạm
                      </span>
                    </div>
                    
                    <!-- Synopsis -->
                    <p class="text-xs sm:text-sm md:text-base text-white/90 mb-4 leading-relaxed max-w-xl">
                      Cuộc truy đuổi gay cấn giữa cảnh sát và tên trộm thông minh nhất thế giới. Khi một vụ cướp ngân hàng lớn xảy ra, cảnh sát phải sử dụng mọi kỹ năng để bắt được kẻ thủ phạm.
                    </p>
                    
                    <!-- Play Button -->
                    <div class="flex items-center gap-4">
                      <button class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center hover:scale-110 transition-all duration-300 shadow-lg hover:shadow-green-500/25">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M8 5v14l11-7z"/>
                        </svg>
                      </button>
                      <div class="text-white/80 text-xs">
                        <div class="font-medium">Xem ngay</div>
                        <div class="text-xs opacity-70">Phim mới nhất</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Thumbnail Carousel - Bottom Right -->
          <div class="absolute bottom-6 right-6 z-30 hidden lg:block">
            <div class="flex gap-3" id="thumbnail-carousel">
              <!-- Thumbnails will be generated dynamically -->
            </div>
          </div>
          
          <!-- Carousel Navigation - Responsive -->
          <div class="absolute bottom-4 sm:bottom-6 lg:bottom-8 left-1/2 transform -translate-x-1/2 z-30">
            <div class="flex gap-2" id="carousel-dots">
              <!-- Dots will be generated dynamically -->
            </div>
          </div>
          
          <!-- Navigation Arrows - Desktop Only -->
          <button class="carousel-nav carousel-prev absolute left-4 top-1/2 transform -translate-y-1/2 z-30 hidden lg:flex items-center justify-center w-12 h-12 bg-black/30 hover:bg-black/50 backdrop-blur-sm rounded-full text-white transition-all duration-300 hover:scale-110">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </button>
          <button class="carousel-nav carousel-next absolute right-4 top-1/2 transform -translate-y-1/2 z-30 hidden lg:flex items-center justify-center w-12 h-12 bg-black/30 hover:bg-black/50 backdrop-blur-sm rounded-full text-white transition-all duration-300 hover:scale-110">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Bộ lọc phim và tìm kiếm -->
  <section class="flex flex-col gap-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
      <form class="flex gap-2 flex-wrap" id="filterForm" onsubmit="showFilterLoading(event)">
        <div class="relative flex items-center">
          <span class="absolute left-2 text-sm">🏢</span>
          <select class="pl-6 pr-2 py-1.5 rounded border border-[#262833] bg-[#222533] text-white text-xs">
            <option>Chọn rạp</option>
            <option>CGV</option>
            <option>BHD</option>
            <option>Lotte</option>
          </select>
        </div>
        <div class="relative flex items-center">
          <span class="absolute left-2 text-sm">🎬</span>
          <select class="pl-6 pr-2 py-1.5 rounded border border-[#262833] bg-[#222533] text-white text-xs">
            <option>Thể loại</option>
            <option>Hành động</option>
            <option>Tình cảm</option>
            <option>Kinh dị</option>
          </select>
        </div>
        <div class="relative flex items-center">
          <span class="absolute left-2 text-sm">🕒</span>
          <select class="pl-6 pr-2 py-1.5 rounded border border-[#262833] bg-[#222533] text-white text-xs">
            <option>Thời gian</option>
            <option>Hôm nay</option>
            <option>Cuối tuần</option>
          </select>
        </div>
        <button type="submit" class="px-3 py-1.5 rounded bg-gradient-to-r from-[#F53003] to-orange-400 text-white font-semibold transition-all duration-300 hover:scale-105 flex items-center gap-1 text-xs">
          <span>Lọc</span>
          <span id="filterSpinner" class="hidden animate-spin ml-1 w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
        </button>
      </form>
      <form class="flex items-center gap-2" id="searchForm" onsubmit="showSearchLoading(event)">
        <input type="text" placeholder="Tìm phim..." class="px-3 py-1.5 rounded border border-[#262833] bg-[#222533] text-white w-40 text-xs">
        <button type="submit" class="px-3 py-1.5 rounded bg-gradient-to-r from-[#F53003] to-orange-400 text-white font-semibold transition-all duration-300 hover:scale-105 flex items-center gap-1 text-xs">
          <span>Tìm</span>
          <span id="searchSpinner" class="hidden animate-spin ml-1 w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
        </button>
      </form>
      <script>
        function showFilterLoading(e) {
          e.preventDefault();
          document.getElementById('filterSpinner').classList.remove('hidden');
          setTimeout(function(){
            document.getElementById('filterSpinner').classList.add('hidden');
            document.getElementById('filterForm').submit();
          }, 1200);
        }
        function showSearchLoading(e) {
          e.preventDefault();
          document.getElementById('searchSpinner').classList.remove('hidden');
          setTimeout(function(){
            document.getElementById('searchSpinner').classList.add('hidden');
            document.getElementById('searchForm').submit();
          }, 1200);
        }
      </script>
    </div>
    

    <div id="now" class="flex items-center justify-between">
      <h2 class="text-lg font-semibold">Phim đang chiếu</h2>
      <a href="#coming" class="text-xs text-[#F53003] hover:underline">Xem phim sắp chiếu</a>
    </div>

  <div id="movies-container" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
      @if(isset($movies) && $movies->count() > 0)
        @foreach ($movies as $movie)
        <div class="movie-card bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden flex flex-col relative group transition-all duration-300 hover:shadow-[0_8px_32px_0_rgba(245,48,3,0.15)] hover:scale-105">
          <div class="relative">
            <!-- Movie Badges -->
            <div class="absolute top-2 left-2 z-20 flex flex-col gap-1">
              @if($loop->first)
                <span class="bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-1.5 py-0.5 rounded-full text-xs font-bold animate-pulse">🔥 Phim hot</span>
              @elseif($loop->index < 3)
                <span class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-1.5 py-0.5 rounded-full text-xs font-bold">🎬 Mới chiếu</span>
              @else
                <span class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-1.5 py-0.5 rounded-full text-xs font-bold">💎 VIP Only</span>
              @endif
            </div>
            
            <!-- Rating Badge -->
            <div class="absolute top-2 right-2 z-20">
              <div class="bg-black/70 backdrop-blur-sm rounded-lg px-1.5 py-0.5 flex items-center gap-1">
                <span class="text-yellow-400 text-xs">⭐</span>
                <span class="text-white text-xs font-bold">{{ number_format(8 + ($movie->id % 10) / 10, 1) }}</span>
              </div>
            </div>
            
            <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" class="movie-img w-full aspect-[2/3] object-cover transition-all duration-300" loading="lazy" onerror="this.onerror=null;this.src='/images/coming-soon.png';">
            
            <!-- Hover Overlay với Trailer Preview -->
            <div class="movie-overlay absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 flex flex-col justify-center items-center text-white transition-all duration-300">
              <div class="text-center mb-3">
                @if($movie->trailer)
                <a href="{{ $movie->trailer }}" target="_blank" class="play-trailer-btn w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white/30 transition-all duration-300 mb-2">
                  <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </a>
                @else
                <button class="play-trailer-btn w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white/30 transition-all duration-300 mb-2">
                  <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </button>
                @endif
                <div class="text-xs text-white/80">Xem trailer</div>
              </div>
              <div class="space-y-1 text-xs">
                <div class="flex items-center gap-1"><span>⭐</span> IMDb: {{ number_format(8 + ($movie->id % 10) / 10, 1) }}</div>
                <div class="flex items-center gap-1"><span>🎭</span> Đạo diễn: {{ $movie->dao_dien }}</div>
                <div class="flex items-center gap-1"><span>🕒</span> Thời lượng: {{ $movie->do_dai }} phút</div>
                <div class="flex items-center gap-1"><span>👥</span> Diễn viên: {{ Str::limit($movie->dien_vien, 20) }}</div>
              </div>
            </div>
          </div>
          
          <div class="p-3 flex-1 flex flex-col gap-2">
            <div>
              <h3 class="font-semibold text-sm mb-1">{{ $movie->ten_phim }}</h3>
              <div class="flex items-center gap-3 text-xs text-[#a6a6b0] mb-2">
                <span class="flex items-center gap-1">
                  <span>⏳</span>
                  <span>{{ $movie->do_dai }} phút</span>
                </span>
                <span class="flex items-center gap-1">
                  <span>🎬</span>
                  <span>{{ $movie->created_at->format('Y') }}</span>
                </span>
                <span class="flex items-center gap-1">
                  <span>⭐</span>
                  <span>{{ number_format(8 + ($movie->id % 10) / 10, 1) }}</span>
                </span>
              </div>
              <p class="text-xs text-[#a6a6b0] mb-2">{{ Str::limit($movie->mo_ta, 80) }}</p>
            </div>
            
            <div class="mt-auto flex gap-1">
              <button type="button" class="btn-booking inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-[#F53003] text-white text-xs transition-all hover:scale-105" onclick="openBookingPopup({{ $movie->id }})">
                <span>🎫</span>
                <span>Đặt vé</span>
              </button>
              <a href="{{ route('movie-detail', $movie->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 rounded-md border border-[#2f3240] text-xs hover:bg-[#222533] transition-all">
                <span>📖</span>
                <span>Chi tiết</span>
              </a>
            </div>
          </div>
        </div>
      @endforeach
      @else
        <div class="col-span-full text-center py-8">
          <div class="text-white/60 text-sm mb-2">Không có phim nào đang chiếu</div>
          <div class="text-white/40 text-xs">Vui lòng quay lại sau</div>
        </div>
      @endif
    </div>
    
    <!-- Loading indicator for infinite scroll -->
    <div id="loading-indicator" class="hidden flex justify-center items-center py-6">
      <div class="flex items-center gap-2">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#F53003]"></div>
        <span class="text-white/70 text-sm">Đang tải thêm phim...</span>
      </div>
    </div>
    
    <!-- Load more button (fallback) -->
    <div class="flex justify-center mt-6">
      <button id="load-more-btn" class="bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-6 py-2 rounded-lg font-semibold hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-[#F53003]/25 text-sm">
        📽️ Xem thêm phim
      </button>
    </div>
  </section>

  <!-- Footer -->
<!-- Footer -->
<footer class="mt-20 border-t border-white/10 bg-gradient-to-b from-gray-900 via-black to-black">
  <div class="w-full px-4 py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
      <!-- Brand section -->
      <div class="lg:col-span-1">
        <div class="flex items-center gap-3 mb-6">
          <img src="/logo.svg" width="32" height="32" alt="MovieHub Logo"/>
          <span class="text-2xl font-bold text-white">MovieHub</span>
        </div>
        <p class="text-white/70 mb-6 leading-relaxed">
          Trải nghiệm điện ảnh tuyệt vời với công nghệ đặt vé hiện đại. 
          Chọn phim, chọn ghế, thanh toán nhanh chóng.
        </p>
        <!-- Social links -->
        <div class="flex gap-4">
          <a href="#" class="social-link group w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-[#4267B2] hover:scale-110 transition-all duration-300 hover:shadow-lg hover:shadow-[#4267B2]/25 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-[#4267B2] to-[#1877F2] opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <svg class="w-6 h-6 text-white group-hover:text-white transition-all duration-300 relative z-10" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.6 0 0 .6 0 1.326v21.348C0 23.4.6 24 1.326 24H12.82v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.104C23.4 24 24 23.4 24 22.674V1.326C24 .6 23.4 0 22.675 0"></path></svg>
          </a>
          <a href="#" class="social-link group w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-gradient-to-r hover:from-pink-500 hover:to-purple-600 hover:scale-110 transition-all duration-300 hover:shadow-lg hover:shadow-pink-500/25 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-pink-500 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <svg class="w-6 h-6 text-white group-hover:text-white transition-all duration-300 relative z-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.334 3.608 1.308.975.974 1.246 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.334 2.633-1.308 3.608-.974.975-2.242 1.246-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.334-3.608-1.308-.975-.974-1.246-2.242-1.308-3.608C2.175 15.647 2.163 15.267 2.163 12s.012-3.584.07-4.85c.062-1.366.334-2.633 1.308-3.608C4.516 2.567 5.784 2.296 7.15 2.234 8.416 2.176 8.796 2.163 12 2.163zm0-2.163C8.741 0 8.332.013 7.052.072 5.771.131 4.659.363 3.678 1.344c-.98.98-1.213 2.092-1.272 3.373C2.013 5.741 2 6.151 2 12c0 5.849.013 6.259.072 7.539.059 1.281.292 2.393 1.272 3.373.98.98 2.092 1.213 3.373 1.272C8.332 23.987 8.741 24 12 24s3.668-.013 4.948-.072c1.281-.059 2.393-.292 3.373-1.272.98-.98 1.213-2.092 1.272-3.373.059-1.28.072-1.69.072-7.539 0-5.849-.013-6.259-.072-7.539-.059-1.281-.292-2.393-1.272-3.373-.98-.98-2.092-1.213-3.373-1.272C15.668.013 15.259 0 12 0z"></path><circle cx="12" cy="12" r="3.5"></circle></svg>
          </a>
          <a href="#" class="social-link group w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-blue-500 hover:scale-110 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/25 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <svg class="w-6 h-6 text-white group-hover:text-white transition-all duration-300 relative z-10" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557a9.93 9.93 0 01-2.828.775 4.932 4.932 0 002.165-2.724c-.951.555-2.005.959-3.127 1.184A4.916 4.916 0 0016.616 3c-2.717 0-4.92 2.206-4.92 4.917 0 .386.044.762.127 1.124C7.691 8.816 4.066 6.864 1.64 3.94c-.423.722-.666 1.561-.666 2.475 0 1.708.87 3.216 2.188 4.099a4.904 4.904 0 01-2.229-.616c-.054 2.281 1.581 4.415 3.949 4.89a4.936 4.936 0 01-2.224.084c.627 1.956 2.444 3.377 4.6 3.417A9.867 9.867 0 010 21.543a13.94 13.94 0 007.548 2.212c9.058 0 14.009-7.513 14.009-14.009 0-.213-.005-.425-.014-.636A10.025 10.025 0 0024 4.557z"></path></svg>
          </a>
          <a href="#" class="social-link group w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-red-600 hover:scale-110 transition-all duration-300 hover:shadow-lg hover:shadow-red-500/25 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-red-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <svg class="w-6 h-6 text-white group-hover:text-white transition-all duration-300 relative z-10" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a2.998 2.998 0 00-2.115-2.115C19.09 3.5 12 3.5 12 3.5s-7.09 0-9.383.571a2.998 2.998 0 00-2.115 2.115C0 8.48 0 12 0 12s0 3.52.502 5.814a2.998 2.998 0 002.115 2.115C4.91 20.5 12 20.5 12 20.5s7.09 0 9.383-.571a2.998 2.998 0 002.115-2.115C24 15.52 24 12 24 12s0-3.52-.502-5.814zM9.545 15.568V8.432l6.545 3.568-6.545 3.568z"></path></svg>
          </a>
        </div>
      </div>
      <!-- Quick Links -->
      <div>
        <h3 class="text-white font-semibold text-lg mb-6">Liên kết nhanh</h3>
        <ul class="space-y-3">
          <li><a href="/movies" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Tất cả phim</a></li>
          <li><a href="/checkout" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Giỏ vé</a></li>
          <li><a href="/login" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Đăng nhập</a></li>
          <li><a href="#" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Tin tức phim</a></li>
          <li><a href="#" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Khuyến mãi</a></li>
        </ul>
      </div>
      <!-- Support -->
      <div>
        <h3 class="text-white font-semibold text-lg mb-6">Hỗ trợ</h3>
        <ul class="space-y-3">
          <li><a href="#" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Trung tâm trợ giúp</a></li>
          <li><a href="#" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Điều khoản sử dụng</a></li>
          <li><a href="#" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Chính sách bảo mật</a></li>
          <li><a href="#" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Liên hệ</a></li>
          <li><a href="#" class="text-white/70 hover:text-orange-400 transition-colors duration-300">Phản hồi</a></li>
        </ul>
      </div>
      <!-- Contact & Download -->
      <div>
        <h3 class="text-white font-semibold text-lg mb-6">Liên hệ & Tải app</h3>
        <div class="space-y-3 mb-6">
          <div class="flex items-center gap-3 text-white/70">
            <!-- MapPin SVG -->
            <svg class="w-4 h-4 text-[#F53003]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4.5 8-10a8 8 0 10-16 0c0 5.5 8 10 8 10z"/></svg>
            <span class="text-sm">123 Đường ABC, Quận 1, TP.HCM</span>
          </div>
          <div class="flex items-center gap-3 text-white/70">
            <!-- Phone SVG -->
            <svg class="w-4 h-4 text-[#F53003]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a2 2 0 011.94 1.515l.72 2.88a2 2 0 01-.45 1.95l-1.27 1.27a16.001 16.001 0 006.586 6.586l1.27-1.27a2 2 0 011.95-.45l2.88.72A2 2 0 0121 18.72V22a2 2 0 01-2 2h-1C7.163 24 0 16.837 0 8V7a2 2 0 012-2z"/></svg>
            <span class="text-sm">1900 1234</span>
          </div>
          <div class="flex items-center gap-3 text-white/70">
            <!-- Mail SVG -->
            <svg class="w-4 h-4 text-[#F53003]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12l-4-4-4 4m8 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v4"/></svg>
            <span class="text-sm">support@moviehub.com</span>
          </div>
        </div>
        <div class="space-y-3">
          <h4 class="text-white font-medium">Tải ứng dụng</h4>
          <div class="flex gap-2">
            <a href="#" class="flex items-center gap-2 bg-white/10 hover:bg-white/20 px-3 py-2 rounded-lg shadow hover:shadow-lg transition-colors duration-300">
              <img src="/images/appstore.png" alt="App Store" class="w-6 h-6 rounded-md">
              <span class="text-xs">App Store</span>
            </a>
            <a href="#" class="flex items-center gap-2 bg-white/10 hover:bg-white/20 px-3 py-2 rounded-lg shadow hover:shadow-lg transition-colors duration-300">
              <img src="/images/googleplay.png" alt="Google Play" class="w-6 h-6 rounded-md">
              <span class="text-xs">Google Play</span>
            </a>
          </div>
        </div>
      </div>
      
      <!-- Newsletter Section -->
      <div class="lg:col-span-4 mt-8">
        <div class="bg-gradient-to-r from-[#F53003] to-orange-400 rounded-xl p-8 text-center">
          <div class="max-w-md mx-auto">
            <h3 class="text-2xl font-bold text-white mb-4 flex items-center justify-center gap-2">
              <span>📧</span>
              <span>Đăng ký nhận tin</span>
            </h3>
            <p class="text-white/90 mb-6">
              Nhận thông tin về phim mới, khuyến mãi đặc biệt và sự kiện độc quyền
            </p>
            <form class="newsletter-form flex gap-3" onsubmit="handleNewsletterSubmit(event)">
              <input 
                type="email" 
                placeholder="Nhập email của bạn..." 
                class="flex-1 px-4 py-3 rounded-lg border-0 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white/50"
                required
              >
              <button 
                type="submit" 
                class="px-6 py-3 bg-white text-[#F53003] font-semibold rounded-lg hover:bg-gray-100 transition-all duration-300 hover:scale-105 shadow-lg"
              >
                Đăng ký
              </button>
            </form>
            <p class="text-white/70 text-sm mt-4">
              Chúng tôi cam kết không spam. Hủy đăng ký bất cứ lúc nào.
            </p>
          </div>
        </div>
      </div>
    </div>
    <!-- Bottom bar -->
    <div class="border-t border-white/10 pt-8">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-white/60 text-sm">
          &copy; {{ date('Y') }} MovieHub. All rights reserved.
        </p>
        <div class="flex items-center gap-6 text-sm text-white/60">
          <span>Đối tác:</span>
          <div class="flex items-center gap-4">
            <span class="partner-badge text-xs bg-gradient-to-r from-red-500 to-red-600 text-white px-3 py-1.5 rounded-full font-semibold border border-red-400/30">CGV</span>
            <span class="partner-badge text-xs bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-1.5 rounded-full font-semibold border border-blue-400/30">Lotte</span>
            <span class="partner-badge text-xs bg-gradient-to-r from-yellow-500 to-yellow-600 text-black px-3 py-1.5 rounded-full font-semibold border border-yellow-400/30">Galaxy</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>

  <!-- Popup đặt vé -->
  <div id="booking-popup" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div class="bg-[#1b1d24] rounded-xl p-6 w-full max-w-md relative">
      <button onclick="closeBookingPopup()" class="absolute top-2 right-2 text-white text-xl">&times;</button>
      <h3 class="font-semibold text-lg mb-4">Chọn rạp, giờ chiếu, ghế</h3>
      <form>
        <div class="mb-3">
          <label class="block mb-1">Rạp</label>
          <select class="w-full px-3 py-2 rounded border border-[#262833] bg-[#222533] text-white">
            <option>CGV</option>
            <option>BHD</option>
            <option>Lotte</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="block mb-1">Giờ chiếu</label>
          <select class="w-full px-3 py-2 rounded border border-[#262833] bg-[#222533] text-white">
            <option>17:00</option>
            <option>19:00</option>
            <option>21:00</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="block mb-1">Ghế</label>
          <input type="text" class="w-full px-3 py-2 rounded border border-[#262833] bg-[#222533] text-white" placeholder="A1, A2...">
        </div>
        <button type="submit" class="w-full px-4 py-2 rounded bg-[#F53003] text-white mt-2">Xác nhận đặt vé</button>
      </form>
    </div>
  </div>

  <script>
    // Hero Carousel functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.carousel-dot');
    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    const thumbnailContainer = document.getElementById('thumbnail-carousel');
    let autoSlideInterval;
    
    // Banner data for thumbnails
    const bannerData = [
      {
        id: 1,
        title: 'Hành Tinh Bí Ẩn',
        titleEn: 'The Mysterious Planet',
        poster: 'https://image.tmdb.org/t/p/w342/2CAL2433ZeIihfX1Hb2139CX0pW.jpg',
        background: 'https://image.tmdb.org/t/p/w1920/2CAL2433ZeIihfX1Hb2139CX0pW.jpg',
        rating: '8.5',
        year: '2024',
        genre: 'Khoa học viễn tưởng, Hành động',
        description: 'Cuộc phiêu lưu vũ trụ đầy kịch tính với những hiệu ứng hình ảnh tuyệt đẹp và cốt truyện hấp dẫn.'
      },
      {
        id: 2,
        title: 'Săn Lùng Siêu Trộm',
        titleEn: 'The Ultimate Heist',
        poster: 'https://image.tmdb.org/t/p/w342/62HCnUTziyWcpDaBO2i1DX17ljH.jpg',
        background: 'https://image.tmdb.org/t/p/w1920/62HCnUTziyWcpDaBO2i1DX17ljH.jpg',
        rating: '7.8',
        year: '2024',
        genre: 'Hành động, Tội phạm',
        description: 'Cuộc truy đuổi gay cấn giữa cảnh sát và tên trộm thông minh nhất thế giới.'
      },
      {
        id: 3,
        title: 'Cuộc Phiêu Lưu Kỳ Thú',
        titleEn: 'The Amazing Adventure',
        poster: 'https://image.tmdb.org/t/p/w342/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg',
        background: 'https://image.tmdb.org/t/p/w1920/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg',
        rating: '8.2',
        year: '2024',
        genre: 'Phiêu lưu, Gia đình',
        description: 'Cuộc hành trình đầy thú vị của một gia đình trong thế giới kỳ diệu.'
      },
      {
        id: 4,
        title: 'Bí Ẩn Rừng Xanh',
        titleEn: 'Mystery of the Green Forest',
        poster: 'https://image.tmdb.org/t/p/w342/9Rq14Eyrf7Tu1xk0Pl7VcNbNh1n.jpg',
        background: 'https://image.tmdb.org/t/p/w1920/9Rq14Eyrf7Tu1xk0Pl7VcNbNh1n.jpg',
        rating: '7.5',
        year: '2024',
        genre: 'Bí ẩn, Kinh dị',
        description: 'Khám phá những bí mật đáng sợ ẩn giấu trong rừng xanh bí ẩn.'
      }
    ];
    
    // Generate thumbnails dynamically
    function generateThumbnails() {
      if (!thumbnailContainer) return;
      
      thumbnailContainer.innerHTML = '';
      
      bannerData.forEach((movie, index) => {
        const thumbnail = document.createElement('div');
        thumbnail.className = `thumbnail-item w-16 h-10 bg-white/20 backdrop-blur-sm rounded-lg overflow-hidden cursor-pointer hover:scale-110 transition-all duration-300 border ${index === 0 ? 'border-white/50' : 'border-white/30'}`;
        thumbnail.setAttribute('data-slide', index);
        
        thumbnail.innerHTML = `
          <img src="${movie.poster}" alt="${movie.title}" class="w-full h-full object-cover" loading="lazy">
        `;
        
        thumbnailContainer.appendChild(thumbnail);
      });
    }
    
    // Generate dots dynamically
    function generateDots() {
      const dotsContainer = document.getElementById('carousel-dots');
      if (!dotsContainer) return;
      
      dotsContainer.innerHTML = '';
      
      bannerData.forEach((_, index) => {
        const dot = document.createElement('button');
        dot.className = `carousel-dot w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full hover:bg-white transition-all duration-300 hover:scale-125 ${index === 0 ? 'bg-white/50' : 'bg-white/30'}`;
        dot.setAttribute('data-slide', index);
        dotsContainer.appendChild(dot);
      });
    }
    
    function showSlide(index) {
      // Update slides (only show first 2 slides, others are dynamic)
      slides.forEach((slide, i) => {
        if (i < 2) {
          slide.style.opacity = i === index ? '1' : '0';
          slide.style.transform = i === index ? 'scale(1)' : 'scale(1.05)';
        }
      });
      
      // Update dots
      const dots = document.querySelectorAll('.carousel-dot');
      dots.forEach((dot, i) => {
        dot.classList.toggle('bg-white/50', i === index);
        dot.classList.toggle('bg-white/30', i !== index);
      });
      
      // Update thumbnails
      const thumbnails = document.querySelectorAll('.thumbnail-item');
      thumbnails.forEach((thumb, i) => {
        thumb.classList.toggle('border-white/50', i === index);
        thumb.classList.toggle('border-white/30', i !== index);
      });
      
      // Update banner content dynamically
      if (index < bannerData.length) {
        updateBannerContent(bannerData[index]);
      }
    }
    
    // Update banner content dynamically
    function updateBannerContent(movie) {
      const currentSlideElement = slides[currentSlide];
      if (!currentSlideElement) return;
      
      // Update background image
      const bgElement = currentSlideElement.querySelector('.hero-bg');
      if (bgElement) {
        bgElement.style.backgroundImage = `url('${movie.background}')`;
      }
      
      // Update title
      const titleElement = currentSlideElement.querySelector('h1');
      if (titleElement) {
        titleElement.textContent = movie.title;
      }
      
      // Update subtitle
      const subtitleElement = currentSlideElement.querySelector('h2');
      if (subtitleElement) {
        subtitleElement.textContent = movie.titleEn;
      }
      
      // Update rating
      const ratingElements = currentSlideElement.querySelectorAll('button');
      if (ratingElements[0]) {
        ratingElements[0].textContent = `IMDb ${movie.rating}`;
      }
      
      // Update year
      if (ratingElements[1]) {
        ratingElements[1].textContent = movie.year;
      }
      
      // Update genre
      const genreElement = currentSlideElement.querySelector('span.bg-gradient-to-r');
      if (genreElement) {
        genreElement.textContent = movie.genre;
      }
      
      // Update description
      const descElement = currentSlideElement.querySelector('p');
      if (descElement) {
        descElement.textContent = movie.description;
      }
    }
    
    function nextSlide() {
      currentSlide = (currentSlide + 1) % bannerData.length;
      showSlide(currentSlide);
    }
    
    function prevSlide() {
      currentSlide = (currentSlide - 1 + bannerData.length) % bannerData.length;
      showSlide(currentSlide);
    }
    
    function startAutoSlide() {
      autoSlideInterval = setInterval(nextSlide, 6000);
    }
    
    function stopAutoSlide() {
      clearInterval(autoSlideInterval);
    }
    
    // Initialize carousel
    generateThumbnails();
    generateDots();
    showSlide(currentSlide);
    startAutoSlide();
    
    // Dot navigation
    function setupDotListeners() {
      const dots = document.querySelectorAll('.carousel-dot');
      dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
          currentSlide = index;
          showSlide(currentSlide);
          stopAutoSlide();
          startAutoSlide();
        });
      });
    }
    
    // Setup dot listeners after generation
    setTimeout(setupDotListeners, 100);
    
    // Arrow navigation
    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        nextSlide();
        stopAutoSlide();
        startAutoSlide();
      });
    }
    
    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        prevSlide();
        stopAutoSlide();
        startAutoSlide();
      });
    }
    
    // Pause auto-slide on hover
    const carousel = document.querySelector('.hero-carousel');
    if (carousel) {
      carousel.addEventListener('mouseenter', stopAutoSlide);
      carousel.addEventListener('mouseleave', startAutoSlide);
    }
    
    // Touch/swipe support for mobile
    let startX = 0;
    let endX = 0;
    
    if (carousel) {
      carousel.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        stopAutoSlide();
      });
      
      carousel.addEventListener('touchend', (e) => {
        endX = e.changedTouches[0].clientX;
        const diff = startX - endX;
        
        if (Math.abs(diff) > 50) { // Minimum swipe distance
          if (diff > 0) {
            nextSlide();
          } else {
            prevSlide();
          }
        }
        startAutoSlide();
      });
    }
    
    // Thumbnail carousel functionality
    function setupThumbnailListeners() {
      const thumbnails = document.querySelectorAll('.thumbnail-item');
      thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', () => {
          currentSlide = index;
          showSlide(currentSlide);
          stopAutoSlide();
          startAutoSlide();
        });
      });
    }
    
    // Setup thumbnail listeners after generation
    setTimeout(setupThumbnailListeners, 100);
    
    // Countdown Timer
    function updateCountdown() {
      const now = new Date().getTime();
      const targetDate = new Date().getTime() + (3 * 24 * 60 * 60 * 1000) + (12 * 60 * 60 * 1000) + (45 * 60 * 1000) + (22 * 1000);
      const distance = targetDate - now;
      
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);
      
      document.getElementById('days').textContent = days.toString().padStart(2, '0');
      document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
      document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
      document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
      
      if (distance < 0) {
        clearInterval(countdownInterval);
        document.querySelector('.countdown-timer').innerHTML = '<div class="text-center text-white font-bold">🎬 Đã ra mắt!</div>';
      }
    }
    
    const countdownInterval = setInterval(updateCountdown, 1000);
    updateCountdown();
    
    // Parallax effect for hero background
    window.addEventListener('scroll', () => {
      const scrolled = window.pageYOffset;
      const parallax = document.querySelectorAll('.hero-bg');
      const speed = 0.3;
      
      parallax.forEach(element => {
        const yPos = -(scrolled * speed);
        element.style.transform = `translateY(${yPos}px) scale(1.05)`;
      });
    });
    
    // Add CSS for smooth transitions
    const style = document.createElement('style');
    style.textContent = `
      .hero-bg {
        transition: transform 20s ease-out;
        will-change: transform;
      }
      
      .hero-slide {
        transition: opacity 0.8s ease-in-out, transform 0.8s ease-in-out;
      }
      
      .carousel-dot {
        transition: all 0.3s ease;
      }
      
      .carousel-nav {
        transition: all 0.3s ease;
      }
      
      .glow-button {
        position: relative;
        overflow: hidden;
      }
      
      .glow-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
      }
      
      .glow-button:hover::before {
        left: 100%;
      }
      
      @media (max-width: 640px) {
        .hero-slide h1 {
          line-height: 1.1;
        }
        
        .countdown-timer {
          font-size: 0.75rem;
        }
      }
    `;
    document.head.appendChild(style);
    
    // Infinite Scroll functionality
    let currentPage = 1;
    let isLoading = false;
    const moviesContainer = document.getElementById('movies-container');
    const loadingIndicator = document.getElementById('loading-indicator');
    const loadMoreBtn = document.getElementById('load-more-btn');
    
    // Additional movies data for infinite scroll
    const additionalMovies = [
      ['id'=>7,'title'=>'Cuộc Phiêu Lưu Kỳ Thú','poster'=>'https://image.tmdb.org/t/p/w342/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg','duration'=>135,'rating'=>'T13'],
      ['id'=>8,'title'=>'Bí Ẩn Rừng Xanh','poster'=>'https://image.tmdb.org/t/p/w342/9Rq14Eyrf7Tu1xk0Pl7VcNbNh1n.jpg','duration'=>108,'rating'=>'P'],
      ['id'=>9,'title'=>'Chiến Binh Ánh Sáng','poster'=>'https://image.tmdb.org/t/p/w342/6XYLiMxHAaCsoyrVo38LBWMw2p8.jpg','duration'=>125,'rating'=>'T16'],
      ['id'=>10,'title'=>'Huyền Thoại Biển Cả','poster'=>'https://image.tmdb.org/t/p/w342/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg','duration'=>98,'rating'=>'T13'],
      ['id'=>11,'title'=>'Cuộc Sống Tương Lai','poster'=>'https://image.tmdb.org/t/p/w342/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg','duration'=>112,'rating'=>'T16'],
      ['id'=>12,'title'=>'Tình Yêu Vĩnh Cửu','poster'=>'https://image.tmdb.org/t/p/w342/6XYLiMxHAaCsoyrVo38LBWMw2p8.jpg','duration'=>89,'rating'=>'P']
    ];
    
    function createMovieCard(movie) {
      const badgeClass = movie.id % 3 === 1 ? 'from-[#F53003] to-orange-400' : 
                        movie.id % 3 === 2 ? 'from-blue-500 to-blue-600' : 
                        'from-purple-500 to-purple-600';
      const badgeText = movie.id % 3 === 1 ? '🔥 Phim hot' : 
                       movie.id % 3 === 2 ? '🎬 Mới chiếu' : 
                       '💎 VIP Only';
      
      return `
        <div class="movie-card bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden flex flex-col relative group transition-all duration-300 hover:shadow-[0_8px_32px_0_rgba(245,48,3,0.15)] hover:scale-105">
          <div class="relative">
            <div class="absolute top-3 left-3 z-20 flex flex-col gap-2">
              <span class="bg-gradient-to-r ${badgeClass} text-white px-2 py-1 rounded-full text-xs font-bold ${movie.id % 3 === 1 ? 'animate-pulse' : ''}">${badgeText}</span>
            </div>
            <div class="absolute top-3 right-3 z-20">
              <div class="bg-black/70 backdrop-blur-sm rounded-lg px-2 py-1 flex items-center gap-1">
                <span class="text-yellow-400">⭐</span>
                <span class="text-white text-sm font-bold">8.${movie.id + 4}</span>
              </div>
            </div>
            <img src="${movie.poster}" alt="${movie.title}" class="movie-img w-full aspect-[2/3] object-cover transition-all duration-300" loading="lazy" onerror="this.onerror=null;this.src='/images/coming-soon.png';">
            <div class="movie-overlay absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 flex flex-col justify-center items-center text-white transition-all duration-300">
              <div class="text-center mb-4">
                <button class="play-trailer-btn w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white/30 transition-all duration-300 mb-4">
                  <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </button>
                <div class="text-sm text-white/80">Xem trailer 5s</div>
              </div>
              <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2"><span>⭐</span> IMDb: 8.${movie.id + 4}</div>
                <div class="flex items-center gap-2"><span>🎭</span> Thể loại: Hành động</div>
                <div class="flex items-center gap-2"><span>🕒</span> Suất chiếu: 19:00</div>
                <div class="flex items-center gap-2"><span>👥</span> Độ tuổi: ${movie.rating}</div>
              </div>
            </div>
          </div>
          <div class="p-4 flex-1 flex flex-col gap-3">
            <div>
              <h3 class="font-semibold text-lg mb-2">${movie.title}</h3>
              <div class="flex items-center gap-4 text-sm text-[#a6a6b0] mb-3">
                <span class="flex items-center gap-1">
                  <span>⏳</span>
                  <span>${movie.duration} phút</span>
                </span>
                <span class="flex items-center gap-1">
                  <span>🎬</span>
                  <span>${movie.rating}</span>
                </span>
                <span class="flex items-center gap-1">
                  <span>⭐</span>
                  <span>8.${movie.id + 4}</span>
                </span>
              </div>
            </div>
            <div class="mt-auto flex gap-2">
              <button type="button" class="btn-booking inline-flex items-center justify-center px-4 py-2 rounded-md bg-[#F53003] text-white text-sm transition-all hover:scale-105" onclick="openBookingPopup(${movie.id})">
                <span>🎫</span>
                <span>Đặt vé</span>
              </button>
              <a href="{{ route('movie-detail', $movie['id']) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-[#2f3240] text-sm hover:bg-[#222533] transition-all">
                <span>📖</span>
                <span>Chi tiết</span>
              </a>
            </div>
          </div>
        </div>
      `;
    }
    
    function loadMoreMovies() {
      if (isLoading) return;
      
      isLoading = true;
      loadingIndicator.classList.remove('hidden');
      
      // Simulate API call delay
      setTimeout(() => {
        const startIndex = (currentPage - 1) * 3;
        const endIndex = startIndex + 3;
        const newMovies = additionalMovies.slice(startIndex, endIndex);
        
        newMovies.forEach(movie => {
          const movieCard = document.createElement('div');
          movieCard.innerHTML = createMovieCard(movie);
          moviesContainer.appendChild(movieCard.firstElementChild);
        });
        
        currentPage++;
        isLoading = false;
        loadingIndicator.classList.add('hidden');
        
        // Hide load more button if no more movies
        if (currentPage * 3 >= additionalMovies.length) {
          loadMoreBtn.style.display = 'none';
        }
      }, 1500);
    }
    
    // Intersection Observer for infinite scroll
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !isLoading) {
          loadMoreMovies();
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '100px'
    });
    
    // Observe the load more button
    observer.observe(loadMoreBtn);
    
    // Load more button click handler
    loadMoreBtn.addEventListener('click', loadMoreMovies);
    
    // Newsletter form handler
    function handleNewsletterSubmit(event) {
      event.preventDefault();
      const form = event.target;
      const email = form.querySelector('input[type="email"]').value;
      const button = form.querySelector('button');
      
      // Show loading state
      button.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#F53003] mr-2"></div>Đang xử lý...';
      button.disabled = true;
      
      // Simulate API call
      setTimeout(() => {
        showNotification('Đăng ký thành công! Cảm ơn bạn đã quan tâm.', 'success');
        form.reset();
        button.innerHTML = 'Đăng ký';
        button.disabled = false;
      }, 2000);
    }
    
    // Social links animation
    document.querySelectorAll('.social-link').forEach(link => {
      link.addEventListener('mouseenter', () => {
        link.style.transform = 'scale(1.1) rotate(5deg)';
      });
      
      link.addEventListener('mouseleave', () => {
        link.style.transform = 'scale(1) rotate(0deg)';
      });
    });
    
    // Newsletter form animation
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        handleNewsletterSubmit(e);
      });
    }
    
    function openBookingPopup(id) {
      document.getElementById('booking-popup').style.display = 'flex';
    }
    function closeBookingPopup() {
      document.getElementById('booking-popup').style.display = 'none';
    }
  </script>
@endsection


