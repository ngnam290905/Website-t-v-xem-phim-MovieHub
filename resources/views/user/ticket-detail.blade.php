@extends('layouts.main')

@section('title', 'Chi tiết vé - MovieHub')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Back button -->
        <a href="{{ route('user.bookings') }}" class="inline-flex items-center text-[#a6a6b0] hover:text-white mb-6 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Quay lại lịch sử đặt vé
        </a>

        <!-- Ticket Card -->
        <div class="bg-gradient-to-br from-[#1b1d24] to-[#252831] border-2 border-[#F53003] rounded-2xl overflow-hidden shadow-2xl">
            <!-- Header -->
            <div class="bg-[#F53003] px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">VÉ XEM PHIM</h1>
                        <p class="text-white/80 text-sm">MovieHub Cinema</p>
                    </div>
                    <div class="text-right">
                        <p class="text-white text-sm">Mã đặt vé</p>
                        <p class="text-2xl font-bold text-white">MV{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Left Column: Movie Info -->
                    <div>
                        <!-- Movie Poster and Title -->
                        <div class="flex gap-4 mb-6">
                            @if($movie->poster)
                                <img src="{{ asset('storage/' . $movie->poster) }}" 
                                     alt="{{ $movie->ten_phim }}" 
                                     class="w-32 h-48 object-cover rounded-lg shadow-lg">
                            @else
                                <div class="w-32 h-48 bg-[#2f3240] rounded-lg flex items-center justify-center">
                                    <svg class="w-12 h-12 text-[#a6a6b0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h2 class="text-xl font-bold text-white mb-2">{{ $movie->ten_phim }}</h2>
                                @if($movie->ten_goc)
                                    <p class="text-[#a6a6b0] text-sm mb-2">{{ $movie->ten_goc }}</p>
                                @endif
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @if($movie->do_tuoi)
                                        <span class="px-2 py-1 bg-[#F53003] text-white text-xs rounded">{{ $movie->do_tuoi }}</span>
                                    @endif
                                    @if($movie->the_loai)
                                        @foreach(explode(',', $movie->the_loai) as $genre)
                                            <span class="px-2 py-1 bg-[#2f3240] text-[#a6a6b0] text-xs rounded">{{ trim($genre) }}</span>
                                        @endforeach
                                    @endif
                                </div>
                                <p class="text-[#a6a6b0] text-sm">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $movie->do_dai ?? 120 }} phút
                                </p>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="space-y-3 bg-[#222533] rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-white mb-3 border-b border-[#2f3240] pb-2">Thông tin đặt vé</h3>
                            
                            <div class="flex justify-between">
                                <span class="text-[#a6a6b0]">Khách hàng:</span>
                                <span class="text-white font-medium">{{ $booking->ten_khach_hang ?? $booking->nguoiDung->ho_ten ?? 'N/A' }}</span>
                            </div>

                            @if($booking->so_dien_thoai ?? $booking->nguoiDung->sdt ?? null)
                            <div class="flex justify-between">
                                <span class="text-[#a6a6b0]">Số điện thoại:</span>
                                <span class="text-white font-medium">{{ $booking->so_dien_thoai ?? $booking->nguoiDung->sdt }}</span>
                            </div>
                            @endif

                            @if($booking->email ?? $booking->nguoiDung->email ?? null)
                            <div class="flex justify-between">
                                <span class="text-[#a6a6b0]">Email:</span>
                                <span class="text-white font-medium text-sm">{{ $booking->email ?? $booking->nguoiDung->email }}</span>
                            </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-[#a6a6b0]">Phòng chiếu:</span>
                                <span class="text-white font-medium">{{ $room->ten_phong ?? 'N/A' }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-[#a6a6b0]">Suất chiếu:</span>
                                <span class="text-white font-medium">
                                    {{ $showtime->thoi_gian_bat_dau ? $showtime->thoi_gian_bat_dau->format('H:i - d/m/Y') : 'N/A' }}
                                </span>
                            </div>

                            <div class="flex justify-between items-start">
                                <span class="text-[#a6a6b0]">Ghế ngồi:</span>
                                <div class="text-right">
                                    <div class="flex flex-wrap gap-1 justify-end">
                                        @foreach($seatList as $seat)
                                            <span class="px-2 py-1 bg-[#F53003] text-white text-sm rounded font-medium">{{ $seat }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @if($comboItems->count() > 0)
                            <div class="flex justify-between items-start pt-2 border-t border-[#2f3240]">
                                <span class="text-[#a6a6b0]">Combo:</span>
                                <div class="text-right">
                                    @foreach($comboItems as $ci)
                                        <div class="text-white text-sm">
                                            {{ optional($ci->combo)->ten ?? 'Combo' }} x{{ max(1,(int)$ci->so_luong) }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($promo)
                            <div class="flex justify-between items-start pt-2 border-t border-[#2f3240]">
                                <span class="text-[#a6a6b0]">Khuyến mãi:</span>
                                <div class="text-right">
                                    <div class="text-white text-sm">{{ $promo->ma_km }}</div>
                                    <div class="text-[#7fd18a] text-sm">-{{ number_format($promoDiscount, 0) }}đ</div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Payment Info -->
                        <div class="mt-4 bg-[#222533] rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[#a6a6b0]">Phương thức thanh toán:</span>
                                @if($pt === 1)
                                    <span class="px-3 py-1 rounded-full text-sm font-medium text-green-300 bg-green-900/30">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        Thanh toán online
                                    </span>
                                @elseif($pt === 2)
                                    <span class="px-3 py-1 rounded-full text-sm font-medium text-blue-300 bg-blue-900/30">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Thanh toán tại quầy
                                    </span>
                                @else
                                    <span class="text-white">—</span>
                                @endif
                            </div>

                            <div class="flex justify-between items-center pt-3 border-t border-[#2f3240]">
                                <span class="text-xl font-bold text-white">Tổng tiền:</span>
                                <span class="text-2xl font-bold text-[#F53003]">{{ number_format($computedTotal, 0) }}đ</span>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mt-4 text-center">
                            @if($booking->trang_thai == 0)
                                <span class="inline-block px-6 py-2 bg-yellow-900 text-yellow-300 rounded-full font-medium">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Chờ xác nhận
                                </span>
                            @elseif($booking->trang_thai == 1)
                                <span class="inline-block px-6 py-2 bg-green-900 text-green-300 rounded-full font-medium">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Đã xác nhận
                                </span>
                            @elseif($booking->trang_thai == 2)
                                <span class="inline-block px-6 py-2 bg-red-900 text-red-300 rounded-full font-medium">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Đã hủy
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Right Column: QR Code -->
                    <div class="flex flex-col items-center justify-center">
                        <div class="bg-white p-6 rounded-2xl shadow-lg mb-4">
                            <div id="qrcode" class="flex items-center justify-center"></div>
                        </div>
                        
                        <div class="text-center mb-4">
                            <p class="text-[#a6a6b0] text-sm mb-2">Quét mã QR để xác thực vé</p>
                            <p class="text-white font-medium">{{ url('/api/ticket/' . $booking->id) }}</p>
                        </div>

                        <div class="bg-[#222533] rounded-lg p-4 w-full text-center">
                            <p class="text-[#a6a6b0] text-sm mb-1">Ngày đặt vé</p>
                            <p class="text-white font-medium">{{ optional($booking->created_at)->format('d/m/Y H:i') }}</p>
                        </div>

<<<<<<< HEAD
                        <div class="bg-[#222533] rounded-lg p-4 w-full text-center mb-4">
                            <p class="text-[#a6a6b0] text-sm mb-1">Mã vé</p>
                            <p class="text-white font-medium font-mono">{{ $booking->ticket_code ?? 'N/A' }}</p>
                        </div>

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
                            <div class="bg-[#222533] rounded-lg p-4 w-full text-center mb-4">
                                <p class="text-[#a6a6b0] text-sm mb-3">Mã QR Vé</p>
                                <div class="bg-white p-3 rounded-lg inline-block" style="min-height: 200px; min-width: 200px; display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ $qrCodeUrl }}" alt="QR Code" id="qrcode-img-user" style="width: 200px; height: 200px; display: block;" onerror="console.error('QR Image failed to load'); this.style.display='none'; document.getElementById('qrcode-fallback-user').style.display='block'; generateQRCodeFallbackUser('{{ $qrData }}');">
                                    <div id="qrcode-fallback-user" style="display: none; width: 200px; height: 200px;"></div>
                                </div>
                                <p class="text-[#a6a6b0] text-xs mt-3">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Xuất trình mã QR này tại rạp
                                </p>
                            </div>
                        @else
                            <!-- Debug: Show status if not confirmed -->
                            <div class="bg-yellow-900/20 border border-yellow-500/50 rounded-lg p-3 mb-4">
                                <p class="text-yellow-400 text-xs">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Trạng thái: {{ $booking->trang_thai }} (QR chỉ hiển thị khi = 1)
                                </p>
                            </div>
                        @endif
                        <!-- Action Buttons -->
                        <div class="mt-6 space-y-3 w-full">
                            <button onclick="window.print()" 
                                    class="w-full px-6 py-3 bg-[#F53003] text-white rounded-lg hover:bg-[#ff4d4d] transition-all duration-300 font-medium flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                In vé
                            </button>
                            
                            <button onclick="downloadTicket()" 
                                    class="w-full px-6 py-3 bg-[#2f3240] text-white rounded-lg hover:bg-[#3a3f50] transition-all duration-300 font-medium flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Tải xuống
                            </button>
                        </div>

                        <!-- Note -->
                        <div class="mt-6 bg-[#2f3240] rounded-lg p-4 w-full">
                            <p class="text-[#a6a6b0] text-sm text-center">
                                <svg class="w-5 h-5 inline mr-1 text-[#F53003]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
<<<<<<< HEAD
                                Vui lòng xuất trình mã vé này khi đến rạp
=======
                                Vui lòng xuất trình mã QR này khi đến rạp
>>>>>>> 7c41d7cf79cbaa269a41f5d8314177793bcddb1f
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-[#1b1d24] px-6 py-4 border-t border-[#2f3240]">
                <div class="flex items-center justify-between text-sm">
                    <div class="text-[#a6a6b0]">
                        <p>MovieHub Cinema - Hệ thống rạp chiếu phim hiện đại</p>
                        <p>Hotline: 1900-xxxx | Email: support@moviehub.vn</p>
                    </div>
                    <div class="text-[#a6a6b0] text-right">
                        <p>Cảm ơn bạn đã chọn MovieHub!</p>
                        <p class="text-[#F53003] font-medium">Chúc bạn xem phim vui vẻ!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<script>
<<<<<<< HEAD
// Generate QR Code fallback for user ticket detail
function generateQRCodeFallbackUser(qrData) {
    const fallbackElement = document.getElementById('qrcode-fallback-user');
    const imgElement = document.getElementById('qrcode-img-user');
    
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
=======
// Generate QR Code
document.addEventListener('DOMContentLoaded', function() {
    const qrcodeContainer = document.getElementById('qrcode');
    const qrData = '{{ url("/api/ticket/" . $booking->id) }}';
    
    new QRCode(qrcodeContainer, {
        text: qrData,
        width: 200,
        height: 200,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
});
>>>>>>> 7c41d7cf79cbaa269a41f5d8314177793bcddb1f

// Download ticket function
function downloadTicket() {
    // You can implement download as PDF or image here
    alert('Chức năng tải xuống sẽ được cập nhật sớm!');
}

// Print styles
const style = document.createElement('style');
style.textContent = `
    @media print {
        body * {
            visibility: hidden;
        }
        .container, .container * {
            visibility: visible;
        }
        .container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        button {
            display: none !important;
        }
        a[href*="bookings"] {
            display: none !important;
        }
    }
`;
document.head.appendChild(style);
</script>

<style>
    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }
    }
</style>
@endsection
