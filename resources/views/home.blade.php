@extends('layouts.app')

@section('title', 'MovieHub - Đặt vé xem phim')

@section('content')
  <section class="flex flex-col gap-8">
    <!-- Phim đang hot -->
    <div>
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Phim đang hot</h2>
        <a href="{{ route('movies.hot') }}" class="text-sm text-[#F53003] hover:underline flex items-center">
          Xem tất cả
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>
      
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        @forelse($hotMovies as $movie)
          <div class="group relative overflow-hidden rounded-lg">
            <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex flex-col justify-end p-4">
              <h3 class="font-semibold text-white">{{ $movie->ten_phim }}</h3>
              <p class="text-xs text-gray-300">{{ $movie->do_dai }} phút • {{ $movie->the_loai }}</p>
              <div class="mt-2 flex items-center">
                <span class="text-yellow-400">★</span>
                <span class="text-white ml-1">{{ number_format($movie->diem_danh_gia, 1) }}</span>
              </div>
              <a href="{{ route('movies.show', $movie->id) }}" class="mt-3 w-full bg-[#F53003] text-white text-center py-1.5 rounded-md text-sm font-medium hover:bg-opacity-90 transition">
                Xem chi tiết
              </a>
            </div>
            @if($movie->trang_thai === 'sap_chieu')
              <div class="absolute top-2 right-2 bg-yellow-500 text-black text-xs font-bold px-2 py-1 rounded">Sắp chiếu</div>
            @endif
          </div>
        @empty
          <div class="col-span-full text-center py-8">
            <p class="text-gray-400">Hiện chưa có phim nào nổi bật</p>
          </div>
        @endforelse
      </div>
    </div>

    <!-- Phim đang chiếu -->
    <div id="now" class="flex items-center justify-between mt-8">
      <h2 class="text-xl font-semibold">Phim đang chiếu</h2>
      <a href="{{ route('movies.now-showing') }}" class="text-sm text-[#F53003] hover:underline">Xem tất cả</a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @forelse($nowShowing as $movie)
        <div class="bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden flex flex-col group">
          <div class="relative overflow-hidden">
            <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" class="w-full aspect-[2/3] object-cover transition-transform duration-300 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
              <a href="{{ route('movies.show', $movie->id) }}" class="w-full bg-[#F53003] text-white text-center py-2 rounded-md text-sm font-medium hover:bg-opacity-90 transition">
                Xem chi tiết
              </a>
            </div>
          </div>
          <div class="p-4 flex-1 flex flex-col gap-3">
            <div>
              <h3 class="font-semibold">{{ $movie->ten_phim }}</h3>
              <p class="text-xs text-[#a6a6b0]">{{ $movie->do_dai }} phút • {{ $movie->the_loai }}</p>
            </div>
            <div class="mt-auto flex items-center justify-between">
              <div class="flex items-center">
                <span class="text-yellow-400">★</span>
                <span class="text-white ml-1 text-sm">{{ number_format($movie->diem_danh_gia, 1) }}</span>
              </div>
              <a href="{{ route('booking', $movie->id) }}" class="inline-flex items-center justify-center px-4 py-1.5 rounded-md bg-[#F53003] hover:opacity-90 transition text-white text-sm">
                Đặt vé
              </a>
            </div>
          </div>
          @if($movie->trang_thai === 'sap_chieu')
            <div class="absolute top-2 right-2 bg-yellow-500 text-black text-xs font-bold px-2 py-1 rounded">Sắp chiếu</div>
          @endif
        </div>
      @empty
        <div class="col-span-full text-center py-8">
          <p class="text-gray-400">Hiện chưa có phim nào đang chiếu</p>
        </div>
      @endforelse
    </div>
  </section>
@endsection


