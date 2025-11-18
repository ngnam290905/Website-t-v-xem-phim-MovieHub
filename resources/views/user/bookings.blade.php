@extends('layouts.main')

@section('title', 'Lịch sử đặt vé - MovieHub')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <h1 class="text-2xl font-bold mb-6 text-white">Lịch sử đặt vé</h1>
        
        @forelse($bookings as $booking)
            @php
                $showtime = optional($booking->suatChieu);
                $movie    = optional($showtime->phim);
                $room     = optional($showtime->phongChieu);
                $seatList = $booking->chiTietDatVe->map(function($ct){ return optional($ct->ghe)->so_ghe; })->filter()->values()->all();
            @endphp
            <div class="bg-[#222533] border border-[#2f3240] rounded-lg p-4 mb-4">
                <div class="flex gap-4">
                    <img src="{{ $movie->poster ?? asset('images/logo.png') }}" alt="{{ $movie->ten_phim ?? 'Movie' }}" class="w-20 h-28 object-cover rounded hidden md:block">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-semibold text-[#F53003]">Mã đặt vé: #{{ $booking->id }}</span>
                                <span class="text-sm px-2 py-1 rounded-full 
                                    {{ $booking->trang_thai == 0 ? 'bg-yellow-900 text-yellow-300' : '' }}
                                    {{ $booking->trang_thai == 1 ? 'bg-green-900 text-green-300' : '' }}
                                    {{ $booking->trang_thai == 2 ? 'bg-red-900 text-red-300' : '' }}
                                ">
                                    {{ $booking->trang_thai == 0 ? 'Chờ xác nhận' : '' }}
                                    {{ $booking->trang_thai == 1 ? 'Đã xác nhận' : '' }}
                                    {{ $booking->trang_thai == 2 ? 'Đã hủy' : '' }}
                                </span>
                            </div>
                            <span class="text-[#a6a6b0] text-sm">Ngày đặt: <span class="text-white">{{ optional($booking->created_at)->format('d/m/Y H:i') }}</span></span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-[#a6a6b0]">Phim:</span>
                                <span class="text-white ml-2 font-medium">{{ $movie->ten_phim ?? 'Đang cập nhật' }}</span>
                            </div>
                            <div>
                                <span class="text-[#a6a6b0]">Phòng:</span>
                                <span class="text-white ml-2">{{ $room->ten_phong ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-[#a6a6b0]">Suất chiếu:</span>
                                <span class="text-white ml-2">{{ $showtime->thoi_gian_bat_dau ? $showtime->thoi_gian_bat_dau->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-[#a6a6b0]">Thời lượng:</span>
                                <span class="text-white ml-2">{{ $movie->do_dai ?? $movie->thoi_luong ?? 120 }} phút</span>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-[#a6a6b0]">Ghế:</span>
                                <span class="text-white ml-2">{{ empty($seatList) ? 'N/A' : implode(', ', $seatList) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-[#2f3240]">
                            @php
                                $comboItems = $booking->chiTietCombo ?? collect();
                                $promo = $booking->khuyenMai;
                                $comboTotal = $comboItems->sum(function($i){ return (float)$i->gia_ap_dung * max(1, (int)$i->so_luong); });
                                $seatTotal = (float) $booking->chiTietDatVe->sum('gia');
                                $subtotal = $seatTotal + $comboTotal;
                                $promoDiscount = 0;
                                if ($promo) {
                                    $type = strtolower($promo->loai_giam);
                                    $val  = (float)$promo->gia_tri_giam;
                                    $min  = 0; // có thể đọc từ dieu_kien nếu cần parsing thêm
                                    if ($subtotal >= $min) {
                                        if ($type === 'phantram') $promoDiscount = round($subtotal * ($val/100));
                                        else $promoDiscount = ($val >= 1000) ? $val : $val * 1000;
                                    }
                                }
                            @endphp

                            @if($promo || $comboItems->count())
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3 text-sm">
                                    @if($comboItems->count())
                                        <div>
                                            <span class="text-[#a6a6b0]">Combo:</span>
                                            <ul class="mt-1 list-disc list-inside text-white">
                                                @foreach($comboItems as $ci)
                                                    <li>
                                                        {{ optional($ci->combo)->ten ?? 'Combo' }}
                                                        x{{ max(1,(int)$ci->so_luong) }}
                                                        + {{ number_format($ci->gia_ap_dung,0) }}đ
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if($promo)
                                        <div>
                                            <span class="text-[#a6a6b0]">Khuyến mãi:</span>
                                            <div class="text-white mt-1">
                                                <span class="px-2 py-1 rounded bg-[#2b2e3b]">{{ $promo->ma_km }}</span>
                                                <span class="ml-2">{{ $promo->mo_ta }}</span>
                                                <span class="ml-2 text-[#7fd18a]">-{{ number_format($promoDiscount,0) }}đ</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            

                            @php
                                $pt = $booking->phuong_thuc_thanh_toan;
                                if (!$pt) {
                                    $map = optional($booking->thanhToan)->phuong_thuc;
                                    $pt = $map === 'online' ? 1 : ($map === 'offline' ? 2 : null);
                                }
                            @endphp
                            <div class="flex items-center justify-between mb-2 text-sm">
                                <span class="text-[#a6a6b0]">Phương thức thanh toán</span>
                                @if($pt === 1)
                                    <span class="px-2 py-1 rounded-full text-xs font-medium text-green-300 bg-green-900/30">Thanh toán online</span>
                                @elseif($pt === 2)
                                    <span class="px-2 py-1 rounded-full text-xs font-medium text-blue-300 bg-blue-900/30">Thanh toán tại quầy</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium text-gray-300 bg-gray-800">—</span>
                                @endif
                            </div>
                            @php $computedTotal = max(0, $subtotal - $promoDiscount); @endphp
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-[#F53003]">
                                    Tổng tiền: {{ number_format($computedTotal, 0) }}đ
                                </span>
                                @if($booking->trang_thai == 0)
                                    <button onclick="cancelBooking({{ $booking->id }})"
                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-300">
                                        Hủy đặt vé
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🎬</div>
                <p class="text-[#a6a6b0] text-lg">Bạn chưa có đặt vé nào</p>
                <a href="/" class="inline-block mt-4 px-6 py-3 bg-[#F53003] text-white rounded-lg hover:bg-[#ff4d4d] transition-all duration-300">
                    Đặt vé ngay
                </a>
            </div>
        @endforelse
        
        @if($bookings->hasPages())
            <div class="mt-8 flex justify-center">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function cancelBooking(bookingId) {
    if (confirm('Bạn có chắc muốn hủy đặt vé này?')) {
        fetch(`/user/bookings/${bookingId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Đã hủy đặt vé thành công!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Có lỗi xảy ra!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra!', 'error');
        });
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 z-50 px-6 py-3 rounded-lg text-white font-medium shadow-lg transform translate-x-full transition-transform duration-300 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' :
        'bg-blue-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>
@endsection
