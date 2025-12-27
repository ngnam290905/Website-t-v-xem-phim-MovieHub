@extends('admin.layout')

@section('title', 'Chi tiết vé - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Chi tiết vé #{{ $ticket->id }}</h1>
        <div class="flex gap-2">
            @if(isset($isPrinted) && $isPrinted)
                <button 
                    disabled
                    class="px-4 py-2 bg-gray-600 text-gray-400 rounded-lg cursor-not-allowed print-hidden"
                >
                    <i class="fas fa-print mr-2"></i>Đã in ({{ $ticket->thoi_gian_in ? $ticket->thoi_gian_in->format('d/m/Y H:i') : 'N/A' }})
                </button>
            @else
                <button 
                    id="print-ticket-btn"
                    onclick="printTicket({{ $ticket->id }})"
                    class="px-4 py-2 bg-[#F53003] hover:bg-[#ff4d4d] text-white rounded-lg transition print-hidden"
                >
                    <i class="fas fa-print mr-2"></i>In vé
                </button>
            @endif
        <a 
            href="{{ route('admin.scan.index') }}" 
            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition"
        >
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ticket Info -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4">Thông tin vé</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Mã vé:</span>
                    <span class="text-white font-mono font-semibold">{{ $ticket->ticket_code ?: sprintf('MV%06d', $ticket->id) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Trạng thái quét:</span>
                    @if($ticket->checked_in)
                        <span class="px-3 py-1 bg-green-600 text-white rounded text-sm">
                            <i class="fas fa-check-circle mr-1"></i>Đã quét
                        </span>
                    @else
                        <span class="px-3 py-1 bg-yellow-600 text-white rounded text-sm">
                            <i class="fas fa-clock mr-1"></i>Chưa quét
                        </span>
                    @endif
                </div>
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Tổng tiền:</span>
                    <span class="text-white font-semibold text-lg">
                        {{ number_format($ticket->tong_tien ?? 0, 0, ',', '.') }} đ
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Ngày đặt:</span>
                    <span class="text-white">
                        {{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : 'N/A' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4">Thông tin khách hàng</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Tên:</span>
                    <span class="text-white">{{ $ticket->ten_khach_hang ?? ($ticket->nguoiDung->ho_ten ?? 'N/A') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Email:</span>
                    <span class="text-white">{{ $ticket->email ?? ($ticket->nguoiDung->email ?? 'N/A') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Số điện thoại:</span>
                    <span class="text-white">{{ $ticket->so_dien_thoai ?? ($ticket->nguoiDung->so_dien_thoai ?? 'N/A') }}</span>
                </div>
                @if($ticket->nguoiDung)
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Thành viên:</span>
                        <a 
                            href="{{ route('admin.users.show', $ticket->nguoiDung->id) }}" 
                            class="text-[#F53003] hover:underline"
                        >
                            {{ $ticket->nguoiDung->ho_ten }} (ID: {{ $ticket->nguoiDung->id }})
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Showtime Info -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4">Thông tin suất chiếu</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Phim:</span>
                    <span class="text-white font-semibold">
                        {{ $ticket->suatChieu->phim->ten_phim ?? 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Phòng chiếu:</span>
                    <span class="text-white">
                        {{ $ticket->suatChieu->phongChieu->ten_phong ?? 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Ngày giờ chiếu:</span>
                    <span class="text-white">
                        {{ $ticket->suatChieu->thoi_gian_bat_dau ? $ticket->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') : 'N/A' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Seats Info -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4">Thông tin ghế</h2>
            <div class="space-y-2">
                @foreach($seats as $seat)
                    <div class="flex justify-between items-center p-2 bg-[#1a1d24] rounded">
                        <div>
                            <span class="text-white font-semibold">{{ $seat['seat'] }}</span>
                            <span class="text-[#a6a6b0] text-sm ml-2">({{ $seat['type'] }})</span>
                        </div>
                        <span class="text-white">{{ number_format($seat['price'], 0, ',', '.') }} đ</span>
                    </div>
                @endforeach
                <div class="pt-2 border-t border-[#262833] flex justify-between">
                    <span class="text-[#a6a6b0]">Tổng ghế:</span>
                    <span class="text-white font-semibold">
                        {{ number_format($seats->sum('price'), 0, ',', '.') }} đ
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Info -->
    @if($ticket->thanhToan)
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4">Thông tin thanh toán</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-[#a6a6b0] text-sm">Phương thức:</span>
                    <p class="text-white font-semibold">{{ $ticket->thanhToan->phuong_thuc ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-[#a6a6b0] text-sm">Mã giao dịch:</span>
                    <p class="text-white font-mono text-sm">{{ $ticket->thanhToan->ma_giao_dich ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-[#a6a6b0] text-sm">Trạng thái:</span>
                    <p class="text-white">
                        @if($ticket->thanhToan->trang_thai == 1)
                            <span class="text-green-400">Đã thanh toán</span>
                        @else
                            <span class="text-yellow-400">Chờ thanh toán</span>
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-[#a6a6b0] text-sm">Thời gian:</span>
                    <p class="text-white text-sm">
                        {{ $ticket->thanhToan->thoi_gian ? \Carbon\Carbon::parse($ticket->thanhToan->thoi_gian)->format('d/m/Y H:i') : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- QR Code Section -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
            <i class="fas fa-qrcode text-[#F53003]"></i>
            <span>Mã QR Vé</span>
        </h2>
        @php
            $qrData = 'ticket_id=' . $ticket->id;
            if ($ticket->ticket_code) {
                $qrData = 'ticket_id=' . $ticket->ticket_code;
            }
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrData);
        @endphp
        <div class="flex flex-col items-center justify-center">
            <div class="bg-white p-4 rounded-lg mb-4" style="min-height: 250px; min-width: 250px; display: flex; align-items: center; justify-center;">
                <img src="{{ $qrCodeUrl }}" alt="QR Code" id="qrcode-img-admin" style="width: 250px; height: 250px; display: block;" onerror="console.error('QR Image failed to load'); this.style.display='none'; document.getElementById('qrcode-fallback-admin').style.display='block'; generateQRCodeFallbackAdmin('{{ $qrData }}');">
                <div id="qrcode-fallback-admin" style="display: none; width: 250px; height: 250px;"></div>
            </div>
            <p class="text-sm text-[#a6a6b0] text-center">
                <i class="fas fa-info-circle mr-2"></i>
                Xuất trình mã QR này tại rạp để vào phòng chiếu
            </p>
            <p class="text-xs text-[#a6a6b0] text-center mt-2 font-mono">
                Mã vé: {{ $ticket->ticket_code ?: sprintf('MV%06d', $ticket->id) }}
            </p>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
function generateQRCodeFallbackAdmin(qrData) {
    const fallbackElement = document.getElementById('qrcode-fallback-admin');
    const imgElement = document.getElementById('qrcode-img-admin');
    
    if (fallbackElement && typeof QRCode !== 'undefined') {
        imgElement.style.display = 'none';
        fallbackElement.style.display = 'block';
        new QRCode(fallbackElement, {
            text: qrData,
            width: 250,
            height: 250,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    } else {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
        script.onload = function() {
            if (fallbackElement) {
                imgElement.style.display = 'none';
                fallbackElement.style.display = 'block';
                new QRCode(fallbackElement, {
                    text: qrData,
                    width: 250,
                    height: 250,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        };
        document.head.appendChild(script);
    }
}

let isPrinting = false;

async function printTicket(ticketId) {
    if (isPrinting) {
        return;
    }

    const printBtn = document.getElementById('print-ticket-btn');
    if (!printBtn || printBtn.disabled) {
        window.print();
        return;
    }

    isPrinting = true;
    printBtn.disabled = true;
    printBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';

    try {
        const response = await fetch(`/admin/scan/${ticketId}/mark-printed`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            printBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Đã in';
            printBtn.classList.remove('bg-[#F53003]', 'hover:bg-[#ff4d4d]');
            printBtn.classList.add('bg-gray-600', 'text-gray-400', 'cursor-not-allowed');
            
            setTimeout(() => {
                window.print();
            }, 500);
        } else {
            alert('Vé này đã được in rồi. Thời gian in: ' + (data.printed_at || 'N/A'));
            printBtn.disabled = true;
            printBtn.innerHTML = '<i class="fas fa-print mr-2"></i>Đã in';
            printBtn.classList.remove('bg-[#F53003]', 'hover:bg-[#ff4d4d]');
            printBtn.classList.add('bg-gray-600', 'text-gray-400', 'cursor-not-allowed');
        }
    } catch (error) {
        console.error('Error marking ticket as printed:', error);
        alert('Có lỗi xảy ra, nhưng vẫn có thể in vé.');
        window.print();
        printBtn.disabled = false;
        printBtn.innerHTML = '<i class="fas fa-print mr-2"></i>In vé';
    } finally {
        isPrinting = false;
    }
}
</script>

<style>
@media print {
    .print-hidden {
        display: none !important;
    }
    
    body * {
        visibility: hidden;
    }
    
    .space-y-6, .space-y-6 * {
        visibility: visible;
    }
    
    .space-y-6 {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
    .bg-\[#151822\], .bg-\[#1a1d24\] {
        background: white !important;
        border: 1px solid #000 !important;
    }
    
    .text-white {
        color: #000 !important;
    }
    
    .text-\[#a6a6b0\] {
        color: #666 !important;
    }
    
    /* Ensure QR code is visible when printing */
    img[alt="QR Code"], #qrcode-img-admin, #qrcode-fallback-admin {
        visibility: visible !important;
        display: block !important;
        max-width: 100% !important;
        height: auto !important;
    }
    
    #qrcode-fallback-admin canvas {
        visibility: visible !important;
        display: block !important;
    }
    
    @page {
        size: A4;
        margin: 10mm;
    }
}
</style>
@endsection

