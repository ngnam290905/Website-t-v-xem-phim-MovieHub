@extends('layouts.main')

@section('title', $title . ' - MovieHub')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-col lg:flex-row gap-8">
    <!-- Sidebar Filter -->
    <aside class="hidden lg:block lg:sticky lg:top-[130px] w-72 shrink-0 h-fit">
      <div class="bg-[#1b1d24]/80 backdrop-blur-sm border border-[#262833] rounded-xl p-6 shadow-xl">
        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
          <i class="fas fa-filter" style="color: {{ $color }};"></i>
          Lọc phim
        </h3>
        
        <!-- Clear Filters Button -->
        <div class="mb-4 flex justify-end">
          <button id="clear-filters" class="text-xs text-white/60 hover:text-white/90 transition-colors flex items-center gap-1">
            <i class="fas fa-times-circle text-[10px]"></i>
            <span>Xóa bộ lọc</span>
          </button>
        </div>
        
        <!-- Genre Filter -->
        <div class="mb-8">
          <h4 class="text-sm font-semibold text-white/90 mb-3 flex items-center gap-2">
            <i class="fas fa-tags text-[8px]" style="color: {{ $color }};"></i>
            Thể loại
          </h4>
          <div class="flex flex-wrap gap-2">
            @php
              $genres = ['Hành động', 'Tình cảm', 'Hài', 'Kinh dị', 'Hoạt hình', 'Khoa học viễn tưởng', 'Phiêu lưu'];
            @endphp
            @foreach($genres as $genre)
              <button class="genre-chip px-3 py-1.5 text-xs font-medium rounded-full border border-[#262833] bg-[#151822] text-white/70 hover:text-white transition-all duration-300" data-genre="{{ $genre }}" style="border-color: {{ $color }}40;" onmouseover="this.style.backgroundColor='{{ $color }}'; this.style.borderColor='{{ $color }}';" onmouseout="if(!this.classList.contains('active')) { this.style.backgroundColor='#151822'; this.style.borderColor='#262833'; }">
                {{ $genre }}
              </button>
            @endforeach
          </div>
        </div>
        
        <!-- Rating Filter -->
        <div class="mb-8">
          <h4 class="text-sm font-semibold text-white/90 mb-3 flex items-center gap-2">
            <i class="fas fa-star text-[8px]" style="color: {{ $color }};"></i>
            Đánh giá
          </h4>
          <div class="flex flex-wrap gap-2">
            <button class="rating-btn px-3 py-1.5 text-xs font-medium rounded-lg border border-[#262833] bg-[#151822] text-white/70 hover:text-white transition-all duration-300" data-rating="9" style="border-color: {{ $color }}40;" onmouseover="this.style.backgroundColor='{{ $color }}'; this.style.borderColor='{{ $color }}';" onmouseout="if(!this.classList.contains('active')) { this.style.backgroundColor='#151822'; this.style.borderColor='#262833'; }">
              ⭐ 9+
            </button>
            <button class="rating-btn px-3 py-1.5 text-xs font-medium rounded-lg border border-[#262833] bg-[#151822] text-white/70 hover:text-white transition-all duration-300" data-rating="8" style="border-color: {{ $color }}40;" onmouseover="this.style.backgroundColor='{{ $color }}'; this.style.borderColor='{{ $color }}';" onmouseout="if(!this.classList.contains('active')) { this.style.backgroundColor='#151822'; this.style.borderColor='#262833'; }">
              ⭐ 8+
            </button>
            <button class="rating-btn px-3 py-1.5 text-xs font-medium rounded-lg border border-[#262833] bg-[#151822] text-white/70 hover:text-white transition-all duration-300" data-rating="7" style="border-color: {{ $color }}40;" onmouseover="this.style.backgroundColor='{{ $color }}'; this.style.borderColor='{{ $color }}';" onmouseout="if(!this.classList.contains('active')) { this.style.backgroundColor='#151822'; this.style.borderColor='#262833'; }">
              ⭐ 7+
            </button>
          </div>
        </div>
        
        <!-- Duration Filter -->
        <div>
          <h4 class="text-sm font-semibold text-white/90 mb-3 flex items-center gap-2">
            <i class="fas fa-clock text-[8px]" style="color: {{ $color }};"></i>
            Thời lượng
          </h4>
          <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="checkbox" name="duration" value="short" class="w-4 h-4 rounded focus:ring-2" style="accent-color: {{ $color }};">
              <span class="text-sm text-white/80 group-hover:text-white transition-colors">&lt; 90 phút</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="checkbox" name="duration" value="medium" class="w-4 h-4 rounded focus:ring-2" style="accent-color: {{ $color }};">
              <span class="text-sm text-white/80 group-hover:text-white transition-colors">90-120 phút</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="checkbox" name="duration" value="long" class="w-4 h-4 rounded focus:ring-2" style="accent-color: {{ $color }};">
              <span class="text-sm text-white/80 group-hover:text-white transition-colors">&gt; 120 phút</span>
            </label>
          </div>
        </div>
      </div>
    </aside>
    
    <!-- Main Content -->
    <div class="flex-1">
      <!-- Header Section -->
      <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-1 h-16 bg-gradient-to-b rounded-full" style="background: linear-gradient(to bottom, {{ $color }}, {{ $color }}88);"></div>
          <div>
            <h1 class="text-4xl md:text-5xl font-bold text-white flex items-center gap-3">
              <i class="fas {{ $icon }} text-4xl" style="color: {{ $color }};"></i>
              <span>{{ $title }}</span>
            </h1>
            <p class="text-[#a6a6b0] text-base mt-2">{{ $description }}</p>
          </div>
        </div>
        
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-[#a6a6b0] mb-6">
          <a href="{{ route('home') }}" class="hover:text-[#FF784E] transition-colors">Trang chủ</a>
          <i class="fas fa-chevron-right text-xs"></i>
          <span class="text-white">{{ $title }}</span>
        </nav>
      </div>
      
      <!-- Filter & Sort Bar -->
      <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4 text-sm">
          <span class="text-[#a6a6b0]">Tìm thấy <strong class="text-white">{{ $movies->total() }}</strong> phim</span>
        </div>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-[#a6a6b0]">Sắp xếp:</span>
          <select class="px-3 py-1.5 bg-[#1b1d24]/80 border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:border-[#FF784E]">
            <option>Mặc định</option>
            <option>Mới nhất</option>
            <option>Đánh giá cao</option>
            <option>A-Z</option>
          </select>
        </div>
      </div>
      
      <!-- Movies Grid -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-8">
        @forelse($movies as $movie)
          @include('partials.movie-card', ['movie' => $movie, 'highlight' => $category])
        @empty
          <div class="col-span-full text-center py-12">
            <i class="fas {{ $icon }} text-6xl mb-4" style="color: {{ $color }}; opacity: 0.3;"></i>
            <p class="text-[#a6a6b0] text-lg">Chưa có phim nào</p>
            <a href="{{ route('home') }}" class="mt-4 inline-flex items-center gap-2 text-[#FF784E] hover:text-[#FFB25E] transition-colors">
              <i class="fas fa-arrow-left"></i>
              <span>Quay về trang chủ</span>
            </a>
          </div>
        @endforelse
      </div>
      
      <!-- Pagination -->
      @if($movies->hasPages())
        <div class="mt-8">
          {{ $movies->links('pagination.custom') }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

