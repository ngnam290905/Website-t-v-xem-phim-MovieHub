@extends('layouts.app')

@section('title', $title . ' - MovieHub')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">{{ $title }}</h1>
        @isset($description)
        <p class="text-gray-400">{{ $description }}</p>
        @endisset
    </div>

    <!-- Genre Filter -->
    @if(!isset($currentGenre))
    <div class="mb-8">
        <div class="flex flex-wrap gap-2">
            @php
                $genres = [
                    'hanh-dong' => 'Hành động',
                    'tinh-cam' => 'Tình cảm',
                    'hai-huoc' => 'Hài hước',
                    'kinh-di' => 'Kinh dị',
                    'vien-tuong' => 'Viễn tưởng',
                    'phieu-luu' => 'Phiêu lưu',
                    'hoat-hinh' => 'Hoạt hình',
                    'tai-lieu' => 'Tài liệu',
                    'hinh-su' => 'Hình sự',
                    'than-thoai' => 'Thần thoại'
                ];
            @endphp
            <a href="{{ route('movies.index') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeTab === 'all' ? 'bg-[#F53003] text-white' : 'bg-[#1b1d24] text-gray-300 hover:bg-[#262833]' }}">
                Tất cả
            </a>
            <a href="{{ route('movies.now-showing') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeTab === 'now-showing' ? 'bg-[#F53003] text-white' : 'bg-[#1b1d24] text-gray-300 hover:bg-[#262833]' }}">
                Đang chiếu
            </a>
            <a href="{{ route('movies.coming-soon') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeTab === 'coming-soon' ? 'bg-[#F53003] text-white' : 'bg-[#1b1d24] text-gray-300 hover:bg-[#262833]' }}">
                Sắp chiếu
            </a>
            <a href="{{ route('movies.hot') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeTab === 'hot' ? 'bg-[#F53003] text-white' : 'bg-[#1b1d24] text-gray-300 hover:bg-[#262833]' }}">
                Phim hot
            </a>
            @foreach($genres as $slug => $genre)
            <a href="{{ route('movies.by-genre', $slug) }}" class="px-4 py-2 rounded-full text-sm font-medium {{ (isset($currentGenre) && $currentGenre === $genre) ? 'bg-[#F53003] text-white' : 'bg-[#1b1d24] text-gray-300 hover:bg-[#262833]' }}">
                {{ $genre }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Pagination -->
    @if($movies->hasPages())
    <div class="mt-8">
        {{ $movies->links() }}
    </div>
    @endif

    <!-- Movie Grid -->
    <!-- Movie Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($movies as $movie)
        <div class="group bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden transition-all duration-300 hover:border-[#F53003]/50 hover:shadow-lg hover:shadow-[#F53003]/10">
            <div class="relative overflow-hidden">
                <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" class="w-full h-80 object-cover transition-transform duration-500 group-hover:scale-105">
                @if($movie->trang_thai === 'sap_chieu')
                <div class="absolute top-2 left-2 bg-yellow-500 text-black text-xs font-bold px-2 py-1 rounded">Sắp chiếu</div>
                @elseif($movie->trang_thai === 'dang_chieu')
                <div class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">Đang chiếu</div>
                @endif
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
        @empty
        <div class="col-span-full text-center py-8">
            <p class="text-gray-400">Không có phim nào được tìm thấy.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($movies->hasPages())
    <div class="mt-8">
        {{ $movies->links() }}
    </div>
    @endif
</div>

@if($movies->count() === 0)
<div class="text-center py-12">
    <div class="text-5xl mb-4">🎬</div>
        <h3 class="text-xl font-medium text-white mb-2">Không tìm thấy phim</h3>
        <p class="text-gray-400">Không có phim nào phù hợp với bộ lọc hiện tại.</p>
    </div>
    @endif
</div>
@endsection
