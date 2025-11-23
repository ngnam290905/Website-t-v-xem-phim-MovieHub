@extends('layouts.app')

@section('title', $title . ' - MovieHub')

@section('content')
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Featured Movies Section -->
    <section class="mb-12">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
          <span class="text-[#F53003]">🔥</span>
          <span>Phim nổi bật</span>
        </h2>
        <a href="#" class="text-sm text-[#F53003] hover:underline flex items-center">
          Xem tất cả
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($movies as $movie)
          <div class="group bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden transition-all duration-300 hover:border-[#F53003]/50 hover:shadow-lg hover:shadow-[#F53003]/10">
            <div class="relative overflow-hidden">
              <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" class="w-full h-80 object-cover transition-transform duration-500 group-hover:scale-105">
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                <a href="{{ route('movies.show', $movie->id) }}" class="w-full bg-[#F53003] text-white text-center py-2 rounded-md text-sm font-medium hover:bg-opacity-90 transition">
                  Xem chi tiết
                </a>
              </div>
              <div class="absolute top-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded">{{ $movie->do_tuoi ?? 'P' }}</div>
            </div>
            <div class="p-4">
              <h3 class="font-bold text-white mb-1 line-clamp-1">{{ $movie->ten_phim }}</h3>
              <p class="text-xs text-[#a6a6b0] mb-2">{{ $movie->do_dai }} phút • {{ $movie->the_loai }}</p>
              <div class="flex justify-between items-center">
                <div class="flex items-center text-yellow-400">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                  <span class="ml-1 text-sm text-white">{{ number_format($movie->diem_danh_gia, 1) }}</span>
                </div>
                @if($movie->trang_thai === 'dang_chieu')
                <a href="{{ route('booking', $movie->id) }}" class="text-sm bg-[#F53003] text-white px-3 py-1 rounded hover:bg-opacity-90 transition">
                  Đặt vé
                </a>
                @else
                <span class="text-sm bg-gray-600 text-white px-3 py-1 rounded opacity-70 cursor-not-allowed">
                  Sắp chiếu
                </span>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $movies->links() }}
    </div>
  </div>

  <!-- Newsletter Section -->
  <section class="bg-[#1b1d24] border-t border-[#262833] mt-16 py-12">
    <div class="max-w-4xl mx-auto px-4 text-center">
      <h2 class="text-2xl font-bold text-white mb-4">Đăng ký nhận thông báo phim mới</h2>
      <p class="text-[#a6a6b0] mb-6">Nhận thông báo khi có phim mới, ưu đãi đặc biệt và nhiều hơn nữa!</p>
      <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
        <input type="email" placeholder="Địa chỉ email của bạn" class="flex-1 bg-[#262833] border border-[#3a3d49] rounded-md px-4 py-3 text-white placeholder-[#6b7280] focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
        <button class="bg-[#F53003] text-white font-medium px-6 py-3 rounded-md hover:bg-opacity-90 transition">Đăng ký</button>
      </div>
    </div>
  </section>
@endsection
