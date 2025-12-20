@php
  // Chuẩn hóa badge theo trạng thái phim
  $badgeConfig = [];
  if ($movie->hot) {
    $badgeConfig[] = ['class' => 'bg-gradient-to-r from-yellow-400 to-orange-400 text-black', 'text' => '🔥 HOT'];
  }
  if ($movie->trang_thai === 'dang_chieu') {
    $badgeConfig[] = ['class' => 'bg-green-500 text-white', 'text' => '🔴 Đang chiếu'];
  } elseif ($movie->trang_thai === 'sap_chieu') {
    $badgeConfig[] = ['class' => 'bg-yellow-500 text-black', 'text' => '🟡 Sắp chiếu'];
  }
  
  $highlightColors = [
    'hot' => ['border' => 'border-[#FF784E]/50', 'hover' => 'hover:border-[#FF784E]'],
    'now' => ['border' => 'border-blue-500/50', 'hover' => 'hover:border-blue-400'],
    'coming' => ['border' => 'border-purple-500/50', 'hover' => 'hover:border-purple-400'],
  ];
  $colors = $highlightColors[$highlight] ?? $highlightColors['now'];
@endphp

<div class="movie-card group cursor-pointer bg-[#1b1d24]/80 border {{ $colors['border'] }} rounded-xl overflow-hidden {{ $colors['hover'] }} transition-all duration-500 transform hover:scale-[1.05] hover:shadow-2xl hover:shadow-[#F53003]/30 focus-within:ring-2 focus-within:ring-[#F53003]/50 focus-within:outline-none">
    <div class="relative overflow-hidden">
    <x-image 
      src="{{ $movie->poster_url }}" 
      alt="{{ $movie->ten_phim }}"
      aspectRatio="2/3"
      class="w-full transition-transform duration-700 group-hover:scale-110"
      quality="high"
      onerror="this.src='{{ asset('images/no-poster.svg') }}'"
    />
    <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
    
    <!-- Badges -->
    <div class="absolute top-3 left-3 z-10 flex flex-col gap-2">
      @foreach($badgeConfig as $badge)
        <span class="px-2.5 py-1 {{ $badge['class'] }} text-[10px] font-bold rounded uppercase shadow-lg">
          {{ $badge['text'] }}
        </span>
      @endforeach
    </div>
    
    <!-- Rating -->
    <div class="absolute top-3 right-3 z-10">
      <div class="px-2.5 py-1 bg-yellow-400/90 backdrop-blur-sm text-black text-xs font-bold rounded-full flex items-center gap-1 shadow-lg">
        <i class="fas fa-star text-[10px]"></i>
        {{ number_format($movie->diem_danh_gia ?? 0, 1) }}
      </div>
    </div>
    
    <!-- Hover Overlay with Trailer & Info -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 z-10 flex flex-col items-center justify-center p-4">
      @if($movie->trailer)
        <button onclick="openTrailer('{{ $movie->trailer }}', '{{ $movie->ten_phim }}')" class="w-20 h-20 rounded-full bg-gradient-to-r from-[#F53003] to-[#ff5c3a] backdrop-blur-md border-2 border-white/30 flex items-center justify-center text-white hover:bg-gradient-to-r hover:from-[#ff5c3a] hover:to-[#F53003] transition-all duration-300 transform hover:scale-110 mb-4 shadow-lg shadow-[#F53003]/50 hover:shadow-xl hover:shadow-[#F53003]/70">
          <i class="fas fa-play text-2xl ml-1"></i>
        </button>
      @endif
      <div class="text-center text-white space-y-3">
        @if($movie->the_loai)
          <div class="px-3 py-1 bg-[#F53003]/80 backdrop-blur-sm rounded-full text-xs font-semibold inline-block">
            🎭 {{ $movie->the_loai }}
          </div>
        @endif
        <p class="text-sm line-clamp-3 px-2">{{ $movie->mo_ta_ngan ?? substr($movie->mo_ta ?? '', 0, 100) . '...' }}</p>
        <div class="flex gap-2">
          @if($highlight !== 'coming')
            <a href="{{ route('booking.index') }}?movie={{ $movie->id }}" class="px-4 py-2 bg-gradient-to-r from-[#F53003] to-[#ff5c3a] text-white rounded-lg text-sm font-bold hover:shadow-lg hover:shadow-[#F53003]/50 transition-all transform hover:scale-105">
              🎟️ Đặt vé
            </a>
          @endif
          <a href="{{ route('movie-detail', $movie->id) }}" class="px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white rounded-lg text-sm font-semibold hover:bg-white/20 transition-all">
            Chi tiết
          </a>
        </div>
      </div>
    </div>
  </div>
  
  <div class="p-4">
    <h3 class="font-bold text-lg text-white mb-2 line-clamp-2 group-hover:text-[#FF784E] transition-colors duration-300">
      {{ $movie->ten_phim }}
    </h3>
    
    <div class="flex items-center gap-2 text-xs text-[#a6a6b0] mb-3">
      <span class="flex items-center gap-1">
        <i class="far fa-clock text-[#FF784E]"></i>
        {{ $movie->do_dai ?? 0 }} phút
      </span>
      <span>•</span>
      <span>{{ $movie->do_tuoi ?? 'P' }}</span>
      <span>•</span>
      <span class="line-clamp-1">{{ $movie->the_loai ?? 'N/A' }}</span>
    </div>
    
    @if($highlight === 'now')
      <!-- Nearest Showtime for Now Showing -->
      <div class="mb-3 text-xs text-[#a6a6b0]">
        <span class="flex items-center gap-1.5">
          <i class="fas fa-clock text-[#FF784E]"></i>
          <span>Suất gần nhất: <strong class="text-white/90">Hôm nay 19:30</strong> – MovieHub Q1</span>
        </span>
      </div>
    @elseif($highlight === 'coming')
      <!-- Release Date for Upcoming -->
      <div class="mb-3 text-xs text-[#a6a6b0]">
        <span class="flex items-center gap-1.5">
          <i class="fas fa-calendar-check text-purple-400"></i>
          <span>Khởi chiếu: <strong class="text-white/90">{{ $movie->ngay_khoi_chieu ? $movie->ngay_khoi_chieu->format('d/m/Y') : 'Sắp ra mắt' }}</strong></span>
        </span>
      </div>
    @endif
    
    <!-- Action Buttons -->
    <div class="flex gap-2">
      @if($highlight === 'coming')
        <a href="{{ route('movie-detail', $movie->id) }}" 
           class="flex-1 px-3 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg font-semibold text-sm hover:shadow-lg hover:shadow-purple-500/50 transition-all duration-300 flex items-center justify-center gap-2 text-center">
          <i class="fas fa-bell"></i>
          Nhắc tôi
        </a>
      @else
        <a href="{{ route('booking.index') }}?movie={{ $movie->id }}" 
           class="flex-1 px-3 py-2 bg-gradient-to-r from-[#FF784E] to-[#FFB25E] text-white rounded-lg font-semibold text-sm hover:shadow-lg hover:shadow-[#FF784E]/50 hover:bg-gradient-to-r hover:from-[#FF8A5E] hover:to-[#FFC26E] transition-all duration-300 flex items-center justify-center gap-2 text-center transform hover:scale-105">
          <i class="fas fa-ticket-alt"></i>
          Đặt vé
        </a>
      @endif
      <a href="{{ route('movie-detail', $movie->id) }}" 
         class="px-3 py-2 bg-transparent border border-[#262833] text-white/70 rounded-lg font-semibold text-sm hover:bg-white/5 hover:text-white hover:border-[#FF784E]/50 transition-all duration-300 flex items-center justify-center transform hover:scale-110">
        <i class="fas fa-info-circle"></i>
      </a>
    </div>
  </div>
</div>

