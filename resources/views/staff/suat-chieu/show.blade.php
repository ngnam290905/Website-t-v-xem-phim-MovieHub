@extends('admin.layout')

@section('title', 'Chi tiết Suất chiếu - Staff')
@section('page-title', 'Chi tiết Suất chiếu')
@section('page-description', 'Xem thông tin chi tiết suất chiếu')

@section('content')
<div class="space-y-6">
    <!-- Showtime Info -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">Suất chiếu #{{ $suatChieu->id }}</h2>
            <div class="flex items-center gap-2">
                @if($suatChieu->thoi_gian_bat_dau > now())
                    <span class="px-3 py-1 text-sm bg-blue-500/20 text-blue-400 rounded">Sắp chiếu</span>
                @else
                    <span class="px-3 py-1 text-sm bg-gray-500/20 text-gray-400 rounded">Đã chiếu</span>
                @endif
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin suất chiếu</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">ID:</span>
                        <span class="text-white font-medium">{{ $suatChieu->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Thời gian bắt đầu:</span>
                        <span class="text-white">{{ $suatChieu->thoi_gian_bat_dau ? date('d/m/Y H:i:s', strtotime($suatChieu->thoi_gian_bat_dau)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Thời gian kết thúc:</span>
                        <span class="text-white">{{ $suatChieu->thoi_gian_ket_thuc ? date('d/m/Y H:i:s', strtotime($suatChieu->thoi_gian_ket_thuc)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Giá vé:</span>
                        <span class="text-white font-medium">{{ number_format($suatChieu->gia_ve ?? 0, 0, ',', '.') }} đ</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin phim</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Tên phim:</span>
                        <span class="text-white">{{ $suatChieu->phim->ten_phim ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Thể loại:</span>
                        <span class="text-white">{{ $suatChieu->phim->the_loai ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Thời lượng:</span>
                        <span class="text-white">{{ $suatChieu->phim->thoi_luong ?? 'N/A' }} phút</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Đạo diễn:</span>
                        <span class="text-white">{{ $suatChieu->phim->dao_dien ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin phòng chiếu</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Phòng chiếu:</span>
                        <span class="text-white">{{ $suatChieu->phongChieu->ten_phong ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Loại phòng:</span>
                        <span class="text-white">{{ $suatChieu->phongChieu->loai_phong ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Số lượng ghế:</span>
                        <span class="text-white">{{ $suatChieu->phongChieu->so_luong_ghe ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Trạng thái phòng:</span>
                        <span class="text-white">
                            @if($suatChieu->phongChieu->status == 'active' || $suatChieu->phongChieu->status == 'hoạt động' || $suatChieu->phongChieu->status == 1)
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Hoạt động</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Ngừng hoạt động</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thống kê suất chiếu</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Tổng ghế:</span>
                        <span class="text-white">{{ $suatChieu->phongChieu->so_luong_ghe ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ghế đã đặt:</span>
                        <span class="text-white">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ghế còn trống:</span>
                        <span class="text-white">{{ $suatChieu->phongChieu->so_luong_ghe ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Doanh thu:</span>
                        <span class="text-white font-medium">0 đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Movie Poster -->
    @if($suatChieu->phim->poster)
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Poster phim</h3>
        <div class="flex justify-center">
            <img src="{{ asset('storage/' . $suatChieu->phim->poster) }}" alt="{{ $suatChieu->phim->ten_phim }}" class="h-64 object-cover rounded-lg">
        </div>
    </div>
    @endif

    <!-- Seat Map (Placeholder) -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Sơ đồ ghế ngồi</h3>
        <div class="text-center py-8 text-[#a6a6b0]">
            <i class="fas fa-chair text-4xl mb-2"></i>
            <p>Sơ đồ ghế ngồi sẽ được hiển thị tại đây</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
        <a href="{{ route('staff.suat-chieu.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>
</div>
@endsection
