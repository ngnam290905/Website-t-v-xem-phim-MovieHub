@extends('layouts.main')

@section('title', 'Booking Data #' . $booking->id)

@section('content')
<div class="min-h-screen bg-[#0F1117] py-8">
    <div class="max-w-7xl mx-auto px-4">
        <a href="{{ route('booking.data') }}" class="inline-flex items-center text-[#a6a6b0] hover:text-white mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Quay lại
        </a>

        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-white">Booking #{{ $booking->id }}</h1>
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    @if($booking->trang_thai == 'PAID') bg-green-500/20 text-green-400
                    @elseif($booking->trang_thai == 'PENDING') bg-yellow-500/20 text-yellow-400
                    @elseif($booking->trang_thai == 'DRAFT') bg-blue-500/20 text-blue-400
                    @else bg-gray-500/20 text-gray-400
                    @endif">
                    {{ $booking->trang_thai }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-white mb-3">Thông tin phim</h2>
                    <div class="space-y-2 text-[#a6a6b0]">
                        <p><strong class="text-white">Phim:</strong> {{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }}</p>
                        <p><strong class="text-white">Ngày chiếu:</strong> {{ $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y') ?? 'N/A' }}</p>
                        <p><strong class="text-white">Giờ chiếu:</strong> {{ $booking->suatChieu->thoi_gian_bat_dau->format('H:i') ?? 'N/A' }}</p>
                        <p><strong class="text-white">Phòng:</strong> {{ $booking->suatChieu->phongChieu->name ?? $booking->suatChieu->phongChieu->ten_phong ?? 'N/A' }}</p>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-white mb-3">Thông tin khách hàng</h2>
                    <div class="space-y-2 text-[#a6a6b0]">
                        <p><strong class="text-white">Tên:</strong> {{ $booking->ten_khach_hang ?? 'N/A' }}</p>
                        <p><strong class="text-white">SĐT:</strong> {{ $booking->so_dien_thoai ?? 'N/A' }}</p>
                        <p><strong class="text-white">Email:</strong> {{ $booking->email ?? 'N/A' }}</p>
                        @if($booking->nguoiDung)
                            <p><strong class="text-white">User:</strong> {{ $booking->nguoiDung->ten ?? 'N/A' }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Seats -->
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-chair text-[#FF784E]"></i>
                    <span>Ghế đã đặt ({{ $booking->chiTietDatVe->count() }})</span>
                </h2>
                <div class="space-y-2">
                    @foreach($booking->chiTietDatVe as $detail)
                        <div class="flex items-center justify-between bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg
                                    @if($detail->ghe->seatType && strpos(strtolower($detail->ghe->seatType->ten_loai ?? ''), 'vip') !== false)
                                        bg-gradient-to-br from-yellow-600 to-yellow-700 border-2 border-yellow-500
                                    @else
                                        bg-[#2a2d3a] border border-[#3a3d4a]
                                    @endif">
                                    <span class="text-white font-semibold text-sm">{{ $detail->ghe->so_ghe }}</span>
                                </div>
                                <div>
                                    <p class="text-white font-semibold">{{ $detail->ghe->so_ghe }}</p>
                                    <p class="text-sm text-[#a6a6b0]">{{ $detail->ghe->seatType->ten_loai ?? 'Thường' }}</p>
                                </div>
                            </div>
                            <p class="text-[#FF784E] font-bold">{{ number_format($detail->gia) }}đ</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Combos -->
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-box text-yellow-400"></i>
                    <span>Combo ({{ $booking->chiTietCombo->count() }})</span>
                </h2>
                <div class="space-y-2">
                    @forelse($booking->chiTietCombo as $comboDetail)
                        <div class="flex items-center justify-between bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-3">
                            <div>
                                <p class="text-white font-semibold">{{ $comboDetail->combo->ten ?? 'N/A' }}</p>
                                <p class="text-sm text-[#a6a6b0]">x{{ $comboDetail->so_luong }}</p>
                            </div>
                            <p class="text-[#FF784E] font-bold">{{ number_format($comboDetail->gia_ap_dung * $comboDetail->so_luong) }}đ</p>
                        </div>
                    @empty
                        <p class="text-[#a6a6b0] text-center py-4">Không có combo</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 mt-8">
            <h2 class="text-xl font-bold text-white mb-4">Tổng thanh toán</h2>
            <div class="space-y-3">
                <div class="flex justify-between text-[#a6a6b0]">
                    <span>Giá vé:</span>
                    <span>{{ number_format($booking->chiTietDatVe->sum('gia')) }}đ</span>
                </div>
                <div class="flex justify-between text-[#a6a6b0]">
                    <span>Combo:</span>
                    <span>{{ number_format($booking->chiTietCombo->sum(function($item) { return $item->gia_ap_dung * $item->so_luong; })) }}đ</span>
                </div>
                <div class="border-t border-[#2A2F3A] pt-3 flex justify-between">
                    <span class="text-xl font-bold text-white">Tổng:</span>
                    <span class="text-2xl font-bold text-[#FF784E]">{{ number_format($booking->tong_tien) }}đ</span>
                </div>
            </div>

            @if($booking->thanhToan)
                <div class="mt-6 pt-6 border-t border-[#2A2F3A]">
                    <h3 class="text-lg font-semibold text-white mb-3">Thông tin thanh toán</h3>
                    <div class="space-y-2 text-[#a6a6b0]">
                        <p><strong class="text-white">Phương thức:</strong> {{ $booking->thanhToan->phuong_thuc ?? 'N/A' }}</p>
                        <p><strong class="text-white">Trạng thái:</strong> {{ $booking->thanhToan->trang_thai ?? 'N/A' }}</p>
                        <p><strong class="text-white">Thời gian:</strong> {{ $booking->thanhToan->thoi_gian ? \Carbon\Carbon::parse($booking->thanhToan->thoi_gian)->format('d/m/Y H:i') : 'N/A' }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

