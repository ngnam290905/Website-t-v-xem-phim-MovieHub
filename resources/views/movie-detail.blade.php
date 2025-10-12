@extends('layouts.app')

@section('title', $movie->ten_phim . ' - MovieHub')

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
  <section class="relative h-[60vh] overflow-hidden mb-8">
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
         style="background-image: url('{{ str_replace('/w342/', '/w1920/', $movie->poster) }}'); filter: brightness(0.4);">
    </div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent"></div>
    
    <div class="relative z-10 h-full flex items-center">
      <div class="max-w-7xl mx-auto px-4 w-full">
        <div class="grid lg:grid-cols-3 gap-8 items-center">
          <!-- Poster -->
          <div class="lg:col-span-1">
            <div class="relative group">
              <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" 
                   class="w-full max-w-sm mx-auto rounded-xl shadow-2xl transition-transform duration-300 group-hover:scale-105">
              <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl flex items-center justify-center">
                <button class="play-trailer-btn w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white/30 transition-all duration-300">
                  <svg class="w-10 h-10 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Movie Info -->
          <div class="lg:col-span-2 text-white">
            <div class="flex items-center gap-3 mb-4">
              <span class="bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-3 py-1 rounded-full text-sm font-semibold">🔥 Phim hot</span>
              <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm">IMDb {{ number_format(8 + ($movie->id % 10) / 10, 1) }}</span>
              <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm">T13</span>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-bold mb-4">{{ $movie->ten_phim }}</h1>
            <p class="text-lg text-white/80 mb-4">{{ $movie->dao_dien }}</p>
            
            <div class="flex items-center gap-6 mb-6 text-white/70">
              <span class="flex items-center gap-2">
                <span>📅</span>
                <span>{{ $movie->created_at->format('d/m/Y') }}</span>
              </span>
              <span class="flex items-center gap-2">
                <span>⏱️</span>
                <span>{{ $movie->do_dai }} phút</span>
              </span>
              <span class="flex items-center gap-2">
                <span>🎭</span>
                <span>{{ $movie->dien_vien }}</span>
              </span>
            </div>
            
            <p class="text-lg text-white/90 mb-6 leading-relaxed">{{ $movie->mo_ta }}</p>
            
            <div class="flex items-center gap-4">
              <button class="bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-[#F53003]/25">
                🎫 Đặt vé ngay
              </button>
              <button class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-black transition-all duration-300">
                🎬 Xem trailer
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
      
      <!-- Similar Movies -->
      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
          <span>🎬</span>
          <span>Phim tương tự</span>
        </h3>
        
        <div class="space-y-4">
          @php
            $similarMovies = [
              ['title' => 'Cuộc Chiến Vũ Trụ', 'poster' => 'https://image.tmdb.org/t/p/w342/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg', 'rating' => 8.2],
              ['title' => 'Bí Mật Thời Gian', 'poster' => 'https://image.tmdb.org/t/p/w342/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg', 'rating' => 7.8],
              ['title' => 'Hành Trình Tình Yêu', 'poster' => 'https://image.tmdb.org/t/p/w342/6XYLiMxHAaCsoyrVo38LBWMw2p8.jpg', 'rating' => 8.0]
            ];
          @endphp
          
          @foreach($similarMovies as $similar)
            <div class="flex items-center gap-3 p-3 bg-[#222533] rounded-lg hover:bg-[#2a2d3a] transition-colors cursor-pointer">
              <img src="{{ $similar['poster'] }}" alt="{{ $similar['title'] }}" class="w-16 h-20 object-cover rounded">
              <div class="flex-1">
                <h4 class="font-semibold text-sm">{{ $similar['title'] }}</h4>
                <div class="flex items-center gap-1 mt-1">
                  <span class="text-yellow-400">⭐</span>
                  <span class="text-sm text-[#a6a6b0]">{{ $similar['rating'] }}</span>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <script>
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
    document.getElementById('rating-stars').addEventListener('mouseleave', () => {
      const stars = document.querySelectorAll('.star-rating');
      stars.forEach(star => {
        star.textContent = '☆';
        star.classList.add('text-gray-400');
        star.classList.remove('text-yellow-400');
      });
    });
    
    // Trailer button functionality
    document.querySelectorAll('.play-trailer-btn, button:contains("Xem trailer")').forEach(btn => {
      btn.addEventListener('click', () => {
        const trailerSection = document.querySelector('.aspect-video');
        trailerSection.scrollIntoView({ behavior: 'smooth' });
      });
    });
  </script>
@endsection
