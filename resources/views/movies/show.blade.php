@extends('layouts.app')

@section('title', $movie->ten_phim . ' - MovieHub')

@section('content')
  <div class="max-w-6xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
      <!-- Left: Poster + trailer -->
      <div class="lg:col-span-1">
        <div class="bg-[#111214] border border-[#262833] rounded-lg overflow-hidden p-4">
          <img src="{{ $movie->poster_url ?? asset('images/no-poster.svg') }}" alt="{{ $movie->ten_phim }}" class="w-full rounded" onerror="this.src='{{ asset('images/no-poster.svg') }}'">
        </div>

        @if(!empty($movie->trailer))
          <div class="mt-4">
            @php
              // Convert common YouTube watch URL to embed if possible
              $embed = $movie->trailer;
              if (Str::contains($movie->trailer, 'youtube.com/watch')) {
                  $embed = str_replace('watch?v=', 'embed/', $movie->trailer);
              }
              if (Str::contains($movie->trailer, 'youtu.be/')) {
                  $parts = explode('youtu.be/', $movie->trailer);
                  $embed = 'https://www.youtube.com/embed/' . ($parts[1] ?? '');
              }
            @endphp
            <div class="aspect-w-16 aspect-h-9 rounded overflow-hidden border border-[#262833]">
              <iframe src="{{ $embed }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
            </div>
          </div>
        @endif
      </div>

      <!-- Right: Details -->
      <div class="lg:col-span-3">
        <h1 class="text-4xl font-bold mb-2">{{ $movie->ten_phim }}</h1>
        @if($movie->ten_goc)
          <div class="text-sm text-[#a6a6b0] mb-3">Tên gốc: {{ $movie->ten_goc }}</div>
        @endif

        <div class="flex flex-wrap items-center gap-4 mb-4">
          <div class="text-sm text-[#a6a6b0]">{{ $movie->the_loai }}</div>
          <div class="text-sm text-[#a6a6b0]">•</div>
          <div class="text-sm text-[#a6a6b0]">{{ $movie->do_dai }} phút</div>
          @if($movie->ngay_khoi_chieu)
            <div class="text-sm text-[#a6a6b0]">•</div>
            <div class="text-sm text-[#a6a6b0]">Khởi chiếu: {{ \Carbon\Carbon::parse($movie->ngay_khoi_chieu)->format('d/m/Y') }}</div>
          @endif
        </div>

        <div class="prose prose-invert mb-6">{!! nl2br(e($movie->mo_ta)) !!}</div>

        <div class="flex items-center gap-4 mb-6">
          @if($movie->trang_thai === 'dang_chieu')
            <a href="{{ route('booking', $movie->id) }}" class="bg-[#F53003] text-white px-4 py-2 rounded-md">Đặt vé</a>
          @else
            <span class="px-4 py-2 bg-gray-600 text-white rounded-md">Sắp chiếu</span>
          @endif

          <div class="flex items-center gap-2 text-yellow-400">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <span class="text-white">{{ number_format($movie->diem_danh_gia ?? 0, 1) }}</span>
            <span class="text-sm text-[#a6a6b0]">({{ $movie->so_luot_danh_gia ?? 0 }} đánh giá)</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-semibold mb-2">Đạo diễn</h3>
            <p class="text-[#a6a6b0]">{{ $movie->dao_dien ?? 'Chưa cập nhật' }}</p>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-2">Diễn viên</h3>
            <p class="text-[#a6a6b0]">{{ $movie->dien_vien ?? 'Chưa cập nhật' }}</p>
          </div>
        </div>

        <!-- Showtimes -->
        <div class="mt-8">
          <h3 class="text-xl font-semibold mb-3">Lịch chiếu</h3>
          @php
            $grouped = $movie->suatChieu->groupBy(function($s) { return \Carbon\Carbon::parse($s->thoi_gian_bat_dau)->format('Y-m-d'); });
          @endphp

          @if($movie->suatChieu->isEmpty())
            <div class="text-gray-400">Chưa có lịch chiếu cho phim này.</div>
          @else
            <div class="space-y-4">
              @foreach($grouped as $date => $shows)
                <div class="bg-[#0f1113] border border-[#262833] rounded p-4">
                  <div class="font-medium mb-2">{{ \Carbon\Carbon::parse($date)->format('l, d/m/Y') }}</div>
                  <div class="flex flex-wrap gap-3">
                    @foreach($shows as $s)
                      <a href="{{ route('booking', $movie->id) }}" class="px-3 py-2 bg-[#1b1d24] border border-[#262833] rounded text-sm hover:bg-[#222533]">{{ \Carbon\Carbon::parse($s->thoi_gian_bat_dau)->format('H:i') }} • {{ $s->phongChieu->ten_phong ?? 'Phòng' }}</a>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
