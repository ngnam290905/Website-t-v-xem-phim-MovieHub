@extends('admin.layout')

@section('title', 'Chi tiết vé - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Chi tiết vé #{{ $ticket->id }}</h1>
        <a 
            href="{{ route('admin.scan.index') }}" 
            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition"
        >
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ticket Info -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4">Thông tin vé</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-[#a6a6b0]">Mã vé:</span>
                    <span class="text-white font-mono font-semibold">{{ $ticket->ticket_code ?? 'N/A' }}</span>
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
</div>
@endsection

