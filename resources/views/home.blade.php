@extends('layouts.main')

@section('title', 'MovieHub - Đặt vé xem phim')

@section('content')
<!-- Hero Banner Section -->
<section class="relative w-full mb-12 overflow-hidden">
  <div class="hero-carousel-container relative h-[500px] md:h-[600px] lg:h-[700px]">
    @forelse($featuredMovies as $index => $featuredMovie)
      <div class="hero-slide absolute inset-0 transition-opacity duration-1000 {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}" data-slide="{{ $index }}">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0">
          <x-image 
            src="{{ $featuredMovie->poster_url }}" 
            alt="{{ $featuredMovie->ten_phim }}"
            class="w-full h-full scale-110 blur-sm"
            quality="medium"
            :lazy="false"
            :placeholder="false"
          />
          <div class="absolute inset-0 bg-gradient-to-r from-[#050814]/98 via-[#050814]/85 to-[#050814]/70"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-[#050814] via-transparent to-transparent"></div>
          <div class="absolute inset-0 bg-black/20"></div>
        </div>
        
        <!-- Content -->
        <div class="relative z-10 h-full flex items-center">
          <div class="max-w-7xl mx-auto px-4 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
              <!-- Left: Poster -->
              <div class="hidden lg:block">
                <div class="relative group">
                  <div class="absolute -inset-4 bg-gradient-to-r from-[#FF784E]/20 to-[#FFB25E]/20 rounded-2xl blur-xl opacity-75 group-hover:opacity-100 transition-opacity duration-500"></div>
                  <div class="relative overflow-hidden rounded-2xl shadow-2xl transform group-hover:scale-105 transition-transform duration-500">
                    <x-image 
                      src="{{ $featuredMovie->poster_url }}" 
                      alt="{{ $featuredMovie->ten_phim }}"
                      aspectRatio="2/3"
                      class="w-full h-full rounded-2xl"
                      quality="high"
                      :lazy="false"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                  </div>
                  <!-- Play Trailer Button -->
                  <button class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-md border-2 border-white/30 flex items-center justify-center text-white hover:bg-white/30 transition-all duration-300 transform hover:scale-110">
                      <i class="fas fa-play text-2xl ml-1"></i>
                    </div>
                  </button>
                </div>
              </div>
              
              <!-- Right: Movie Info -->
              <div class="text-white space-y-6 animate-fade-in-up">
                <!-- Badges -->
                <div class="flex items-center gap-3 flex-wrap">
                  @if($featuredMovie->trang_thai === 'dang_chieu')
                    <span class="px-4 py-1.5 bg-gradient-to-r from-[#FF784E] to-[#FFB25E] text-white text-sm font-bold rounded-full uppercase shadow-lg">
                      <i class="fas fa-fire mr-2"></i>Đang chiếu
                    </span>
                  @elseif($featuredMovie->trang_thai === 'sap_chieu')
                    <span class="px-4 py-1.5 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-sm font-bold rounded-full uppercase shadow-lg">
                      <i class="fas fa-calendar-alt mr-2"></i>Sắp chiếu
                    </span>
                  @endif
                  <div class="px-4 py-1.5 bg-yellow-400/90 backdrop-blur-sm text-black text-sm font-bold rounded-full flex items-center gap-2 shadow-lg">
                    <i class="fas fa-star"></i>
                    <span>{{ number_format($featuredMovie->diem_danh_gia ?? 0, 1) }}</span>
                  </div>
                  <span class="px-4 py-1.5 bg-white/10 backdrop-blur-sm border border-white/20 text-white text-sm font-medium rounded-full">
                    {{ $featuredMovie->do_tuoi ?? 'P' }}
                  </span>
                </div>
                
                <!-- Title -->
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
                  <span class="bg-gradient-to-r from-white via-white/95 to-white/85 bg-clip-text text-transparent drop-shadow-lg">
                    {{ $featuredMovie->ten_phim }}
                  </span>
                </h1>
                
                <!-- Meta Info -->
                <div class="flex items-center gap-4 text-white/80 text-sm md:text-base">
                  <span class="flex items-center gap-2">
                    <i class="far fa-clock text-[#FF784E]"></i>
                    {{ $featuredMovie->do_dai ?? 0 }} phút
                  </span>
                  <span>•</span>
                  <span class="flex items-center gap-2">
                    <i class="fas fa-film text-[#FF784E]"></i>
                    {{ $featuredMovie->the_loai ?? 'N/A' }}
                  </span>
                  <span>•</span>
                  <span class="flex items-center gap-2">
                    <i class="fas fa-calendar text-[#FF784E]"></i>
                    {{ $featuredMovie->ngay_khoi_chieu ? $featuredMovie->ngay_khoi_chieu->format('d/m/Y') : 'N/A' }}
                  </span>
                </div>
                
                <!-- Description -->
                <p class="text-white/70 text-base md:text-lg leading-relaxed line-clamp-3 max-w-2xl">
                  {{ Str::limit($featuredMovie->mo_ta ?? 'Khám phá một câu chuyện điện ảnh đầy cảm xúc và kịch tính.', 150) }}
                </p>
                
                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-4">
                  <a href="{{ route('booking.index') }}?movie={{ $featuredMovie->id }}" 
                     class="px-10 py-5 bg-gradient-to-r from-[#FF784E] to-[#FFB25E] text-white rounded-full font-bold text-lg md:text-xl hover:shadow-2xl hover:shadow-[#FF784E]/60 transition-all duration-300 flex items-center gap-3 transform hover:scale-110 hover:-translate-y-1 group">
                    <i class="fas fa-ticket-alt text-xl"></i>
                    <span>Đặt vé ngay</span>
                    <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                  </a>
                  <button class="px-8 py-4 bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white rounded-full font-bold text-lg hover:bg-white/20 hover:border-white/50 transition-all duration-300 flex items-center gap-3 group">
                    <i class="fas fa-play"></i>
                    <span>Xem trailer</span>
                  </button>
                </div>
                
                <!-- Cast & Director -->
                <div class="flex flex-col gap-2 text-sm text-white/60">
                  @if($featuredMovie->dao_dien)
                    <span><strong class="text-white/80">Đạo diễn:</strong> {{ $featuredMovie->dao_dien }}</span>
                  @endif
                  @if($featuredMovie->dien_vien)
                    <span><strong class="text-white/80">Diễn viên:</strong> {{ Str::limit($featuredMovie->dien_vien, 80) }}</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="absolute inset-0 flex items-center justify-center">
        <p class="text-white/50 text-lg">Chưa có phim nổi bật</p>
      </div>
    @endforelse
  </div>
  
  <!-- Navigation Dots -->
  @if($featuredMovies->count() > 1)
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex items-center gap-3">
      @foreach($featuredMovies as $index => $movie)
        <button class="hero-dot w-3 h-3 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-[#FF784E] w-8' : 'bg-white/30 hover:bg-white/50' }}" 
                data-slide-to="{{ $index }}"
                aria-label="Go to slide {{ $index + 1 }}"></button>
      @endforeach
    </div>
  @endif
  
  <!-- Navigation Arrows -->
  @if($featuredMovies->count() > 1)
    <button class="hero-nav hero-nav-prev absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white/20 transition-all duration-300 flex items-center justify-center group">
      <i class="fas fa-chevron-left group-hover:-translate-x-1 transition-transform"></i>
    </button>
    <button class="hero-nav hero-nav-next absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white/20 transition-all duration-300 flex items-center justify-center group">
      <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
    </button>
  @endif
</section>

<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-col lg:flex-row gap-8">
    <!-- Sidebar Filter -->
    <aside class="hidden lg:block lg:sticky lg:top-[130px] w-72 shrink-0 h-fit">
      <div class="bg-[#1b1d24]/80 backdrop-blur-sm border border-[#262833] rounded-xl p-6 shadow-xl">
        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
          <i class="fas fa-filter text-[#FF784E]"></i>
          Lọc phim
        </h3>
        
        <!-- Clear Filters Button -->
        <div class="mb-4 flex justify-end">
          <button id="clear-filters" class="text-xs text-white/60 hover:text-white/90 transition-colors flex items-center gap-1">
            <i class="fas fa-times-circle text-[10px]"></i>
            <span>Xóa bộ lọc</span>
          </button>
        </div>
        
        <!-- Status Filter -->
        <div class="mb-8">
          <h4 class="text-sm font-semibold text-white/90 mb-3 flex items-center gap-2">
            <i class="fas fa-circle text-[#FF784E] text-[8px]"></i>
            Trạng thái
          </h4>
          <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="radio" name="status" value="dang_chieu" checked class="w-4 h-4 text-[#FF784E] bg-[#262833] border-[#262833] focus:ring-[#FF784E] focus:ring-2">
              <span class="text-sm text-white/80 group-hover:text-[#FF784E] transition-colors">● Đang chiếu</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="radio" name="status" value="sap_chieu" class="w-4 h-4 text-[#FF784E] bg-[#262833] border-[#262833] focus:ring-[#FF784E] focus:ring-2">
              <span class="text-sm text-white/80 group-hover:text-[#FF784E] transition-colors">○ Sắp chiếu</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="radio" name="status" value="special" class="w-4 h-4 text-[#FF784E] bg-[#262833] border-[#262833] focus:ring-[#FF784E] focus:ring-2">
              <span class="text-sm text-white/80 group-hover:text-[#FF784E] transition-colors">○ Suất đặc biệt</span>
            </label>
          </div>
    </div>

        <!-- Genre Filter -->
        <div class="mb-8">
          <h4 class="text-sm font-semibold text-white/90 mb-3 flex items-center gap-2">
            <i class="fas fa-tags text-[#FF784E] text-[8px]"></i>
            Thể loại
          </h4>
          <div class="flex flex-wrap gap-2">
            @php
              $genres = ['Hành động', 'Tình cảm', 'Hài', 'Kinh dị', 'Hoạt hình', 'Khoa học viễn tưởng', 'Phiêu lưu'];
      @endphp
            @foreach($genres as $genre)
              <button class="genre-chip px-3 py-1.5 text-xs font-medium rounded-full border border-[#262833] bg-[#151822] text-white/70 hover:bg-[#FF784E] hover:text-white hover:border-[#FF784E] transition-all duration-300" data-genre="{{ $genre }}">
                {{ $genre }}
              </button>
            @endforeach
          </div>
        </div>
        
        <!-- Rating Filter -->
        <div class="mb-8">
          <h4 class="text-sm font-semibold text-white/90 mb-3 flex items-center gap-2">
            <i class="fas fa-star text-[#FF784E] text-[8px]"></i>
            Đánh giá
          </h4>
          <div class="flex flex-wrap gap-2">
            <button class="rating-btn px-3 py-1.5 text-xs font-medium rounded-lg border border-[#262833] bg-[#151822] text-white/70 hover:bg-[#FF784E] hover:text-white hover:border-[#FF784E] transition-all duration-300" data-rating="9">
              ⭐ 9+
            </button>
            <button class="rating-btn px-3 py-1.5 text-xs font-medium rounded-lg border border-[#262833] bg-[#151822] text-white/70 hover:bg-[#FF784E] hover:text-white hover:border-[#FF784E] transition-all duration-300" data-rating="8">
              ⭐ 8+
            </button>
            <button class="rating-btn px-3 py-1.5 text-xs font-medium rounded-lg border border-[#262833] bg-[#151822] text-white/70 hover:bg-[#FF784E] hover:text-white hover:border-[#FF784E] transition-all duration-300" data-rating="7">
              ⭐ 7+
            </button>
          </div>
        </div>
        
        <!-- Duration Filter -->
        <div>
          <h4 class="text-sm font-semibold text-white/90 mb-3 flex items-center gap-2">
            <i class="fas fa-clock text-[#FF784E] text-[8px]"></i>
            Thời lượng
          </h4>
          <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="checkbox" name="duration" value="short" class="w-4 h-4 text-[#FF784E] bg-[#262833] border-[#262833] rounded focus:ring-[#FF784E] focus:ring-2">
              <span class="text-sm text-white/80 group-hover:text-[#FF784E] transition-colors">&lt; 90 phút</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="checkbox" name="duration" value="medium" class="w-4 h-4 text-[#FF784E] bg-[#262833] border-[#262833] rounded focus:ring-[#FF784E] focus:ring-2">
              <span class="text-sm text-white/80 group-hover:text-[#FF784E] transition-colors">90-120 phút</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="checkbox" name="duration" value="long" class="w-4 h-4 text-[#FF784E] bg-[#262833] border-[#262833] rounded focus:ring-[#FF784E] focus:ring-2">
              <span class="text-sm text-white/80 group-hover:text-[#FF784E] transition-colors">&gt; 120 phút</span>
            </label>
          </div>
        </div>
      </div>
    </aside>
    
    <!-- Main Content -->
    <div class="flex-1 space-y-12">
      
      <!-- Section 1: Phim Hot -->
      <section id="hot" class="scroll-mt-32 mb-12">
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="w-1 h-12 bg-gradient-to-b from-[#FF784E] to-[#FFB25E] rounded-full"></div>
            <div>
              <h2 class="text-3xl md:text-4xl font-bold text-white flex items-center gap-3">
                <i class="fas fa-fire text-[#FF784E] text-3xl animate-pulse-glow"></i>
                <span>Phim Hot</span>
              </h2>
              <p class="text-[#a6a6b0] text-sm mt-1">Những bộ phim được đánh giá cao nhất</p>
            </div>
          </div>
          @if($hotMovies->count() > 5)
            <a href="{{ route('movies.category', 'hot') }}" class="hidden md:flex items-center gap-2 text-[#FF784E] hover:text-[#FFB25E] transition-colors text-sm font-medium group">
              <span>Xem tất cả</span>
              <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
          @endif
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          @forelse($hotMovies->take(5) as $movie)
            @include('partials.movie-card', ['movie' => $movie, 'highlight' => 'hot'])
          @empty
            <div class="col-span-full text-center py-12">
              <i class="fas fa-fire text-6xl text-[#262833] mb-4"></i>
              <p class="text-[#a6a6b0] text-lg">Chưa có phim hot</p>
            </div>
          @endforelse
        </div>
      </section>
      
      <!-- Section 2: Phim Đang Chiếu -->
      <section id="now" class="scroll-mt-32 mb-12">
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="w-1 h-12 bg-gradient-to-b from-blue-500 to-cyan-400 rounded-full"></div>
            <div>
              <h2 class="text-3xl md:text-4xl font-bold text-white flex items-center gap-3">
                <i class="fas fa-video text-blue-400 text-3xl"></i>
                <span>Phim Đang Chiếu</span>
              </h2>
              <p class="text-[#a6a6b0] text-sm mt-1">Đặt vé ngay để không bỏ lỡ</p>
            </div>
          </div>
          @if($nowShowingMovies->count() > 5)
            <a href="{{ route('movies.category', 'now') }}" class="hidden md:flex items-center gap-2 text-blue-400 hover:text-cyan-400 transition-colors text-sm font-medium group">
              <span>Xem tất cả</span>
              <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
          @endif
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          @forelse($nowShowingMovies->take(5) as $movie)
            @include('partials.movie-card', ['movie' => $movie, 'highlight' => 'now'])
          @empty
            <div class="col-span-full text-center py-12">
              <i class="fas fa-video text-6xl text-[#262833] mb-4"></i>
              <p class="text-[#a6a6b0] text-lg">Chưa có phim đang chiếu</p>
            </div>
          @endforelse
        </div>
      </section>
      
      <!-- Section 3: Phim Sắp Chiếu -->
      <section id="coming" class="scroll-mt-32 mb-12">
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="w-1 h-12 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full"></div>
            <div>
              <h2 class="text-3xl md:text-4xl font-bold text-white flex items-center gap-3">
                <i class="fas fa-calendar-alt text-purple-400 text-3xl"></i>
                <span>Phim Sắp Chiếu</span>
              </h2>
              <p class="text-[#a6a6b0] text-sm mt-1">Sắp ra mắt - Đặt vé sớm để nhận ưu đãi</p>
            </div>
          </div>
          @if($upcomingMovies->count() > 5)
            <a href="{{ route('movies.category', 'coming') }}" class="hidden md:flex items-center gap-2 text-purple-400 hover:text-pink-400 transition-colors text-sm font-medium group">
              <span>Xem tất cả</span>
              <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
          @endif
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          @forelse($upcomingMovies->take(5) as $movie)
            @include('partials.movie-card', ['movie' => $movie, 'highlight' => 'coming'])
          @empty
            <div class="col-span-full text-center py-12">
              <i class="fas fa-calendar-alt text-6xl text-[#262833] mb-4"></i>
              <p class="text-[#a6a6b0] text-lg">Chưa có phim sắp chiếu</p>
            </div>
          @endforelse
        </div>
      </section>
      
    </div>
  </div>
</div>

<!-- Floating CTA Button -->
<a href="{{ route('booking.index') }}" 
   class="fixed bottom-6 right-6 z-40 bg-gradient-to-r from-[#FF784E] to-[#FFB25E] text-white px-6 py-4 rounded-[999px] font-semibold shadow-[0_8px_32px_rgba(255,120,78,0.5)] hover:shadow-[0_12px_40px_rgba(255,120,78,0.6)] transition-all duration-300 flex items-center gap-2 group animate-bounce-subtle transform hover:scale-110 hover:-translate-y-1 mb-safe">
  <i class="fas fa-ticket-alt text-xl"></i>
  <span class="hidden sm:inline">Đặt vé nhanh</span>
</a>

@section('scripts')
<script>
  // Hero Carousel Functionality
  document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    const prevBtn = document.querySelector('.hero-nav-prev');
    const nextBtn = document.querySelector('.hero-nav-next');
    let currentSlide = 0;
    let autoPlayInterval;
    let isPaused = false;
    
    if (slides.length === 0) return;
    
    function showSlide(index) {
      // Remove active class from all slides
      slides.forEach((slide, i) => {
        slide.classList.remove('opacity-100');
        slide.classList.add('opacity-0');
      });
      
      // Add active class to current slide
      if (slides[index]) {
        slides[index].classList.remove('opacity-0');
        slides[index].classList.add('opacity-100');
      }
      
      // Update dots
      dots.forEach((dot, i) => {
        if (i === index) {
          dot.classList.add('bg-[#FF784E]', 'w-8');
          dot.classList.remove('bg-white/30', 'w-3');
        } else {
          dot.classList.remove('bg-[#FF784E]', 'w-8');
          dot.classList.add('bg-white/30', 'w-3');
        }
      });
      
      currentSlide = index;
    }
    
    function nextSlide() {
      const next = (currentSlide + 1) % slides.length;
      showSlide(next);
    }
    
    function prevSlide() {
      const prev = (currentSlide - 1 + slides.length) % slides.length;
      showSlide(prev);
    }
    
    function startAutoPlay() {
      autoPlayInterval = setInterval(() => {
        if (!isPaused) {
          nextSlide();
        }
      }, 5000); // Change slide every 5 seconds
    }
    
    function pauseAutoPlay() {
      isPaused = true;
    }
    
    function resumeAutoPlay() {
      isPaused = false;
    }
    
    // Event listeners
    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        nextSlide();
        pauseAutoPlay();
        setTimeout(resumeAutoPlay, 10000); // Resume after 10 seconds
      });
    }
    
    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        prevSlide();
        pauseAutoPlay();
        setTimeout(resumeAutoPlay, 10000);
      });
    }
    
    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        showSlide(index);
        pauseAutoPlay();
        setTimeout(resumeAutoPlay, 10000);
      });
    });
    
    // Pause on hover
    const carouselContainer = document.querySelector('.hero-carousel-container');
    if (carouselContainer) {
      carouselContainer.addEventListener('mouseenter', pauseAutoPlay);
      carouselContainer.addEventListener('mouseleave', resumeAutoPlay);
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') {
        prevSlide();
        pauseAutoPlay();
        setTimeout(resumeAutoPlay, 10000);
      } else if (e.key === 'ArrowRight') {
        nextSlide();
        pauseAutoPlay();
        setTimeout(resumeAutoPlay, 10000);
      }
    });
    
    // Start auto-play
    startAutoPlay();
    
    // Fade in animation for content
    const fadeInElements = document.querySelectorAll('.animate-fade-in-up');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.1 });
    
    fadeInElements.forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
      observer.observe(el);
    });
    
    // Filter functionality
    const genreChips = document.querySelectorAll('.genre-chip');
    const ratingBtns = document.querySelectorAll('.rating-btn');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    // Genre chip toggle
    genreChips.forEach(chip => {
      chip.addEventListener('click', function() {
        this.classList.toggle('bg-[#FF784E]');
        this.classList.toggle('text-white');
        this.classList.toggle('border-[#FF784E]');
        this.classList.toggle('bg-[#151822]');
        this.classList.toggle('text-white/70');
        this.classList.toggle('border-[#262833]');
      });
    });
    
    // Rating button toggle
    ratingBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        // Remove active from all
        ratingBtns.forEach(b => {
          b.classList.remove('bg-[#FF784E]', 'text-white', 'border-[#FF784E]');
          b.classList.add('bg-[#151822]', 'text-white/70', 'border-[#262833]');
        });
        // Add active to clicked
        this.classList.add('bg-[#FF784E]', 'text-white', 'border-[#FF784E]');
        this.classList.remove('bg-[#151822]', 'text-white/70', 'border-[#262833]');
      });
    });
    
    // Clear filters
    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', function() {
        // Reset genre chips
        genreChips.forEach(chip => {
          chip.classList.remove('bg-[#FF784E]', 'text-white', 'border-[#FF784E]');
          chip.classList.add('bg-[#151822]', 'text-white/70', 'border-[#262833]');
        });
        // Reset rating buttons
        ratingBtns.forEach(btn => {
          btn.classList.remove('bg-[#FF784E]', 'text-white', 'border-[#FF784E]');
          btn.classList.add('bg-[#151822]', 'text-white/70', 'border-[#262833]');
        });
        // Reset checkboxes
        document.querySelectorAll('input[name="duration"]').forEach(cb => {
          cb.checked = false;
        });
        // Reset radio buttons
        document.querySelector('input[name="status"][value="dang_chieu"]').checked = true;
      });
    }
    
  });
</script>
@endsection


