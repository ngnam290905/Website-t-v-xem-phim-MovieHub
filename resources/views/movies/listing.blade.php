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

    <!-- Advanced Filters -->
    <div class="mb-8 bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Bộ lọc nâng cao</h3>
            <button id="toggle-filters" class="text-[#a6a6b0] hover:text-white">
                <i class="fas fa-filter"></i>
            </button>
        </div>
        
        <form method="GET" action="{{ route('movies.index') }}" id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Status Filter -->
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Trạng thái</label>
                <select name="status" class="w-full px-4 py-2 bg-[#151822] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                    <option value="">Tất cả</option>
                    <option value="dang_chieu" {{ request('status') === 'dang_chieu' ? 'selected' : '' }}>🔴 Đang chiếu</option>
                    <option value="sap_chieu" {{ request('status') === 'sap_chieu' ? 'selected' : '' }}>🟡 Sắp chiếu</option>
                </select>
            </div>

            <!-- Genre Filter -->
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Thể loại</label>
                <select name="genre" class="w-full px-4 py-2 bg-[#151822] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                    <option value="">Tất cả thể loại</option>
                    @php
                        $allGenres = \App\Models\Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
                            ->whereNotNull('the_loai')
                            ->where('the_loai', '!=', '')
                            ->pluck('the_loai')
                            ->flatMap(function($item) {
                                return array_map('trim', explode(',', $item));
                            })
                            ->unique()
                            ->sort()
                            ->values();
                    @endphp
                    @foreach($allGenres as $genre)
                        <option value="{{ $genre }}" {{ request('genre') === $genre ? 'selected' : '' }}>{{ $genre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Country Filter -->
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Quốc gia</label>
                <select name="country" class="w-full px-4 py-2 bg-[#151822] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                    <option value="">Tất cả quốc gia</option>
                    @php
                        $countries = \App\Models\Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
                            ->whereNotNull('quoc_gia')
                            ->where('quoc_gia', '!=', '')
                            ->pluck('quoc_gia')
                            ->unique()
                            ->sort()
                            ->values();
                    @endphp
                    @foreach($countries as $country)
                        <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>{{ $country }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Age Rating Filter -->
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Độ tuổi</label>
                <select name="age" class="w-full px-4 py-2 bg-[#151822] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                    <option value="">Tất cả</option>
                    <option value="P" {{ request('age') === 'P' ? 'selected' : '' }}>P - Mọi lứa tuổi</option>
                    <option value="C13" {{ request('age') === 'C13' ? 'selected' : '' }}>C13 - 13+</option>
                    <option value="C16" {{ request('age') === 'C16' ? 'selected' : '' }}>C16 - 16+</option>
                    <option value="C18" {{ request('age') === 'C18' ? 'selected' : '' }}>C18 - 18+</option>
                </select>
            </div>

            <!-- Sort By -->
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Sắp xếp theo</label>
                <select name="sort" class="w-full px-4 py-2 bg-[#151822] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                    <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Đánh giá cao</option>
                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Tên A-Z</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên phim, đạo diễn..." class="w-full px-4 py-2 bg-[#151822] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-[#F53003] hover:bg-[#e02a00] text-white rounded-lg font-semibold transition-all">
                    <i class="fas fa-search mr-2"></i>Lọc
                </button>
                <a href="{{ route('movies.index') }}" class="px-4 py-2 bg-[#262833] hover:bg-[#2f3240] text-white rounded-lg transition-all">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Filter Tabs -->
    <div class="mb-8">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('movies.index') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeTab === 'all' ? 'bg-[#F53003] text-white' : 'bg-[#1b1d24] text-gray-300 hover:bg-[#262833]' }}">
                Tất cả
            </a>
            <a href="{{ route('movies.now-showing') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeTab === 'now-showing' ? 'bg-[#F53003] text-white' : 'bg-[#1b1d24] text-gray-300 hover:bg-[#262833]' }}">
                🔴 Đang chiếu
            </a>
            <a href="{{ route('movies.coming-soon') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ $activeTab === 'coming-soon' ? 'bg-[#F53003] text-white' : 'bg-[#1b1d24] text-gray-300 hover:bg-[#262833]' }}">
                🟡 Sắp chiếu
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
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($movies as $movie)
        <div class="group bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden transition-all duration-300 hover:border-[#F53003]/50 hover:shadow-lg hover:shadow-[#F53003]/10">
            <div class="relative overflow-hidden">
                <img src="{{ $movie->poster_url }}" alt="{{ $movie->ten_phim }}" class="w-full h-80 object-cover transition-transform duration-500 group-hover:scale-105" onerror="this.src='{{ asset('images/no-poster.svg') }}'">
                <!-- Chuẩn hóa Badge -->
                <div class="absolute top-2 left-2 z-10 flex flex-col gap-2">
                    @if($movie->hot)
                        <span class="px-2 py-1 bg-gradient-to-r from-yellow-400 to-orange-400 text-black text-xs font-bold rounded uppercase shadow-lg">🔥 HOT</span>
                    @endif
                    @if($movie->trang_thai === 'dang_chieu')
                        <span class="px-2 py-1 bg-green-500 text-white text-xs font-bold rounded uppercase shadow-lg">🔴 Đang chiếu</span>
                    @elseif($movie->trang_thai === 'sap_chieu')
                        <span class="px-2 py-1 bg-yellow-500 text-black text-xs font-bold rounded uppercase shadow-lg">🟡 Sắp chiếu</span>
                    @endif
                </div>
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
