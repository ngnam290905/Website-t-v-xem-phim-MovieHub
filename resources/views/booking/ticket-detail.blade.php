@extends('layouts.main')

@section('title', 'Chi tiết vé - MovieHub')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#0a1a2f] via-[#0f1f3a] to-[#151822] py-8 px-4">
    <div class="max-w-4xl mx-auto">
        
        <!-- Back Button -->
        <a 
            href="{{ route('booking.tickets') }}" 
            class="inline-flex items-center gap-2 text-[#a6a6b0] hover:text-white mb-6 transition-colors"
        >
            <i class="fas fa-arrow-left"></i>
            <span>Quay lại danh sách vé</span>
        </a>

        @php
            $showtime = $booking->suatChieu;
            $movie = $showtime->phim ?? null;
            $room = $showtime->phongChieu ?? null;
            $seats = $booking->chiTietDatVe;
            $comboItems = isset($comboItems) ? $comboItems : ($booking->chiTietCombo ?? collect());
            $foodItems = isset($foodItems) ? $foodItems : ($booking->chiTietFood ?? collect());
            $combos = $comboItems;
            $foods = $foodItems;
            $payment = $booking->thanhToan;
            $promo = isset($promo) ? $promo : ($booking->khuyenMai ?? null);
            $promoDiscount = isset($promoDiscount) ? $promoDiscount : 0;
            $paidTotal = optional($payment)->so_tien;
            $storedTotal = $booking->tong_tien ?? null;
            $comboSum = $comboItems->sum(function($i){ return (float)$i->gia_ap_dung * max(1,(int)$i->so_luong); });
            $foodSum = $foodItems->sum(function($f){ return (float)$f->price * max(1,(int)$f->quantity); });
            $seatSum = (float) $booking->chiTietDatVe->sum('gia');
            $computedTotal = isset($computedTotal) ? $computedTotal : (float) ($seatSum + $comboSum + $foodSum - $promoDiscount);
            $displayTotal = is_numeric($paidTotal) && $paidTotal > 0 ? (float)$paidTotal : (is_numeric($storedTotal) && $storedTotal > 0 ? (float)$storedTotal : (float)max(0,$computedTotal));
            
            $statusMap = [
                0 => ['label' => 'Đang xử lý', 'bg' => 'bg-yellow-500/20', 'text' => 'text-yellow-400', 'border' => 'border-yellow-500/50', 'icon' => 'clock'],
                1 => ['label' => 'Đã thanh toán', 'bg' => 'bg-green-500/20', 'text' => 'text-green-400', 'border' => 'border-green-500/50', 'icon' => 'check-circle'],
                2 => ['label' => 'Đã hủy', 'bg' => 'bg-red-500/20', 'text' => 'text-red-400', 'border' => 'border-red-500/50', 'icon' => 'times-circle'],
            ];
            $currentStatus = $statusMap[$booking->trang_thai] ?? $statusMap[0];
            $isPaid = $booking->trang_thai == 1;
            $isCancelled = $booking->trang_thai == 2;
        @endphp

        <!-- Ticket Card -->
        <div class="bg-gradient-to-br from-[#1a1d24] to-[#151822] border border-[#2a2d3a] rounded-xl overflow-hidden mb-6">
            <!-- Header -->
            <div class="relative p-8 bg-gradient-to-r from-[#0077c8]/20 to-[#0099e6]/20 border-b border-[#2a2d3a]">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $currentStatus['bg'] }} {{ $currentStatus['text'] }} border {{ $currentStatus['border'] }}">
                                <i class="fas fa-{{ $currentStatus['icon'] }} mr-2"></i>
                                {{ $currentStatus['label'] }}
                            </span>
                            <span class="text-[#a6a6b0] text-sm">
                                Mã vé: #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        @if($movie)
                            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">{{ $movie->ten_phim }}</h1>
                            <p class="text-[#a6a6b0]">{{ $movie->the_loai ?? 'Phim điện ảnh' }}</p>
                        @endif
                    </div>
                    @if($movie && $movie->poster_url)
                        <x-image 
                          src="{{ $movie->poster_url }}" 
                          alt="{{ $movie->ten_phim }}"
                          aspectRatio="2/3"
                          class="w-32 h-48 rounded-lg border-2 border-[#2a2d3a] shadow-lg"
                          quality="high"
                        />
                    @endif
                </div>
            </div>

            <!-- Body -->
            <div class="p-8 space-y-6">
                <!-- Showtime Info -->
                @if($showtime)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-lg bg-[#0077c8]/20 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-[#0077c8] text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-[#a6a6b0] mb-1">Ngày chiếu</div>
                                    <div class="text-white font-bold text-lg">
                                        {{ $showtime->thoi_gian_bat_dau->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-lg bg-[#0077c8]/20 flex items-center justify-center">
                                    <i class="fas fa-clock text-[#0077c8] text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-[#a6a6b0] mb-1">Giờ chiếu</div>
                                    <div class="text-white font-bold text-lg">
                                        {{ $showtime->thoi_gian_bat_dau->format('H:i') }} - {{ $showtime->thoi_gian_ket_thuc->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($room)
                            <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-12 h-12 rounded-lg bg-[#0077c8]/20 flex items-center justify-center">
                                        <i class="fas fa-door-open text-[#0077c8] text-xl"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs text-[#a6a6b0] mb-1">Phòng chiếu</div>
                                        <div class="text-white font-bold text-lg">
                                            {{ $room->ten_phong ?? $room->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-lg bg-[#ffcc00]/20 flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-[#ffcc00] text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-[#a6a6b0] mb-1">Tổng tiền</div>
                                    <div class="text-[#ffcc00] font-bold text-lg">
                                        {{ number_format($displayTotal) }}đ
                                    </div>
                                </div>
                            </div>
                            @if($promo)
                                <div class="mt-3 p-3 rounded-lg bg-[#151822] border border-[#2a2d3a] flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-[#a6a6b0] mb-1">Khuyến mãi đã áp dụng</div>
                                        <div class="text-white font-semibold">
                                            {{ $promo->ma_km ?? ('KM #' . $promo->id) }}
                                        </div>
                                        @if(!empty($promo->mo_ta))
                                            <div class="text-xs text-[#a6a6b0] mt-1 line-clamp-2">{{ $promo->mo_ta }}</div>
                                        @endif
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded bg-[#1a1d24] border border-[#2a2f3a] text-[#a6a6b0]">
                                        @php $type = strtolower($promo->loai_giam ?? ''); @endphp
                                        @if($type === 'phantram')
                                            -{{ (float)$promo->gia_tri_giam }}%
                                        @else
                                            -{{ number_format(((float)($promo->gia_tri_giam ?? 0) >= 1000) ? (float)($promo->gia_tri_giam ?? 0) : ((float)($promo->gia_tri_giam ?? 0)*1000)) }}đ
                                        @endif
                                    </span>
                                </div>
                            @endif

                            
                        </div>
                    </div>
                @endif

                <!-- Seats - Each seat will be printed on separate page -->
                <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-chair text-[#0077c8]"></i>
                        <span>Ghế đã chọn ({{ $seats->count() }})</span>
                    </h3>
                    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
                        @forelse($seats as $seatDetail)
                            @php
                                $seat = $seatDetail->ghe;
                                $seatType = $seat->seatType ?? null;
                                $isVip = $seatType && strpos(strtolower($seatType->ten_loai ?? ''), 'vip') !== false;
                            @endphp
                            <div class="text-center seat-item" data-seat-code="{{ $seat->so_ghe }}">
                                <div class="px-3 py-2 rounded-lg text-sm font-semibold mb-1 {{ $isVip ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50' : 'bg-[#0077c8]/20 text-[#0077c8] border border-[#0077c8]/50' }}">
                                    <i class="fas fa-{{ $isVip ? 'crown' : 'chair' }} mr-1"></i>
                                    {{ $seat->so_ghe }}
                                </div>
                                @if($seatType)
                                    <div class="text-xs text-[#a6a6b0]">{{ $seatType->ten_loai }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-sm text-[#a6a6b0]">Chưa có ghế nào.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Combos - Will be printed on separate page -->
                @if($combos->isNotEmpty())
                <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5 combo-foods-section">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-box text-[#ffcc00]"></i>
                        <span>Combo đã chọn</span>
                    </h3>
                    <div class="space-y-3">
                        @foreach($combos as $comboDetail)
                            @php
                                $c = $comboDetail->combo;
                                $comboName = $c->ten ?? $c->ten_combo ?? 'Combo';
                                $comboImg = null;
                                if ($c && !empty($c->anh)) { $comboImg = $c->anh; }
                                elseif ($c && !empty($c->hinh_anh)) { $comboImg = $c->hinh_anh; }
                                $qty = max(1, (int)($comboDetail->so_luong ?? 1));
                                $unit = (float)($comboDetail->gia_ap_dung ?? $c->gia ?? 0);
                                $lineTotal = $unit * $qty;
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-[#151822] rounded-lg border border-[#2a2d3a]">
                                <div class="flex items-center gap-3">
                                    <img 
                                        src="{{ $comboImg ?: asset('images/no-poster.svg') }}" 
                                        alt="{{ $comboName }}"
                                        class="w-16 h-16 object-cover rounded-lg"
                                        onerror="this.src='{{ asset('images/no-poster.svg') }}'"
                                    >
                                    <div>
                                        <div class="text-white font-semibold">{{ $comboName }}</div>
                                        <div class="text-xs text-[#a6a6b0]">Đơn giá: {{ number_format($unit) }}đ</div>
                                        <div class="text-sm text-[#a6a6b0]">Số lượng: {{ $qty }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[#ffcc00] font-bold">{{ number_format($lineTotal) }}đ</div>
                                    <div class="text-xs text-[#a6a6b0]">= {{ number_format($unit) }}đ x {{ $qty }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Foods - Will be printed on same page as combos -->
                @if(isset($foods) && $foods->isNotEmpty())
                <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5 combo-foods-section">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-utensils text-[#ff784e]"></i>
                        <span>Đồ ăn đã chọn</span>
                    </h3>
                    <div class="space-y-3">
                        @foreach($foods as $foodDetail)
                            @php
                                $f = $foodDetail->food;
                                $foodName = $f->name ?? 'Đồ ăn';
                                $foodImg = $f->image_url ?? $f->image ?? null;
                                $qty = max(1, (int)($foodDetail->quantity ?? 1));
                                $unit = (float)($foodDetail->price ?? $f->price ?? 0);
                                $lineTotal = $unit * $qty;
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-[#151822] rounded-lg border border-[#2a2d3a]">
                                <div class="flex items-center gap-3">
                                    <img 
                                        src="{{ $foodImg ?: asset('images/no-poster.svg') }}" 
                                        alt="{{ $foodName }}"
                                        class="w-16 h-16 object-cover rounded-lg"
                                        onerror="this.src='{{ asset('images/no-poster.svg') }}'"
                                    >
                                    <div>
                                        <div class="text-white font-semibold">{{ $foodName }}</div>
                                        <div class="text-xs text-[#a6a6b0]">Đơn giá: {{ number_format($unit) }}đ</div>
                                        <div class="text-sm text-[#a6a6b0]">Số lượng: {{ $qty }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[#ff784e] font-bold">{{ number_format($lineTotal) }}đ</div>
                                    <div class="text-xs text-[#a6a6b0]">= {{ number_format($unit) }}đ x {{ $qty }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Payment Info -->
                @if($payment)
                    <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5 print-hide">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <i class="fas fa-credit-card text-[#10b981]"></i>
                            <span>Thông tin thanh toán</span>
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-[#a6a6b0] mb-1">Phương thức</div>
                                <div class="text-white font-semibold">{{ $payment->phuong_thuc ?? 'Online' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-[#a6a6b0] mb-1">Thời gian</div>
                                <div class="text-white font-semibold">
                                    {{ $payment->thoi_gian ? \Carbon\Carbon::parse($payment->thoi_gian)->format('d/m/Y H:i') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <!-- QR Code for Confirmed Tickets -->
                @php
                    // Always generate QR code data for confirmed tickets
                    $qrData = $qrCodeData ?? null;
                    if (!$qrData) {
                        $qrData = 'ticket_id=' . $booking->id;
                        if ($booking->ticket_code) {
                            $qrData = 'ticket_id=' . $booking->ticket_code;
                        }
                    }
                    // Use QR code API for reliable display
                    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrData);
                @endphp
                
                @if($booking->trang_thai == 1)
                    <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5 print-hide">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <i class="fas fa-qrcode text-[#0077c8]"></i>
                            <span>Mã QR Vé</span>
                        </h3>
                        <div class="flex flex-col items-center justify-center">
                            <div class="bg-white p-4 rounded-lg mb-4" style="min-height: 200px; min-width: 200px; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ $qrCodeUrl }}" alt="QR Code" id="qrcode-img" style="width: 200px; height: 200px; display: block;" onerror="console.error('QR Image failed to load'); this.style.display='none'; document.getElementById('qrcode-fallback').style.display='block'; generateQRCodeFallback('{{ $qrData }}');">
                                <div id="qrcode-fallback" style="display: none; width: 200px; height: 200px;"></div>
                            </div>
                            <p class="text-sm text-[#a6a6b0] text-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Vui lòng xuất trình mã QR này tại rạp để vào xem phim
                            </p>
                            <p class="text-xs text-[#a6a6b0] text-center mt-2">
                                Mã vé: #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                    </div>
                @else
                    <!-- Debug: Show status if not confirmed -->
                    <div class="bg-yellow-900/20 border border-yellow-500/50 rounded-lg p-3 mb-4">
                        <p class="text-yellow-400 text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            Trạng thái vé: {{ $booking->trang_thai }} (QR code chỉ hiển thị khi trạng thái = 1)
                        </p>
                    </div>
                @endif

                <!-- QR Code for Print (Always visible when printing) -->
                <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5 print-only" style="display: none;">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-qrcode text-[#0077c8]"></i>
                        <span>Mã QR Vé</span>
                    </h3>
                    <div class="flex flex-col items-center justify-center">
                        <div class="bg-white p-4 rounded-lg mb-4" style="min-height: 200px; min-width: 200px; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ $qrCodeUrl }}" alt="QR Code" style="width: 200px; height: 200px; display: block;">
                        </div>
                        <p class="text-sm text-[#a6a6b0] text-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Vui lòng xuất trình mã QR này tại rạp để vào xem phim
                        </p>
                        <p class="text-xs text-[#a6a6b0] text-center mt-2 font-mono">
                            Mã vé: {{ $booking->ticket_code ?? 'MV' . str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>
                </div>

                <!-- Booking Info -->
                <div class="bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg p-5">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-[#0077c8]"></i>
                        <span>Thông tin đặt vé</span>
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-[#a6a6b0] mb-1">Ngày đặt</div>
                            <div class="text-white font-semibold">{{ $booking->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        @if($booking->nguoiDung)
                            <div>
                                <div class="text-[#a6a6b0] mb-1">Khách hàng</div>
                                <div class="text-white font-semibold">{{ $booking->nguoiDung->ho_ten ?? 'N/A' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-8 py-6 bg-[#0a1a2f] border-t border-[#2a2d3a] flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-[#a6a6b0]">
                    <i class="fas fa-shield-alt text-[#10b981] mr-2"></i>
                    Vé đã được bảo vệ và xác thực
                    @if(isset($isPrinted) && $isPrinted)
                        <span class="ml-3 text-yellow-400">
                            <i class="fas fa-print mr-1"></i>
                            Đã in lúc: {{ $booking->thoi_gian_in ? $booking->thoi_gian_in->format('d/m/Y H:i:s') : 'N/A' }}
                        </span>
                    @endif
                </div>
                <div class="flex gap-3">
                    @if($isPaid)
                        @if(isset($isPrinted) && $isPrinted)
                            <button 
                                disabled
                                class="px-6 py-3 bg-gray-600 text-gray-400 rounded-lg font-semibold cursor-not-allowed flex items-center gap-2 print-hidden"
                            >
                                <i class="fas fa-print"></i>
                                <span>Đã in</span>
                            </button>
                        @else
                            <button 
                                id="print-ticket-btn"
                                onclick="printTicket({{ $booking->id }})"
                                class="px-6 py-3 bg-gradient-to-r from-[#0077c8] to-[#0099e6] text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-[#0077c8]/50 transition-all flex items-center gap-2 print-hidden"
                            >
                                <i class="fas fa-print"></i>
                                <span>In vé</span>
                            </button>
                        @endif
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
let isPrinting = false;

async function printTicket(bookingId) {
    if (isPrinting) {
        return;
    }

    const printBtn = document.getElementById('print-ticket-btn');
    if (!printBtn || printBtn.disabled) {
        // Vé đã được in rồi, chỉ cho phép xem
        window.print();
        return;
    }

    isPrinting = true;
    printBtn.disabled = true;
    printBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Đang xử lý...</span>';

    try {
        // Gọi API để đánh dấu đã in
        const response = await fetch(`/tickets/${bookingId}/mark-printed`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Đánh dấu thành công, tiến hành in
            printBtn.innerHTML = '<i class="fas fa-check"></i> <span>Đã in</span>';
            printBtn.classList.remove('bg-gradient-to-r', 'from-[#0077c8]', 'to-[#0099e6]', 'hover:shadow-lg', 'hover:shadow-[#0077c8]/50');
            printBtn.classList.add('bg-gray-600', 'text-gray-400', 'cursor-not-allowed');
            
            // In vé
            setTimeout(() => {
                window.print();
            }, 500);
        } else {
            // Vé đã được in rồi
            alert('Vé này đã được in rồi. Thời gian in: ' + (data.printed_at || 'N/A'));
            printBtn.disabled = true;
            printBtn.innerHTML = '<i class="fas fa-print"></i> <span>Đã in</span>';
            printBtn.classList.remove('bg-gradient-to-r', 'from-[#0077c8]', 'to-[#0099e6]', 'hover:shadow-lg', 'hover:shadow-[#0077c8]/50');
            printBtn.classList.add('bg-gray-600', 'text-gray-400', 'cursor-not-allowed');
        }
    } catch (error) {
        console.error('Error marking ticket as printed:', error);
        // Vẫn cho phép in nếu API lỗi (fallback)
        alert('Có lỗi xảy ra, nhưng vẫn có thể in vé.');
        window.print();
        printBtn.disabled = false;
        printBtn.innerHTML = '<i class="fas fa-print"></i> <span>In vé</span>';
    } finally {
        isPrinting = false;
    }
}

function cancelTicket(bookingId) {
    if (confirm('Bạn có chắc chắn muốn hủy vé này? Hành động này không thể hoàn tác.')) {
        // TODO: Implement cancel ticket API
        alert('Tính năng hủy vé đang được phát triển');
    }
}

// Generate QR Code fallback if image fails to load
function generateQRCodeFallback(qrData) {
    const fallbackElement = document.getElementById('qrcode-fallback');
    const imgElement = document.getElementById('qrcode-img');
    
    if (fallbackElement && typeof QRCode !== 'undefined') {
        imgElement.style.display = 'none';
        fallbackElement.style.display = 'block';
        new QRCode(fallbackElement, {
            text: qrData,
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    } else {
        // If QRCode library not loaded, try to load it
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
        script.onload = function() {
            if (fallbackElement) {
                imgElement.style.display = 'none';
                fallbackElement.style.display = 'block';
                new QRCode(fallbackElement, {
                    text: qrData,
                    width: 200,
                    height: 200,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        };
        document.head.appendChild(script);
    }
}
</script>

<style>
@media print {
    @page {
        size: A4;
        margin: 10mm;
    }
    
    body * {
        visibility: hidden;
    }
    
    .min-h-screen, .min-h-screen * {
        visibility: visible;
    }
    
    .min-h-screen {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: 100%;
        padding: 0 !important;
    }
    
    /* Hide buttons and navigation */
    button, a[href*="tickets"] {
        display: none !important;
    }
    
    /* Hide payment info and QR code when printing */
    .print-hide {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Hide QR code sections */
    .bg-\[#0a1a2f\]:has(img[alt="QR Code"]),
    .bg-\[#0a1a2f\]:has(#qrcode-img),
    .bg-\[#0a1a2f\]:has(#qrcode-fallback) {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Improve print colors */
    .bg-gradient-to-br, .bg-gradient-to-r {
        background: white !important;
        color: black !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .bg-[#0a1a2f], .bg-[#1a1d24], .bg-[#151822] {
        background: #f5f5f5 !important;
        border: 1px solid #ddd !important;
    }
    
    .text-white {
        color: #000 !important;
    }
    
    .text-[#a6a6b0] {
        color: #666 !important;
    }
    
    .border {
        border-color: #000 !important;
    }
    
    /* Ensure QR code is always visible when printing */
    img[alt="QR Code"], 
    img[id*="qrcode"], 
    #qrcode-img, 
    #qrcode-fallback,
    .bg-white img {
        visibility: visible !important;
        display: block !important;
        max-width: 100% !important;
        height: auto !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    #qrcode-fallback canvas {
        visibility: visible !important;
        display: block !important;
    }
    
    /* Hide payment info and QR code when printing */
    .print-hide {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Hide QR code sections */
    img[alt="QR Code"], #qrcode-img, #qrcode-fallback {
        display: none !important;
        visibility: hidden !important;
    }
    
    #qrcode-fallback canvas {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Hide print-only sections */
    .print-only {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Keep status badges visible but readable */
    .bg-green-500\/20, .bg-yellow-500\/20, .bg-red-500\/20 {
        background: #f0f0f0 !important;
        border: 1px solid #000 !important;
    }
    
    /* Print each seat on separate page */
    .seat-item {
        page-break-inside: avoid;
    }
    
    /* Combo and Foods section on separate page */
    .combo-foods-section {
        page-break-before: always;
        page-break-inside: avoid;
    }
    
    /* Ensure each seat ticket is on its own page when printing individual seats */
    .ticket-per-seat {
        page-break-after: always;
        page-break-inside: avoid;
    }
}
</style>
@endsection

