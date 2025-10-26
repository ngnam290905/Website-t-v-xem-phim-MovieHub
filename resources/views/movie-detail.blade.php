@extends('layouts.main')

@section('title', $movie->ten_phim . ' - MovieHub')

@section('meta')
  <meta name="description" content="{{ Str::limit($movie->mo_ta, 160) }} - Xem phim {{ $movie->ten_phim }} tại MovieHub với chất lượng HD và trải nghiệm tuyệt vời.">
  <meta name="keywords" content="{{ $movie->ten_phim }}, {{ $movie->dao_dien }}, phim hay, xem phim online, MovieHub">
  <meta name="author" content="MovieHub">
  
  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="video.movie">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:title" content="{{ $movie->ten_phim }} - MovieHub">
  <meta property="og:description" content="{{ Str::limit($movie->mo_ta, 160) }}">
  <meta property="og:image" content="{{ $movie->poster }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:site_name" content="MovieHub">
  
  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="{{ url()->current() }}">
  <meta property="twitter:title" content="{{ $movie->ten_phim }} - MovieHub">
  <meta property="twitter:description" content="{{ Str::limit($movie->mo_ta, 160) }}">
  <meta property="twitter:image" content="{{ $movie->poster }}">
  
  <!-- Schema.org structured data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Movie",
    "name": "{{ $movie->ten_phim }}",
    "description": "{{ $movie->mo_ta }}",
    "image": "{{ $movie->poster }}",
    "director": {
      "@type": "Person",
      "name": "{{ $movie->dao_dien }}"
    },
    "actor": "{{ $movie->dien_vien }}",
    "duration": "PT{{ $movie->do_dai }}M",
    "datePublished": "{{ $movie->created_at->format('Y-m-d') }}",
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "{{ number_format(8 + ($movie->id % 10) / 10, 1) }}",
      "bestRating": "10",
      "worstRating": "1"
    }
  }
  </script>
@endsection

