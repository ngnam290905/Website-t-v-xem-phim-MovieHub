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
                    onclick="window.print()"
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

        <!-- Combo Info -->
        @if(isset($comboItems) && $comboItems->isNotEmpty())
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-box text-[#ffcc00]"></i>
                <span>Combo đã chọn</span>
            </h2>
            <div class="space-y-2">
                @foreach($comboItems as $comboDetail)
                    @php
                        $c = $comboDetail->combo;
                        $comboName = $c->ten ?? $c->ten_combo ?? 'Combo';
                        $qty = max(1, (int)($comboDetail->so_luong ?? 1));
                        $unit = (float)($comboDetail->gia_ap_dung ?? $c->gia ?? 0);
                        $lineTotal = $unit * $qty;
                    @endphp
                    <div class="flex justify-between items-center p-2 bg-[#1a1d24] rounded">
                        <div>
                            <span class="text-white font-semibold">{{ $comboName }}</span>
                            <span class="text-[#a6a6b0] text-sm ml-2">x{{ $qty }}</span>
                            <div class="text-[#a6a6b0] text-xs mt-1">Đơn giá: {{ number_format($unit, 0, ',', '.') }} đ</div>
                        </div>
                        <span class="text-white font-semibold">{{ number_format($lineTotal, 0, ',', '.') }} đ</span>
                    </div>
                @endforeach
                <div class="pt-2 border-t border-[#262833] flex justify-between">
                    <span class="text-[#a6a6b0]">Tổng combo:</span>
                    <span class="text-white font-semibold">
                        {{ number_format($comboItems->sum(function($i){ return (float)$i->gia_ap_dung * max(1,(int)$i->so_luong); }), 0, ',', '.') }} đ
                    </span>
                </div>
            </div>
        </div>
        @endif

        <!-- Foods Info -->
        @if(isset($foodItems) && $foodItems->isNotEmpty())
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-utensils text-[#ff784e]"></i>
                <span>Đồ ăn đã chọn</span>
            </h2>
            <div class="space-y-2">
                @foreach($foodItems as $foodDetail)
                    @php
                        $f = $foodDetail->food;
                        $foodName = $f->name ?? 'Đồ ăn';
                        $qty = max(1, (int)($foodDetail->quantity ?? 1));
                        $unit = (float)($foodDetail->price ?? $f->price ?? 0);
                        $lineTotal = $unit * $qty;
                    @endphp
                    <div class="flex justify-between items-center p-2 bg-[#1a1d24] rounded">
                        <div>
                            <span class="text-white font-semibold">{{ $foodName }}</span>
                            <span class="text-[#a6a6b0] text-sm ml-2">x{{ $qty }}</span>
                            <div class="text-[#a6a6b0] text-xs mt-1">Đơn giá: {{ number_format($unit, 0, ',', '.') }} đ</div>
                        </div>
                        <span class="text-white font-semibold">{{ number_format($lineTotal, 0, ',', '.') }} đ</span>
                    </div>
                @endforeach
                <div class="pt-2 border-t border-[#262833] flex justify-between">
                    <span class="text-[#a6a6b0]">Tổng đồ ăn:</span>
                    <span class="text-white font-semibold">
                        {{ number_format($foodItems->sum(function($f){ return (float)$f->price * max(1,(int)$f->quantity); }), 0, ',', '.') }} đ
                    </span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

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
    
    @page {
        size: A4;
        margin: 10mm;
    }
}
</style>
@endsection

