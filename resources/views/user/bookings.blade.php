@extends('layouts.app')

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
                    <img src="{{ $movie->poster_url ?? $movie->poster ?? asset('images/no-poster.svg') }}" alt="{{ $movie->ten_phim ?? 'Movie' }}" class="w-20 h-28 object-cover rounded hidden md:block" onerror="this.src='{{ asset('images/no-poster.svg') }}'">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-semibold text-[#F53003]">Mã đặt vé: #{{ $booking->id }}</span>
                                
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
                                $foodItems  = $booking->chiTietFood ?? collect();
                                $promo = $booking->khuyenMai;

                                // Tổng Combo
                                $comboTotal = $comboItems->sum(function($i){ return (float)$i->gia_ap_dung * max(1, (int)$i->so_luong); });
                                // Tổng Đồ ăn
                                $foodTotal = $foodItems->sum(function($f){ return (float)($f->price ?? 0) * max(1, (int)($f->quantity ?? 1)); });
                                // Tổng Ghế: tính theo loại ghế (đảm bảo ghế đôi = 200,000/ghế)
                                $seatTotal = 0;
                                if ($booking->chiTietDatVe) {
                                    foreach ($booking->chiTietDatVe as $detail) {
                                        $typeStrs = [];
                                        $typeStrs[] = $detail->ghe->loaiGhe->ten_loai ?? '';
                                        $typeStrs[] = $detail->ghe->seatType->ten_loai ?? '';
                                        $type = strtolower(trim(implode(' ', array_filter($typeStrs))));
                                        if (str_contains($type, 'vip')) {
                                            $seatTotal += 150000;
                                        } elseif (str_contains($type, 'đôi') || str_contains($type, 'doi') || str_contains($type, 'couple')) {
                                            $seatTotal += 200000;
                                        } else {
                                            $seatTotal += 100000;
                                        }
                                    }
                                }

                                $subtotal = $seatTotal + $comboTotal + $foodTotal;
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
                            @php 
                                // Luôn hiển thị tổng đã tính lại để đảm bảo đồng bộ quy tắc giá ghế
                                $computedTotal = max(0, $subtotal - $promoDiscount);
                                $displayTotal = (float)$computedTotal;
                            @endphp
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-[#F53003]">
                                    Tổng tiền: {{ number_format($displayTotal, 0) }}đ
                                </span>
                                <div class="flex gap-2">
                                    <a href="{{ route('booking.ticket.detail', $booking->id) }}" 
                                       class="px-4 py-2 bg-[#2f3240] text-white rounded-lg hover:bg-[#3a3f50] transition-all duration-300 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Xem chi tiết
                                    </a>
                                </div>
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
// Cancel booking removed by request; no client-side cancellation available.
</script>
@endsection
