@extends('layouts.main')

@section('title', 'Danh sách phim - MovieHub')

@section('content')
<div class="min-h-screen bg-[#0F1117] py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Danh sách phim</h1>
            <p class="text-[#a6a6b0]">Khám phá bộ sưu tập phim đa dạng của chúng tôi</p>
        </div>

        <!-- Filters -->
        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 mb-6">
            <form method="GET" action="{{ route('public.movies') }}" class="flex flex-col md:flex-row gap-4">
                <!-- Status Filter -->
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-white mb-2">Trạng thái</label>
                    <select name="status" class="w-full px-4 py-2 bg-[#1a1d24] border border-[#2A2F3A] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                        <option value="dang_chieu" {{ $status === 'dang_chieu' ? 'selected' : '' }}>Đang chiếu</option>
                        <option value="sap_chieu" {{ $status === 'sap_chieu' ? 'selected' : '' }}>Sắp chiếu</option>
                        <option value="ngung_chieu" {{ $status === 'ngung_chieu' ? 'selected' : '' }}>Ngừng chiếu</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-white mb-2">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Tên phim, đạo diễn, diễn viên..."
                           class="w-full px-4 py-2 bg-[#1a1d24] border border-[#2A2F3A] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                </div>

                <!-- Sort -->
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-white mb-2">Sắp xếp</label>
                    <select name="sort" class="w-full px-4 py-2 bg-[#1a1d24] border border-[#2A2F3A] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                        <option value="ngay_khoi_chieu" {{ request('sort') === 'ngay_khoi_chieu' ? 'selected' : '' }}>Ngày khởi chiếu</option>
                        <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Đánh giá</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Tên A-Z</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full md:w-auto px-6 py-2 bg-gradient-to-r from-[#F53003] to-orange-400 text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                        <i class="fas fa-search mr-2"></i>
                        Tìm kiếm
                    </button>
                </div>
            </form>
        </div>

        <!-- Movies Grid -->
        @if($movies->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-8">
                @foreach($movies as $movie)
                    <a href="{{ route('movie-detail', $movie->id) }}" 
                       class="group bg-[#161A23] border border-[#2A2F3A] rounded-xl overflow-hidden hover:border-[#F53003] transition-all duration-300">
                        <div class="relative overflow-hidden">
                            <x-image 
                              src="{{ $movie->poster_url ?? $movie->poster }}" 
                              alt="{{ $movie->ten_phim }}"
                              aspectRatio="2/3"
                              class="w-full"
                              quality="high"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/0 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10"></div>
                            
                            <!-- Rating Badge -->
                            @if($movie->diem_danh_gia)
                                <div class="absolute top-2 right-2 bg-yellow-500 text-black px-2 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-star"></i> {{ number_format($movie->diem_danh_gia, 1) }}
                                </div>
                            @endif

                            <!-- Status Badge -->
                            <div class="absolute top-2 left-2">
                                @if($movie->trang_thai === 'dang_chieu')
                                    <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold">Đang chiếu</span>
                                @elseif($movie->trang_thai === 'sap_chieu')
                                    <span class="bg-blue-500 text-white px-2 py-1 rounded-full text-xs font-semibold">Sắp chiếu</span>
                                @endif
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-white font-semibold text-sm line-clamp-2 group-hover:text-[#F53003] transition-colors mb-1">
                                {{ $movie->ten_phim }}
                            </h3>
                            <p class="text-[#a6a6b0] text-xs">
                                {{ $movie->formatted_duration ?? ($movie->do_dai ?? 0) . ' phút' }}
                                @if($movie->do_tuoi) • {{ $movie->do_tuoi }} @endif
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $movies->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <i class="fas fa-film text-6xl text-[#a6a6b0] mb-4"></i>
                <p class="text-[#a6a6b0] text-lg">Không tìm thấy phim nào</p>
            </div>
        @endif
    </div>
</div>
@endsection