@section('content')
  @php
    $reviews = [
      ['user' => 'Nguyễn Văn A', 'rating' => 5, 'comment' => 'Phim hay tuyệt vời! Hiệu ứng hình ảnh đẹp mắt, cốt truyện hấp dẫn. Đáng xem!', 'date' => '2024-01-20'],
      ['user' => 'Trần Thị B', 'rating' => 4, 'comment' => 'Phim khá hay, diễn viên diễn tốt. Chỉ có điều hơi dài một chút.', 'date' => '2024-01-19'],
      ['user' => 'Lê Văn C', 'rating' => 5, 'comment' => 'Tuyệt vời! Đây là một trong những phim hay nhất năm nay.', 'date' => '2024-01-18']
    ];
    
    // Dữ liệu suất chiếu sẽ được truyền từ controller
  @endphp

  <!-- Hero Section với Backdrop -->
  <section class="relative h-[60vh] md:h-[70vh] overflow-hidden mb-8 parallax-section">
    <!-- Background Image với Parallax Effect -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-fixed" 
         style="background-image: url('{{ str_replace('/w342/', '/w1920/', $movie->poster) }}'); filter: brightness(0.3);">
    </div>
    
    <!-- Enhanced Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/60 to-black/30"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
    
    <!-- Floating Elements (hidden on mobile) -->
    <div class="hidden md:block absolute top-20 left-10 w-2 h-2 bg-[#F53003] rounded-full animate-pulse"></div>
    <div class="hidden md:block absolute top-40 right-20 w-1 h-1 bg-orange-400 rounded-full animate-pulse delay-1000"></div>
    <div class="hidden md:block absolute bottom-40 left-20 w-1.5 h-1.5 bg-yellow-400 rounded-full animate-pulse delay-2000"></div>
    
    <div class="relative z-10 h-full flex items-center">
      <div class="max-w-7xl mx-auto px-4 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-center">
          <!-- Poster với Enhanced Effects -->
          <div class="lg:col-span-1 order-2 lg:order-1">
            <div class="relative group">
              <div class="absolute -inset-2 md:-inset-4 bg-gradient-to-r from-[#F53003] to-orange-400 rounded-2xl opacity-20 group-hover:opacity-40 transition-opacity duration-500 blur-xl"></div>
              <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" 
                   class="relative w-full max-w-xs md:max-w-sm mx-auto rounded-xl shadow-2xl transition-all duration-500 group-hover:scale-105 group-hover:rotate-1 movie-poster-mobile"
                   loading="lazy" decoding="async">
              
              <!-- Enhanced Play Button -->
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-all duration-500 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <button class="play-trailer-btn w-16 h-16 md:w-24 md:h-24 bg-gradient-to-r from-[#F53003] to-orange-400 rounded-full flex items-center justify-center hover:scale-110 transition-all duration-300 shadow-2xl hover:shadow-[#F53003]/50">
                  <svg class="w-8 h-8 md:w-12 md:h-12 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </button>
              </div>
              
              <!-- Rating Badge -->
              <div class="absolute -top-2 -right-2 md:-top-3 md:-right-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-2 py-1 md:px-3 md:py-1 rounded-full text-xs md:text-sm font-bold shadow-lg">
                ⭐ {{ number_format(8 + ($movie->id % 10) / 10, 1) }}
              </div>
            </div>
          </div>
          
          <!-- Enhanced Movie Info -->
          <div class="lg:col-span-2 text-white order-1 lg:order-2">
            <!-- Status Badges -->
            <div class="flex flex-wrap items-center gap-2 md:gap-3 mb-4 md:mb-6">
              <span class="bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-bold shadow-lg animate-pulse">🔥 Phim hot</span>
              <span class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-medium">IMDb {{ number_format(8 + ($movie->id % 10) / 10, 1) }}</span>
              <span class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-medium">T13</span>
              <span class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-medium">HD</span>
            </div>
            
            <!-- Movie Title với Animation -->
            <h1 class="text-3xl md:text-5xl lg:text-7xl font-black mb-4 md:mb-6 leading-tight bg-gradient-to-r from-white via-orange-100 to-yellow-100 bg-clip-text text-transparent animate-fade-in-up hero-title-mobile">
              {{ $movie->ten_phim }}
            </h1>
            
            <!-- Director -->
            <p class="text-lg md:text-xl text-white/90 mb-4 md:mb-6 font-medium animate-fade-in-up delay-200">
              <span class="text-[#F53003] font-bold">Đạo diễn:</span> {{ $movie->dao_dien }}
            </p>
            
            <!-- Movie Details với Icons -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 mb-6 md:mb-8 animate-fade-in-up delay-300">
              <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-3">
                <span class="text-xl md:text-2xl">📅</span>
                <div>
                  <p class="text-xs md:text-sm text-white/70">Ngày phát hành</p>
                  <p class="font-semibold text-sm md:text-base">{{ $movie->created_at->format('d/m/Y') }}</p>
                </div>
              </div>
              <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-3">
                <span class="text-xl md:text-2xl">⏱️</span>
                <div>
                  <p class="text-xs md:text-sm text-white/70">Thời lượng</p>
                  <p class="font-semibold text-sm md:text-base">{{ $movie->do_dai }} phút</p>
                </div>
              </div>
              <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-3">
                <span class="text-xl md:text-2xl">🎭</span>
                <div>
                  <p class="text-xs md:text-sm text-white/70">Diễn viên</p>
                  <p class="font-semibold text-xs md:text-sm">{{ Str::limit($movie->dien_vien, 15) }}</p>
                </div>
              </div>
            </div>
            
            <!-- Enhanced Description -->
            <div class="mb-6 md:mb-8 animate-fade-in-up delay-400">
              <p class="text-base md:text-lg text-white/90 leading-relaxed mb-4">{{ $movie->mo_ta }}</p>
              <button class="text-[#F53003] hover:text-orange-400 font-semibold transition-colors duration-300" onclick="toggleDescription()">
                <span id="read-more-text">Xem thêm</span>
                <svg class="inline w-4 h-4 ml-1 transition-transform duration-300" id="read-more-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
            </div>
            
            <!-- Enhanced Action Buttons -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 md:gap-4 animate-fade-in-up delay-500">
              <button class="group bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-6 py-3 md:px-10 md:py-4 rounded-xl font-bold text-base md:text-lg hover:scale-105 transition-all duration-300 shadow-2xl hover:shadow-[#F53003]/50 relative overflow-hidden">
                <span class="relative z-10 flex items-center justify-center gap-2">
                  <span class="text-lg md:text-xl">🎫</span>
                  <span>Đặt vé ngay</span>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-orange-400 to-[#F53003] opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </button>
              
              <button class="group border-2 border-white/30 text-white px-6 py-3 md:px-10 md:py-4 rounded-xl font-bold text-base md:text-lg hover:bg-white hover:text-black transition-all duration-300 backdrop-blur-sm hover:border-white relative overflow-hidden">
                <span class="relative z-10 flex items-center justify-center gap-2">
                  <span class="text-lg md:text-xl">🎬</span>
                  <span>Xem trailer</span>
                </span>
                <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </button>
              
              <button class="group bg-white/10 backdrop-blur-sm text-white px-4 py-3 md:px-6 md:py-4 rounded-xl font-bold text-base md:text-lg hover:bg-white/20 transition-all duration-300 border border-white/20">
                <span class="flex items-center justify-center gap-2">
                  <span class="text-lg md:text-xl">❤️</span>
                  <span class="hidden sm:inline">Yêu thích</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-3 gap-8">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-8">
      
      <!-- Trailer Section -->
      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
          <span>🎬</span>
          <span>Trailer chính thức</span>
        </h2>
        <div class="relative aspect-video rounded-lg overflow-hidden">
          <iframe 
            src="{{ $movie['trailer_url'] }}" 
            title="Trailer {{ $movie['title'] }}"
            class="w-full h-full"
            allowfullscreen>
          </iframe>
        </div>
      </div>
      
      <!-- Cast & Crew -->
      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
          <span>👥</span>
          <span>Diễn viên & Đạo diễn</span>
        </h2>
        
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-semibold mb-4 text-[#F53003]">Đạo diễn</h3>
            <div class="flex items-center gap-3 p-3 bg-[#222533] rounded-lg">
              <div class="w-12 h-12 bg-gradient-to-r from-[#F53003] to-orange-400 rounded-full flex items-center justify-center text-white font-bold">
                {{ substr($movie['director'], 0, 1) }}
              </div>
              <div>
                <p class="font-semibold">{{ $movie['director'] }}</p>
                <p class="text-sm text-[#a6a6b0]">Đạo diễn</p>
              </div>
            </div>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold mb-4 text-[#F53003]">Diễn viên chính</h3>
            <div class="space-y-3">
              @foreach($movie['cast'] as $actor)
                <div class="flex items-center gap-3 p-3 bg-[#222533] rounded-lg">
                  <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                    {{ substr($actor, 0, 1) }}
                  </div>
                  <div>
                    <p class="font-semibold">{{ $actor }}</p>
                    <p class="text-sm text-[#a6a6b0]">Diễn viên</p>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      
      <!-- Reviews Section -->
      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
          <span>⭐</span>
          <span>Đánh giá từ khán giả</span>
        </h2>
        
        <div class="space-y-4">
          @foreach($reviews as $review)
            <div class="bg-[#222533] rounded-lg p-4">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                    {{ substr($review['user'], 0, 1) }}
                  </div>
                  <div>
                    <p class="font-semibold">{{ $review['user'] }}</p>
                    <div class="flex items-center gap-1">
                      @for($i = 1; $i <= 5; $i++)
                        <span class="text-yellow-400">{{ $i <= $review['rating'] ? '★' : '☆' }}</span>
                      @endfor
                    </div>
                  </div>
                </div>
                <span class="text-sm text-[#a6a6b0]">{{ date('d/m/Y', strtotime($review['date'])) }}</span>
              </div>
              <p class="text-white/80">{{ $review['comment'] }}</p>
            </div>
          @endforeach
        </div>
        
        <!-- Add Review Form -->
        <div class="mt-6 p-4 bg-[#222533] rounded-lg">
          <h3 class="font-semibold mb-4">Viết đánh giá của bạn</h3>
          <form class="space-y-4">
            <div class="flex items-center gap-2">
              <span class="text-sm">Đánh giá:</span>
              <div class="flex gap-1" id="rating-stars">
                @for($i = 1; $i <= 5; $i++)
                  <button type="button" class="star-rating text-2xl text-gray-400 hover:text-yellow-400 transition-colors" data-rating="{{ $i }}">☆</button>
                @endfor
              </div>
            </div>
            <textarea 
              placeholder="Chia sẻ cảm nhận của bạn về bộ phim..." 
              class="w-full p-3 bg-[#1b1d24] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]"
              rows="4">
            </textarea>
            <button type="submit" class="bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-6 py-2 rounded-lg font-semibold hover:scale-105 transition-all duration-300">
              Gửi đánh giá
            </button>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
      
      <!-- Showtimes -->
      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6 sticky top-6">
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
          <span>🎫</span>
          <span>Suất chiếu hôm nay</span>
        </h3>
        
        <div class="space-y-3">
          @if(isset($suatChieu) && $suatChieu->count() > 0)
            @foreach($suatChieu as $suat)
              <div class="bg-[#222533] rounded-lg p-4 hover:bg-[#2a2d3a] transition-colors cursor-pointer">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="font-semibold text-lg">{{ $suat->thoi_gian_bat_dau->format('H:i') }}</p>
                    <p class="text-sm text-[#a6a6b0]">{{ $suat->phongChieu->ten_phong }}</p>
                    <p class="text-xs text-[#a6a6b0]">{{ $suat->thoi_gian_bat_dau->format('d/m/Y') }}</p>
                  </div>
                  <div class="text-right">
                    <p class="font-bold text-[#F53003]">{{ number_format(80000) }}đ</p>
                    <button class="text-sm bg-[#F53003] text-white px-3 py-1 rounded hover:bg-[#ff4d4d] transition-colors">
                      Đặt vé
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="text-center py-8">
              <p class="text-[#a6a6b0]">Không có suất chiếu nào</p>
            </div>
          @endif
        </div>
      </div>
      
      <!-- Enhanced Similar Movies Carousel -->
      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold flex items-center gap-2">
            <span class="text-2xl">🎬</span>
            <span>Phim tương tự</span>
          </h3>
          <div class="flex gap-2">
            <button class="carousel-prev p-2 rounded-lg bg-[#2a2d3a] hover:bg-[#3a3d4a] transition-colors">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button class="carousel-next p-2 rounded-lg bg-[#2a2d3a] hover:bg-[#3a3d4a] transition-colors">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>
        
        <div class="similar-movies-carousel overflow-hidden">
          <div class="similar-movies-slide flex gap-4 transition-transform duration-500" id="similar-movies-slide">
            @php
              $similarMovies = [
                ['title' => 'Cuộc Chiến Vũ Trụ', 'poster' => 'https://image.tmdb.org/t/p/w342/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg', 'rating' => 8.2, 'genre' => 'Hành động'],
                ['title' => 'Bí Mật Thời Gian', 'poster' => 'https://image.tmdb.org/t/p/w342/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg', 'rating' => 7.8, 'genre' => 'Khoa học viễn tưởng'],
                ['title' => 'Hành Trình Tình Yêu', 'poster' => 'https://image.tmdb.org/t/p/w342/6XYLiMxHAaCsoyrVo38LBWMw2p8.jpg', 'rating' => 8.0, 'genre' => 'Tình cảm'],
                ['title' => 'Chiến Binh Bóng Đêm', 'poster' => 'https://image.tmdb.org/t/p/w342/1hRoyzDtpgMU7Dz4JF22RANzQO7.jpg', 'rating' => 8.5, 'genre' => 'Hành động'],
                ['title' => 'Cuộc Phiêu Lưu Kỳ Diệu', 'poster' => 'https://image.tmdb.org/t/p/w342/6XYLiMxHAaCsoyrVo38LBWMw2p8.jpg', 'rating' => 7.9, 'genre' => 'Phiêu lưu']
              ];
            @endphp
            
            @foreach($similarMovies as $index => $similar)
              <div class="movie-card-hover flex-shrink-0 w-32 bg-[#222533] rounded-lg overflow-hidden hover:bg-[#2a2d3a] transition-all duration-300 cursor-pointer group">
                <div class="relative">
                  <img src="{{ $similar['poster'] }}" alt="{{ $similar['title'] }}" 
                       class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110"
                       loading="lazy" decoding="async">
                  <div class="absolute top-2 right-2 bg-black/70 text-white px-2 py-1 rounded text-xs font-bold">
                    ⭐ {{ $similar['rating'] }}
                  </div>
                  <div class="movie-info">
                    <h4 class="font-bold text-sm mb-1">{{ $similar['title'] }}</h4>
                    <p class="text-xs text-white/70 mb-2">{{ $similar['genre'] }}</p>
                    <button class="w-full bg-[#F53003] text-white py-1 px-2 rounded text-xs font-semibold hover:bg-[#ff4d4d] transition-colors">
                      Xem ngay
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Enhanced Movie Detail Page Functionality
    document.addEventListener('DOMContentLoaded', function() {
      
      // Star rating functionality
      document.querySelectorAll('.star-rating').forEach(star => {
        star.addEventListener('click', () => {
          const rating = parseInt(star.dataset.rating);
          const stars = document.querySelectorAll('.star-rating');
          
          stars.forEach((s, index) => {
            if (index < rating) {
              s.textContent = '★';
              s.classList.add('text-yellow-400');
              s.classList.remove('text-gray-400');
            } else {
              s.textContent = '☆';
              s.classList.add('text-gray-400');
              s.classList.remove('text-yellow-400');
            }
          });
        });
        
        star.addEventListener('mouseenter', () => {
          const rating = parseInt(star.dataset.rating);
          const stars = document.querySelectorAll('.star-rating');
          
          stars.forEach((s, index) => {
            if (index < rating) {
              s.textContent = '★';
              s.classList.add('text-yellow-400');
              s.classList.remove('text-gray-400');
            } else {
              s.textContent = '☆';
              s.classList.add('text-gray-400');
              s.classList.remove('text-yellow-400');
            }
          });
        });
      });
      
      // Reset stars on mouse leave
      const ratingStars = document.getElementById('rating-stars');
      if (ratingStars) {
        ratingStars.addEventListener('mouseleave', () => {
          const stars = document.querySelectorAll('.star-rating');
          stars.forEach(star => {
            star.textContent = '☆';
            star.classList.add('text-gray-400');
            star.classList.remove('text-yellow-400');
          });
        });
      }
      
      // Trailer button functionality
      document.querySelectorAll('.play-trailer-btn, button:contains("Xem trailer")').forEach(btn => {
        btn.addEventListener('click', () => {
          const trailerSection = document.querySelector('.aspect-video');
          if (trailerSection) {
            trailerSection.scrollIntoView({ behavior: 'smooth' });
          }
        });
      });
      
      // Similar Movies Carousel
      let currentSlide = 0;
      const slide = document.getElementById('similar-movies-slide');
      const totalSlides = document.querySelectorAll('.movie-card-hover').length;
      const slidesToShow = 4; // Number of slides visible at once
      
      function updateCarousel() {
        if (slide) {
          const translateX = -currentSlide * (128 + 16); // 128px width + 16px gap
          slide.style.transform = `translateX(${translateX}px)`;
        }
      }
      
      // Carousel navigation
      document.querySelectorAll('.carousel-prev').forEach(btn => {
        btn.addEventListener('click', () => {
          if (currentSlide > 0) {
            currentSlide--;
            updateCarousel();
          }
        });
      });
      
      document.querySelectorAll('.carousel-next').forEach(btn => {
        btn.addEventListener('click', () => {
          if (currentSlide < totalSlides - slidesToShow) {
            currentSlide++;
            updateCarousel();
          }
        });
      });
      
      // Auto-play carousel
      setInterval(() => {
        if (currentSlide < totalSlides - slidesToShow) {
          currentSlide++;
        } else {
          currentSlide = 0;
        }
        updateCarousel();
      }, 5000);
      
      // Parallax effect for hero section
      window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxSection = document.querySelector('.parallax-section');
        if (parallaxSection) {
          const rate = scrolled * -0.5;
          parallaxSection.style.transform = `translateY(${rate}px)`;
        }
      });
      
      // Intersection Observer for animations
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in-up');
          }
        });
      }, observerOptions);
      
      // Observe elements for animation
      document.querySelectorAll('.animate-fade-in-up').forEach(el => {
        observer.observe(el);
      });
    });
    
    // Toggle description functionality
    function toggleDescription() {
      const description = document.querySelector('.movie-description');
      const readMoreText = document.getElementById('read-more-text');
      const readMoreIcon = document.getElementById('read-more-icon');
      
      if (description && readMoreText && readMoreIcon) {
        if (description.classList.contains('collapsed')) {
          description.classList.remove('collapsed');
          description.classList.add('expanded');
          readMoreText.textContent = 'Thu gọn';
          readMoreIcon.style.transform = 'rotate(180deg)';
        } else {
          description.classList.remove('expanded');
          description.classList.add('collapsed');
          readMoreText.textContent = 'Xem thêm';
          readMoreIcon.style.transform = 'rotate(0deg)';
        }
      }
    }
    
    // Enhanced button interactions
    document.querySelectorAll('.btn-enhanced').forEach(btn => {
      btn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.05)';
      });
      
      btn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
      });
    });
    
    // Search functionality enhancement
    document.querySelectorAll('input[placeholder*="Tìm kiếm"]').forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.classList.add('search-bar-enhanced');
      });
      
      input.addEventListener('blur', function() {
        this.parentElement.classList.remove('search-bar-enhanced');
      });
    });
  </script>
@endsection
