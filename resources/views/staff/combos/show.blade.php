@extends('admin.layout')

@section('title', 'Chi tiết Combo - Staff')
@section('page-title', 'Chi tiết Combo')
@section('page-description', 'Xem thông tin chi tiết combo')

@section('content')
<div class="space-y-6">
    <!-- Combo Info -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">{{ $combo->ten_combo }}</h2>
            <div class="flex items-center gap-2">
                @if($combo->trang_thai == 'active')
                    <span class="px-3 py-1 text-sm bg-green-500/20 text-green-400 rounded">Đang bán</span>
                @else
                    <span class="px-3 py-1 text-sm bg-red-500/20 text-red-400 rounded">Ngừng bán</span>
                @endif
            </div>
        </div>
        
        @if($combo->anh)
        <div class="mb-6">
            <img src="{{ asset('storage/' . $combo->anh) }}" alt="{{ $combo->ten_combo }}" class="h-48 w-full object-cover rounded-lg">
        </div>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin cơ bản</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">ID:</span>
                        <span class="text-white font-medium">{{ $combo->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Tên combo:</span>
                        <span class="text-white">{{ $combo->ten_combo ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Giá:</span>
                        <span class="text-white font-medium">{{ number_format($combo->gia ?? 0, 0, ',', '.') }} đ</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Trạng thái:</span>
                        <span class="text-white">
                            @if($combo->trang_thai == 'active')
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Đang bán</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Ngừng bán</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin thời gian</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ngày tạo:</span>
                        <span class="text-white">{{ $combo->created_at ? date('d/m/Y H:i:s', strtotime($combo->created_at)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ngày cập nhật:</span>
                        <span class="text-white">{{ $combo->updated_at ? date('d/m/Y H:i:s', strtotime($combo->updated_at)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Người tạo:</span>
                        <span class="text-white">Admin</span>
                    </div>
                </div>
            </div>
        </div>
        
        @if($combo->mo_ta)
        <div class="mt-6">
            <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Mô tả</h3>
            <p class="text-white">{{ $combo->mo_ta }}</p>
        </div>
        @endif
        
        @if($combo->chi_tiet)
        <div class="mt-6">
            <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Chi tiết combo</h3>
            <p class="text-white">{{ $combo->chi_tiet }}</p>
        </div>
        @endif
    </div>

    <!-- Combo Statistics -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Thống kê sử dụng</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-400">0</div>
                <div class="text-sm text-[#a6a6b0]">Lượt đã đặt</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-400">{{ number_format($combo->gia ?? 0, 0, ',', '.') }}</div>
                <div class="text-sm text-[#a6a6b0]">Giá combo</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-400">
                    @if($combo->trang_thai == 'active')
                        Đang bán
                    @else
                        Ngừng bán
                    @endif
                </div>
                <div class="text-sm text-[#a6a6b0]">Trạng thái</div>
            </div>
        </div>
    </div>

    <!-- Recent Orders (Placeholder) -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Đặt vé gần đây sử dụng combo này</h3>
        <div class="text-center py-8 text-[#a6a6b0]">
            <i class="fas fa-popcorn text-4xl mb-2"></i>
            <p>Chưa có dữ liệu đặt vé sử dụng combo này</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
        <a href="{{ route('staff.combos.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>
</div>
@endsection
