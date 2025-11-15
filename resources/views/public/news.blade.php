@extends('layouts.main')

@section('title', 'Tin tức - MovieHub')

@section('content')
<div class="min-h-screen bg-[#0F1117] py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Tin tức</h1>
            <p class="text-[#a6a6b0]">Cập nhật tin tức mới nhất về phim ảnh và rạp chiếu</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Search & Filter -->
                <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 mb-6">
                    <form method="GET" action="{{ route('public.news') }}" class="flex flex-col md:flex-row gap-4">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Tìm kiếm tin tức..."
                               class="flex-1 px-4 py-2 bg-[#1a1d24] border border-[#2A2F3A] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                        @if($categories->count() > 0)
                            <select name="category" class="px-4 py-2 bg-[#1a1d24] border border-[#2A2F3A] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                                <option value="">Tất cả thể loại</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <button type="submit" class="px-6 py-2 bg-gradient-to-r from-[#F53003] to-orange-400 text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- News Grid -->
                @if($news->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        @foreach($news as $article)
                            <a href="{{ route('public.news.detail', $article->slug) }}" 
                               class="group bg-[#161A23] border border-[#2A2F3A] rounded-xl overflow-hidden hover:border-[#F53003] transition-all">
                                <div class="relative h-48 overflow-hidden">
                                    <x-image 
                                      src="{{ $article->image_url ?? $article->hinh_anh }}" 
                                      alt="{{ $article->tieu_de }}"
                                      aspectRatio="16/9"
                                      class="w-full h-full"
                                      quality="high"
                                    />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent pointer-events-none z-10"></div>
                                    @if($article->noi_bat)
                                        <div class="absolute top-2 right-2 bg-yellow-500 text-black px-3 py-1 rounded-full text-xs font-bold">
                                            <i class="fas fa-star"></i> Nổi bật
                                        </div>
                                    @endif
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center gap-3 text-xs text-[#a6a6b0] mb-2">
                                        <span>{{ $article->the_loai ?? 'Tin tức' }}</span>
                                        <span>•</span>
                                        <span>{{ $article->ngay_dang->format('d/m/Y') }}</span>
                                        <span>•</span>
                                        <span><i class="fas fa-eye"></i> {{ number_format($article->luot_xem) }}</span>
                                    </div>
                                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-[#F53003] transition-colors line-clamp-2">
                                        {{ $article->tieu_de }}
                                    </h3>
                                    @if($article->tom_tat)
                                        <p class="text-[#a6a6b0] text-sm line-clamp-2 mb-4">{{ $article->tom_tat }}</p>
                                    @endif
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#F53003] text-sm font-semibold group-hover:underline">
                                            Đọc thêm <i class="fas fa-arrow-right ml-1"></i>
                                        </span>
                                        @if($article->tac_gia)
                                            <span class="text-xs text-[#a6a6b0]">{{ $article->tac_gia }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-center">
                        {{ $news->links() }}
                    </div>
                @else
                    <div class="text-center py-16">
                        <i class="fas fa-newspaper text-6xl text-[#a6a6b0] mb-4"></i>
                        <p class="text-[#a6a6b0] text-lg">Không tìm thấy tin tức nào</p>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Featured News -->
                @if($featuredNews->count() > 0)
                    <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 sticky top-6">
                        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span>Tin nổi bật</span>
                        </h3>
                        <div class="space-y-4">
                            @foreach($featuredNews as $article)
                                <a href="{{ route('public.news.detail', $article->slug) }}" 
                                   class="block group">
                                    <div class="flex gap-3">
                                        <x-image 
                                          src="{{ $article->image_url ?? $article->hinh_anh }}" 
                                          alt="{{ $article->tieu_de }}"
                                          aspectRatio="1/1"
                                          class="w-20 h-20 rounded-lg"
                                          quality="medium"
                                        />
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-white group-hover:text-[#F53003] transition-colors line-clamp-2 mb-1">
                                                {{ $article->tieu_de }}
                                            </h4>
                                            <p class="text-xs text-[#a6a6b0]">{{ $article->ngay_dang->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Categories -->
                @if($categories->count() > 0)
                    <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                        <h3 class="text-xl font-bold text-white mb-4">Thể loại</h3>
                        <div class="space-y-2">
                            <a href="{{ route('public.news') }}" 
                               class="block px-4 py-2 rounded-lg {{ !request('category') ? 'bg-[#F53003] text-white' : 'bg-[#1a1d24] text-[#a6a6b0] hover:bg-[#2A2F3A]' }} transition-colors">
                                Tất cả
                            </a>
                            @foreach($categories as $category)
                                <a href="{{ route('public.news', ['category' => $category]) }}" 
                                   class="block px-4 py-2 rounded-lg {{ request('category') === $category ? 'bg-[#F53003] text-white' : 'bg-[#1a1d24] text-[#a6a6b0] hover:bg-[#2A2F3A]' }} transition-colors">
                                    {{ $category }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

