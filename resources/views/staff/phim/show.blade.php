@extends('admin.layout')

@section('title', 'Chi tiết Phim - Staff')
@section('page-title', 'Chi tiết Phim')
@section('page-description', 'Xem thông tin chi tiết phim')

@section('content')
<div class="space-y-6">
    <!-- Movie Info -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Poster -->
            <div class="lg:w-1/4">
                <div class="aspect-[2/3] bg-[#262833] rounded-lg overflow-hidden">
                    @if($movie->poster)
                        <img src="{{ asset('storage/' . $movie->poster) }}" alt="{{ $movie->ten_phim }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-film text-6xl text-[#a6a6b0]"></i>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Movie Details -->
            <div class="lg:w-3/4 space-y-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $movie->ten_phim }}</h1>
                    <p class="text-lg text-[#a6a6b0]">{{ $movie->ten_phim_en }}</p>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    @if($movie->the_loai)
                        @foreach(explode(',', $movie->the_loai) as $genre)
                            <span class="px-3 py-1 bg-[#262833] text-[#a6a6b0] rounded-full text-sm">{{ trim($genre) }}</span>
                        @endforeach
                    @endif
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Đạo diễn:</span>
                            <span class="text-white">{{ $movie->dao_dien ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Diễn viên:</span>
                            <span class="text-white">{{ $movie->dien_vien ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Thời lượng:</span>
                            <span class="text-white">{{ $movie->thoi_luong }} phút</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Khởi chiếu:</span>
                            <span class="text-white">{{ $movie->khoi_chieu ? date('d/m/Y', strtotime($movie->khoi_chieu)) : 'N/A' }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Quốc gia:</span>
                            <span class="text-white">{{ $movie->quoc_gia ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Ngôn ngữ:</span>
                            <span class="text-white">{{ $movie->ngon_ngu ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Phân loại:</span>
                            <span class="text-white">{{ $movie->phan_loai ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Trạng thái:</span>
                            @if($movie->trang_thai == 1)
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Đang chiếu</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Ngừng chiếu</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($movie->mo_ta)
                <div>
                    <h3 class="text-lg font-semibold text-white mb-2">Nội dung phim</h3>
                    <p class="text-[#a6a6b0] leading-relaxed">{{ $movie->mo_ta }}</p>
                </div>
                @endif
                
                @if($movie->trailer)
                <div>
                    <h3 class="text-lg font-semibold text-white mb-2">Trailer</h3>
                    <div class="aspect-video bg-[#262833] rounded-lg overflow-hidden">
                        <iframe src="{{ $movie->trailer }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Back Button -->
    <div class="flex justify-start">
        <a href="{{ route('staff.movies.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại danh sách
        </a>
    </div>
</div>
@endsection
