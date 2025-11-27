@extends('layouts.main')

@section('title', 'Lịch sử đặt vé - MovieHub')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Lịch sử đặt vé</h1>
            <span class="text-[#a6a6b0]">Tổng số: <span class="text-white font-semibold">{{ $bookings->total() }}</span> đặt vé</span>
        </div>
        
        @forelse($bookings as $booking)
            @php
                $showtime = optional($booking->suatChieu);
                $movie    = optional($showtime->phim);
                $room     = optional($showtime->phongChieu);
                $seatList = $booking->chiTietDatVe->map(function($ct){ return optional($ct->ghe)->so_ghe; })->filter()->values()->all();
                
                // Status badge colors
                $statusClass = '';
                $statusText = '';
                switch($booking->trang_thai) {
                    case 0:
                        $statusClass = 'bg-yellow-900/30 text-yellow-300';
                        $statusText = 'Chờ xác nhận';
                        break;
                    case 1:
                        $statusClass = 'bg-green-900/30 text-green-300';
                        $statusText = 'Đã xác nhận';
                        break;
                    case 2:
                        $statusClass = 'bg-red-900/30 text-red-300';
                        $statusText = 'Đã hủy';
                        break;
                    case 3:
                        $statusClass = 'bg-orange-900/30 text-orange-300';
                        $statusText = 'Yêu cầu hủy';
                        break;
                    default:
                        $statusClass = 'bg-gray-900/30 text-gray-300';
                        $statusText = 'Không xác định';
                }
            @endphp
            <div class="bg-[#222533] border border-[#2f3240] rounded-lg p-4 mb-4 hover:border-[#F53003] transition-all duration-300">
                <div class="flex gap-4">
                    @if($movie->poster)
                        <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim ?? 'Movie' }}" class="w-20 h-28 object-cover rounded hidden md:block">
                    @endif
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                            <div class="flex items-center gap-4 flex-wrap">
                                <span class="text-sm font-semibold text-[#F53003]">Mã đặt vé: #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-xs px-3 py-1 rounded-full font-medium {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                            <span class="text-[#a6a6b0] text-sm">
                                Ngày đặt: 
                                <span class="text-white">
                                    {{ $booking->created_at ? $booking->created_at->format('d/m/Y H:i') : ($showtime->thoi_gian_bat_dau ? $showtime->thoi_gian_bat_dau->format('d/m/Y') : 'N/A') }}
                                </span>
                            </span>
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
                                <span class="text-[#a6a6b0]">Ghế đã đặt:</span>
                                <span class="text-white ml-2 font-medium">
                                    @if(empty($seatList))
                                        <span class="text-red-400">Chưa có thông tin ghế</span>
                                    @else
                                        {{ implode(', ', $seatList) }} 
                                        <span class="text-[#a6a6b0]">({{ count($seatList) }} ghế)</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-[#2f3240]">
                            @php
                                // Calculate seat total
                                $seatDetails = $booking->chiTietDatVe->map(function($detail) {
                                    $ghe = optional($detail->ghe);
                                    $loaiGhe = optional($ghe->loaiGhe);
                                    return [
                                        'so_ghe' => $ghe->so_ghe ?? 'N/A',
                                        'loai' => $loaiGhe->ten_loai ?? 'Thường',
                                        'gia' => (float)$detail->gia
                                    ];
                                });
                                $seatTotal = (float) $booking->chiTietDatVe->sum('gia');
                                
                                // Calculate combo total
                                $comboItems = $booking->chiTietCombo ?? collect();
                                $comboTotal = $comboItems->sum(function($i){ 
                                    return (float)$i->gia_ap_dung * max(1, (int)$i->so_luong); 
                                });
                                
                                // Calculate subtotal
                                $subtotal = $seatTotal + $comboTotal;
                                
                                // Calculate promotion discount
                                $promo = $booking->khuyenMai;
                                $promoDiscount = 0;
                                if ($promo && $promo->trang_thai == 1) {
                                    $type = strtolower($promo->loai_giam ?? '');
                                    $val  = (float)$promo->gia_tri_giam;
                                    if ($type === 'phantram') {
                                        $promoDiscount = round($subtotal * ($val/100));
                                    } else {
                                        $promoDiscount = $val;
                                    }
                                }
                                
                                // Final total
                                $computedTotal = max(0, $subtotal - $promoDiscount);
                                $savedTotal = (float)($booking->tong_tien ?? 0);
                                $finalTotal = $savedTotal > 0 ? $savedTotal : $computedTotal;
                            @endphp

                            {{-- Chi tiết giá vé --}}
                            @if($seatDetails->count() > 0)
                                <div class="mb-3">
                                    <div class="text-[#a6a6b0] text-sm mb-2">Chi tiết giá vé:</div>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                                        @foreach($seatDetails as $seat)
                                            <div class="bg-[#1b1d24] px-3 py-2 rounded">
                                                <span class="text-white font-medium">{{ $seat['so_ghe'] }}</span>
                                                <span class="text-[#a6a6b0] mx-1">•</span>
                                                <span class="text-[#a6a6b0]">{{ $seat['loai'] }}</span>
                                                <span class="text-white block mt-1">{{ number_format($seat['gia'], 0) }}đ</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-sm mt-2">
                                        <span class="text-[#a6a6b0]">Tổng tiền vé:</span>
                                        <span class="text-white font-semibold ml-2">{{ number_format($seatTotal, 0) }}đ</span>
                                    </div>
                                </div>
                            @endif

                            @if($promo || $comboItems->count())
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3 text-sm border-t border-[#2f3240] pt-3">
                                    @if($comboItems->count())
                                        <div>
                                            <span class="text-[#a6a6b0] block mb-2">Combo đã chọn:</span>
                                            <div class="space-y-1">
                                                @foreach($comboItems as $ci)
                                                    <div class="bg-[#1b1d24] px-3 py-2 rounded flex justify-between items-center">
                                                        <div>
                                                            <div class="text-white font-medium">{{ optional($ci->combo)->ten ?? 'Combo' }}</div>
                                                            <div class="text-[#a6a6b0] text-xs">Số lượng: {{ max(1,(int)$ci->so_luong) }}</div>
                                                        </div>
                                                        <span class="text-white font-semibold">{{ number_format($ci->gia_ap_dung * max(1,(int)$ci->so_luong), 0) }}đ</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if($promo)
                                        <div>
                                            <span class="text-[#a6a6b0] block mb-2">Khuyến mãi:</span>
                                            <div class="bg-[#1b1d24] px-3 py-2 rounded">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="px-2 py-0.5 rounded text-xs font-mono bg-[#2b2e3b] text-[#F53003]">{{ $promo->ma_km }}</span>
                                                    @if($promo->loai_giam === 'phantram')
                                                        <span class="text-xs text-[#a6a6b0]">(-{{ $promo->gia_tri_giam }}%)</span>
                                                    @endif
                                                </div>
                                                <div class="text-white text-xs">{{ $promo->mo_ta }}</div>
                                                <div class="text-[#7fd18a] font-semibold mt-1">-{{ number_format($promoDiscount, 0) }}đ</div>
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
                            <div class="flex items-center justify-between mb-3 text-sm border-t border-[#2f3240] pt-3">
                                <span class="text-[#a6a6b0]">Phương thức thanh toán:</span>
                                @if($pt === 1)
                                    <span class="px-3 py-1 rounded-full text-xs font-medium text-green-300 bg-green-900/30">
                                        <i class="fas fa-credit-card mr-1"></i> Thanh toán online
                                    </span>
                                @elseif($pt === 2)
                                    <span class="px-3 py-1 rounded-full text-xs font-medium text-blue-300 bg-blue-900/30">
                                        <i class="fas fa-money-bill-wave mr-1"></i> Thanh toán tại quầy
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-medium text-gray-300 bg-gray-800">Chưa xác định</span>
                                @endif
                            </div>
                            <div class="flex justify-between items-center bg-[#1b1d24] px-4 py-3 rounded-lg">
                                <span class="text-lg font-bold text-white">
                                    Tổng tiền:
                                </span>
                                <span class="text-2xl font-bold text-[#F53003]">
                                    {{ number_format($finalTotal, 0) }}đ
                                </span>
                            </div>
                            <div class="flex gap-2 mt-4 justify-end">
                                <a href="{{ route('user.bookings.show', $booking->id) }}" 
                                   class="px-4 py-2 bg-[#2f3240] text-white rounded-lg hover:bg-[#3a3f50] transition-all duration-300 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Xem chi tiết</span>
                                </a>
                                @if($booking->trang_thai == 0)
                                    <button onclick="cancelBooking({{ $booking->id }})"
                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-300 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <span>Hủy đặt vé</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16">
                <svg class="w-24 h-24 mx-auto text-[#a6a6b0] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                <p class="text-[#a6a6b0] text-xl mb-2">Bạn chưa có đặt vé nào</p>
                <p class="text-[#6b6b77] mb-6">Hãy đặt vé xem phim để trải nghiệm những bộ phim tuyệt vời!</p>
                <a href="{{ route('movies.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#F53003] text-white rounded-lg hover:bg-[#ff4d4d] transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                    </svg>
                    <span>Đặt vé ngay</span>
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
