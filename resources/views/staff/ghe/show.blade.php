@extends('admin.layout')

@section('title', 'Chi tiết Ghế - Staff')
@section('page-title', 'Chi tiết Ghế')
@section('page-description', 'Xem thông tin chi tiết ghế')

@section('content')
<div class="space-y-6">
    <!-- Seat Info -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">Ghế #{{ $ghe->so_ghe }}</h2>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-sm bg-gray-500/20 text-gray-400 rounded">Không xác định</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin ghế</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">ID:</span>
                        <span class="text-white font-medium">{{ $ghe->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Số ghế:</span>
                        <span class="text-white">{{ $ghe->so_ghe ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Hàng ghế:</span>
                        <span class="text-white">{{ $ghe->hang_ghe ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Loại ghế:</span>
                        <span class="text-white">
                            <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded">Không xác định</span>
                        </span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin phòng chiếu</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Phòng chiếu:</span>
                        <span class="text-white">{{ $ghe->phongChieu->ten_phong ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Loại phòng:</span>
                        <span class="text-white">{{ $ghe->phongChieu->loai_phong ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Số ghế trong phòng:</span>
                        <span class="text-white">{{ $ghe->phongChieu->so_luong_ghe ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Trạng thái phòng:</span>
                        <span class="text-white">
                            @if($ghe->phongChieu->status == 'active' || $ghe->phongChieu->status == 'hoạt động' || $ghe->phongChieu->status == 1)
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Hoạt động</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Ngừng hoạt động</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin trạng thái</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Trạng thái ghế:</span>
                        <span class="text-white">
                            <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded">Không xác định</span>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ngày tạo:</span>
                        <span class="text-white">{{ $ghe->created_at ? date('d/m/Y H:i:s', strtotime($ghe->created_at)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Cập nhật:</span>
                        <span class="text-white">{{ $ghe->updated_at ? date('d/m/Y H:i:s', strtotime($ghe->updated_at)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Người tạo:</span>
                        <span class="text-white">Admin</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin sử dụng</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Số lần đặt:</span>
                        <span class="text-white">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Lần đặt cuối:</span>
                        <span class="text-white">Chưa có</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Doanh thu:</span>
                        <span class="text-white font-medium">0 đ</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Tỷ lệ đặt:</span>
                        <span class="text-white">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seat Visual -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Biểu tượng ghế</h3>
        <div class="flex justify-center">
            <div class="w-16 h-16 bg-gray-500/20 border-2 border-gray-500 rounded flex items-center justify-center">
                <i class="fas fa-chair text-2xl text-gray-400"></i>
            </div>
        </div>
        <p class="text-center text-[#a6a6b0] mt-4">Ghế {{ $ghe->so_ghe }} - {{ $ghe->hang_ghe }}</p>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Lịch sử đặt ghế</h3>
        <div class="text-center py-8 text-[#a6a6b0]">
            <i class="fas fa-ticket-alt text-4xl mb-2"></i>
            <p>Chưa có lịch sử đặt ghế</p>
        </div>
    </div>

    <!-- Room Layout -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Sơ đồ phòng chiếu</h3>
        <div class="text-center py-8 text-[#a6a6b0]">
            <i class="fas fa-th text-4xl mb-2"></i>
            <p>Sơ đồ phòng chiếu sẽ được hiển thị tại đây</p>
            <p class="text-sm mt-2">Ghế hiện tại được đánh dấu</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
        <a href="{{ route('staff.ghe.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>
</div>
@endsection
