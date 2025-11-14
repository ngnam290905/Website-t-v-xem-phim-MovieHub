@extends('admin.layout')

@section('title', 'Chi tiết Đặt Vé #' . $booking->id)

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Chi tiết Đặt Vé #{{ $booking->id }}</h1>
            <p class="text-[#a6a6b0]">Quản lý thông tin đặt vé của khách hàng</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="inline-flex items-center px-3 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]">
                <i class="fas fa-edit mr-2"></i> Chỉnh sửa
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Customer Information Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-user text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Thông tin khách hàng</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Họ và tên</p>
                    <p class="text-white font-medium">{{ $booking->nguoiDung->ho_ten ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Email</p>
                    <p class="text-white">{{ $booking->nguoiDung->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Số điện thoại</p>
                    <p class="text-white">{{ $booking->nguoiDung->so_dien_thoai ?? 'N/A' }}</p>
                </div>
            </div>
            
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Điểm tích lũy</p>
                    @if ($booking->nguoiDung?->diemThanhVien)
                        <div class="flex items-center gap-2">
                            <span class="text-white font-medium">{{ $booking->nguoiDung->diemThanhVien->tong_diem }} điểm</span>
                            <span class="text-xs text-[#a6a6b0]">(Hết hạn: {{ \Carbon\Carbon::parse($booking->nguoiDung->diemThanhVien->ngay_het_han)->format('d/m/Y') }})</span>
                        </div>
                    @else
                        <p class="text-[#a6a6b0]">Chưa có thông tin điểm</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Ngày đặt</p>
                    <p class="text-white">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Movie Information Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-film text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Thông tin phim & suất chiếu</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Tên phim</p>
                    <p class="text-white font-medium">{{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Phòng chiếu</p>
                    <p class="text-white">{{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}</p>
                </div>
            </div>
            
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Thời gian chiếu</p>
                    <p class="text-white font-medium">{{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Thời lượng</p>
                    <p class="text-white">{{ $booking->suatChieu?->phim?->do_dai ?? $booking->suatChieu?->phim?->thoi_luong ?? 'N/A' }} phút</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Status Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-check-circle text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Trạng thái đặt vé</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Trạng thái</p>
                @switch($booking->trang_thai)
                    @case(0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-300">
                            <i class="fas fa-clock mr-1"></i> Chờ xác nhận
                        </span>
                    @break
                    @case(1)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300">
                            <i class="fas fa-check mr-1"></i> Đã xác nhận
                        </span>
                    @break
                    @case(3)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-500/20 text-orange-300">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Yêu cầu hủy
                        </span>
                    @break
                    @case(2)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-300">
                            <i class="fas fa-times mr-1"></i> Đã hủy
                        </span>
                    @break
                    @default
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-400">
                            Không xác định
                        </span>
                @endswitch
            </div>
            
            <div>
                <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Thanh toán</p>
                @switch($booking->trang_thai_thanh_toan ?? 0)
                    @case(0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-400">
                            <i class="fas fa-wallet mr-1"></i> Chưa thanh toán
                        </span>
                    @break
                    @case(1)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300">
                            <i class="fas fa-check mr-1"></i> Đã thanh toán
                        </span>
                    @break
                    @case(2)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300">
                            <i class="fas fa-undo mr-1"></i> Đã hoàn tiền
                        </span>
                    @break
                    @default
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-400">
                            Không xác định
                        </span>
                @endswitch
            </div>
            
            <div>
                <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Phương thức thanh toán</p>
                <p class="text-white">{{ $booking->thanhToan?->phuong_thuc ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Seats Information Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-couch text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Ghế đã đặt</h2>
        </div>
        
        @if ($booking->chiTietDatVe->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-couch text-4xl text-[#a6a6b0] mb-3"></i>
                <p class="text-[#a6a6b0]">Không có ghế nào được đặt</p>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach ($booking->chiTietDatVe as $detail)
                    <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-3 text-center hover:border-[#F53003] transition-colors">
                        <div class="text-lg font-bold text-white mb-1">{{ $detail->ghe->id_loai ?? 'N/A' }}</div>
                        <div class="text-xs text-[#a6a6b0]">{{ $detail->ghe->loaiGhe->ten_loai ?? '' }}</div>
                        <div class="text-sm text-green-400 font-medium mt-1">{{ number_format($detail->gia_tien) }}đ</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Combo Information Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-pink-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-popcorn text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Combo đi kèm</h2>
        </div>
        
        @if ($booking->chiTietCombo->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-popcorn text-4xl text-[#a6a6b0] mb-3"></i>
                <p class="text-[#a6a6b0]">Không có combo nào được đặt</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($booking->chiTietCombo as $combo)
                    <div class="flex items-center justify-between bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-pink-600/20 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-popcorn text-pink-400 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">{{ $combo->combo->ten ?? 'N/A' }}</p>
                                <p class="text-xs text-[#a6a6b0]">Số lượng: {{ $combo->so_luong }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-green-400 font-medium">{{ number_format($combo->gia_tien) }}đ</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Pricing Summary Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-calculator text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Chi tiết thanh toán</h2>
        </div>
        
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-[#a6a6b0]">Tiền ghế:</span>
                <span class="text-white">{{ number_format($booking->chiTietDatVe->sum('gia_tien')) }}đ</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[#a6a6b0]">Tiền combo:</span>
                <span class="text-white">{{ number_format($booking->chiTietCombo->sum('gia_tien')) }}đ</span>
            </div>
            @if ($booking->khuyenMai)
                <div class="flex justify-between items-center">
                    <span class="text-[#a6a6b0]">Mã giảm giá ({{ $booking->khuyenMai->ma_km }}):</span>
                    <span class="text-red-400">-{{ number_format($booking->khuyenMai->gia_tri_giam ?? 0) }}đ</span>
                </div>
            @endif
            <div class="border-t border-[#262833] pt-3">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-white">Tổng cộng:</span>
                    <span class="text-lg font-bold text-green-400">{{ number_format($booking->tong_tien ?? 0) }}đ</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end gap-3">
        @if ($booking->trang_thai == 0)
            <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                    <i class="fas fa-check mr-2"></i> Xác nhận đặt vé
                </button>
            </form>
        @endif
        
        @if (in_array($booking->trang_thai, [0, 1]))
            <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn hủy vé này?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                    <i class="fas fa-times mr-2"></i> Hủy vé
                </button>
            </form>
        @endif
        
        <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
            <i class="fas fa-edit mr-2"></i> Chỉnh sửa
        </a>
    </div>
</div>
@endsection
