@extends('admin.layout')

@section('title', 'Chi tiết Khuyến mãi - Staff')
@section('page-title', 'Chi tiết Khuyến mãi')
@section('page-description', 'Xem thông tin chi tiết khuyến mãi')

@section('content')
<div class="space-y-6">
    <!-- Promotion Info -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">{{ $khuyenMai->ten_khuyen_mai }}</h2>
            <div class="flex items-center gap-2">
                @if($khuyenMai->ngay_bat_dau <= now() && $khuyenMai->ngay_ket_thuc >= now())
                    <span class="px-3 py-1 text-sm bg-green-500/20 text-green-400 rounded">Đang diễn ra</span>
                @elseif($khuyenMai->ngay_bat_dau > now())
                    <span class="px-3 py-1 text-sm bg-orange-500/20 text-orange-400 rounded">Sắp diễn ra</span>
                @else
                    <span class="px-3 py-1 text-sm bg-red-500/20 text-red-400 rounded">Đã kết thúc</span>
                @endif
            </div>
        </div>
        
        @if($khuyenMai->anh)
        <div class="mb-6">
            <img src="{{ asset('storage/' . $khuyenMai->anh) }}" alt="{{ $khuyenMai->ten_khuyen_mai }}" class="h-48 w-full object-cover rounded-lg">
        </div>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin cơ bản</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Mã giảm giá:</span>
                        <span class="text-white font-medium">{{ $khuyenMai->ma_giam_gia ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Loại giảm giá:</span>
                        <span class="text-white">
                            @if($khuyenMai->loai_giam_gia == 'phan_tram')
                                Giảm {{ $khuyenMai->gia_tri_giam }}%
                            @else
                                Giảm {{ number_format($khuyenMai->gia_tri_giam, 0, ',', '.') }} đ
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Giảm tối đa:</span>
                        <span class="text-white">
                            {{ $khuyenMai->gia_tri_toi_da ? number_format($khuyenMai->gia_tri_toi_da, 0, ',', '.') . ' đ' : 'Không giới hạn' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Đơn hàng tối thiểu:</span>
                        <span class="text-white">
                            {{ $khuyenMai->don_hang_toi_thieu ? number_format($khuyenMai->don_hang_toi_thieu, 0, ',', '.') . ' đ' : 'Không yêu cầu' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thời gian áp dụng</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ngày bắt đầu:</span>
                        <span class="text-white">{{ $khuyenMai->ngay_bat_dau ? date('d/m/Y H:i:s', strtotime($khuyenMai->ngay_bat_dau)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ngày kết thúc:</span>
                        <span class="text-white">{{ $khuyenMai->ngay_ket_thuc ? date('d/m/Y H:i:s', strtotime($khuyenMai->ngay_ket_thuc)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Số lần sử dụng:</span>
                        <span class="text-white">
                            {{ $khuyenMai->so_luong_su_dung ?? 0 }} / {{ $khuyenMai->so_luong_toi_da ?? 'Không giới hạn' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ngày tạo:</span>
                        <span class="text-white">{{ $khuyenMai->ngay_tao ? date('d/m/Y H:i:s', strtotime($khuyenMai->ngay_tao)) : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        @if($khuyenMai->mo_ta)
        <div class="mt-6">
            <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Mô tả</h3>
            <p class="text-white">{{ $khuyenMai->mo_ta }}</p>
        </div>
        @endif
        
        @if($khuyenMai->dieu_kien)
        <div class="mt-6">
            <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Điều kiện áp dụng</h3>
            <p class="text-white">{{ $khuyenMai->dieu_kien }}</p>
        </div>
        @endif
    </div>

    <!-- Usage Statistics -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Thống kê sử dụng</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-400">{{ $khuyenMai->so_luong_su_dung ?? 0 }}</div>
                <div class="text-sm text-[#a6a6b0]">Lượt sử dụng</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-400">{{ $khuyenMai->so_luong_toi_da ?? '∞' }}</div>
                <div class="text-sm text-[#a6a6b0]">Số lần tối đa</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-400">
                    {{ $khuyenMai->so_luong_toi_da ? ($khuyenMai->so_luong_toi_da - ($khuyenMai->so_luong_su_dung ?? 0)) : '∞' }}
                </div>
                <div class="text-sm text-[#a6a6b0]">Còn lại</div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
        <a href="{{ route('staff.khuyen-mai.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>
</div>
@endsection
