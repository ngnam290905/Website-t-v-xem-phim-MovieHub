@extends('layouts.main')

@section('title', 'Vé của tôi - MovieHub')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#0a1a2f] via-[#0f1f3a] to-[#151822] py-8 px-4">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 flex items-center gap-3">
                <i class="fas fa-ticket-alt text-[#ffcc00]"></i>
                <span>Vé của tôi</span>
            </h1>
            <p class="text-[#a6a6b0]">Quản lý và xem chi tiết các vé đã đặt</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-[#1a1d24] to-[#151822] border border-[#2a2d3a] rounded-xl p-5 hover:border-[#0077c8] transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 rounded-lg bg-[#0077c8]/20 flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-[#0077c8] text-xl"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">{{ $stats['total'] ?? 0 }}</div>
                <div class="text-sm text-[#a6a6b0]">Tổng vé</div>
            </div>

            <div class="bg-gradient-to-br from-[#1a1d24] to-[#151822] border border-[#2a2d3a] rounded-xl p-5 hover:border-[#10b981] transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 rounded-lg bg-[#10b981]/20 flex items-center justify-center">
                        <i class="fas fa-check-circle text-[#10b981] text-xl"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">{{ $stats['paid'] ?? 0 }}</div>
                <div class="text-sm text-[#a6a6b0]">Đã thanh toán</div>
            </div>

            <div class="bg-gradient-to-br from-[#1a1d24] to-[#151822] border border-[#2a2d3a] rounded-xl p-5 hover:border-[#ffcc00] transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 rounded-lg bg-[#ffcc00]/20 flex items-center justify-center">
                        <i class="fas fa-clock text-[#ffcc00] text-xl"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">{{ $stats['draft'] ?? 0 }}</div>
                <div class="text-sm text-[#a6a6b0]">Đang xử lý</div>
            </div>

            <div class="bg-gradient-to-br from-[#1a1d24] to-[#151822] border border-[#2a2d3a] rounded-xl p-5 hover:border-[#ef4444] transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 rounded-lg bg-[#ef4444]/20 flex items-center justify-center">
                        <i class="fas fa-times-circle text-[#ef4444] text-xl"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">{{ $stats['cancelled'] ?? 0 }}</div>
                <div class="text-sm text-[#a6a6b0]">Đã hủy</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gradient-to-br from-[#1a1d24] to-[#151822] border border-[#2a2d3a] rounded-xl p-6 mb-8">
            <form method="GET" action="{{ route('booking.tickets') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[#e6e7eb] mb-2">
                            <i class="fas fa-search mr-2 text-[#0077c8]"></i>Tìm kiếm phim
                        </label>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            placeholder="Nhập tên phim..."
                            class="w-full px-4 py-3 bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg text-white placeholder-[#6b7280] focus:outline-none focus:border-[#0077c8] transition-colors"
                        >
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-[#e6e7eb] mb-2">
                            <i class="fas fa-filter mr-2 text-[#0077c8]"></i>Trạng thái
                        </label>
                        <select 
                            name="status" 
                            class="w-full px-4 py-3 bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg text-white focus:outline-none focus:border-[#0077c8] transition-colors"
                            onchange="this.form.submit()"
                        >
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tất cả</option>
                            <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Đang xử lý</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label class="block text-sm font-medium text-[#e6e7eb] mb-2">
                            <i class="fas fa-calendar mr-2 text-[#0077c8]"></i>Ngày chiếu
                        </label>
                        <input 
                            type="date" 
                            name="date" 
                            value="{{ request('date') }}" 
                            class="w-full px-4 py-3 bg-[#0a1a2f] border border-[#2a2d3a] rounded-lg text-white focus:outline-none focus:border-[#0077c8] transition-colors"
                        >
                    </div>
                </div>

                <div class="flex gap-3">
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-[#0077c8] to-[#0099e6] text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-[#0077c8]/50 transition-all flex items-center gap-2"
                    >
                        <i class="fas fa-search"></i>
                        <span>Tìm kiếm</span>
                    </button>
                    @if(request('search') || request('date') || request('status') !== 'all')
                        <a 
                            href="{{ route('booking.tickets') }}" 
                            class="px-6 py-3 bg-[#2a2d3a] text-white rounded-lg font-semibold hover:bg-[#3a3d4a] transition-all flex items-center gap-2"
                        >
                            <i class="fas fa-times"></i>
                            <span>Xóa lọc</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tickets List -->
        @if($bookings->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                @foreach($bookings as $booking)
                    @php
                        $showtime = $booking->suatChieu;
                        $movie = $showtime->phim ?? null;
                        $room = $showtime->phongChieu ?? null;
                        $seats = $booking->chiTietDatVe;
                        $combos = $booking->chiTietCombo;
                        $totalPrice = $booking->tong_tien_hien_thi ?? 0;
                        
                        // Status mapping
                        $statusMap = [
                            0 => ['label' => 'Đang xử lý', 'bg' => 'bg-yellow-500/20', 'text' => 'text-yellow-400', 'border' => 'border-yellow-500/50', 'icon' => 'clock'],
                            1 => ['label' => 'Đã thanh toán', 'bg' => 'bg-green-500/20', 'text' => 'text-green-400', 'border' => 'border-green-500/50', 'icon' => 'check-circle'],
                            2 => ['label' => 'Đã hủy', 'bg' => 'bg-red-500/20', 'text' => 'text-red-400', 'border' => 'border-red-500/50', 'icon' => 'times-circle'],
                        ];
                        $currentStatus = $statusMap[$booking->trang_thai] ?? $statusMap[0];
                        $isPaid = $booking->trang_thai == 1;
                        $isCancelled = $booking->trang_thai == 2;
                    @endphp

                    <div class="bg-gradient-to-br from-[#1a1d24] to-[#151822] border border-[#2a2d3a] rounded-xl overflow-hidden hover:border-[#0077c8] transition-all group">
                        <!-- Ticket Header -->
                        <div class="relative p-6 bg-gradient-to-r from-[#0077c8]/20 to-[#0099e6]/20 border-b border-[#2a2d3a]">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $currentStatus['bg'] }} {{ $currentStatus['text'] }} border {{ $currentStatus['border'] }}">
                                            <i class="fas fa-{{ $currentStatus['icon'] }} mr-1"></i>
                                            {{ $currentStatus['label'] }}
                                        </span>
                                        <span class="text-[#a6a6b0] text-sm">
                                            Mã vé: #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                    @if($movie)
                                        <h3 class="text-xl font-bold text-white mb-1 group-hover:text-[#ffcc00] transition-colors">
                                            {{ $movie->ten_phim }}
                                        </h3>
                                    @endif
                                </div>
                                @if($movie && $movie->poster_url)
                                    <x-image 
                                      src="{{ $movie->poster_url }}" 
                                      alt="{{ $movie->ten_phim }}"
                                      aspectRatio="2/3"
                                      class="w-20 h-28 rounded-lg border border-[#2a2d3a]"
                                      quality="high"
                                    />
                                @endif
                            </div>
                        </div>

                        <!-- Ticket Body -->
                        <div class="p-6 space-y-4">
                            <!-- Showtime Info -->
                            @if($showtime)
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-xs text-[#a6a6b0] mb-1">Ngày & Giờ</div>
                                        <div class="text-white font-semibold">
                                            <i class="fas fa-calendar-alt text-[#0077c8] mr-2"></i>
                                            {{ $showtime->thoi_gian_bat_dau->format('d/m/Y') }}
                                        </div>
                                        <div class="text-white font-semibold">
                                            <i class="fas fa-clock text-[#0077c8] mr-2"></i>
                                            {{ $showtime->thoi_gian_bat_dau->format('H:i') }}
                                        </div>
                                    </div>
                                    @if($room)
                                        <div>
                                            <div class="text-xs text-[#a6a6b0] mb-1">Phòng chiếu</div>
                                            <div class="text-white font-semibold">
                                                <i class="fas fa-door-open text-[#0077c8] mr-2"></i>
                                                {{ $room->ten_phong ?? $room->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Seats -->
                            @if($seats->count() > 0)
                                <div>
                                    <div class="text-xs text-[#a6a6b0] mb-2">Ghế đã chọn</div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($seats as $seatDetail)
                                            @php
                                                $seat = $seatDetail->ghe;
                                                $seatType = $seat->seatType ?? null;
                                                $isVip = $seatType && strpos(strtolower($seatType->ten_loai ?? ''), 'vip') !== false;
                                            @endphp
                                            <span class="px-3 py-1 rounded-lg text-sm font-semibold {{ $isVip ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50' : 'bg-[#0077c8]/20 text-[#0077c8] border border-[#0077c8]/50' }}">
                                                <i class="fas fa-{{ $isVip ? 'crown' : 'chair' }} mr-1"></i>
                                                {{ $seat->so_ghe }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Combos -->
                            @if($combos->count() > 0)
                                <div>
                                    <div class="text-xs text-[#a6a6b0] mb-2">Combo</div>
                                    <div class="space-y-1">
                                        @foreach($combos as $comboDetail)
                                            <div class="text-sm text-white">
                                                <i class="fas fa-box text-[#ffcc00] mr-2"></i>
                                                {{ $comboDetail->combo->ten ?? 'N/A' }} 
                                                <span class="text-[#a6a6b0]">x{{ $comboDetail->so_luong }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Price -->
                            <div class="pt-4 border-t border-[#2a2d3a]">
                                <div class="flex items-center justify-between">
                                    <span class="text-[#a6a6b0]">Tổng tiền</span>
                                    <span class="text-2xl font-bold text-[#ffcc00]">
                                        {{ number_format($totalPrice) }}đ
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Footer -->
                        <div class="px-6 py-4 bg-[#0a1a2f] border-t border-[#2a2d3a] flex items-center justify-between">
                            <div class="text-xs text-[#a6a6b0]">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Đặt ngày: {{ $booking->created_at->format('d/m/Y H:i') }}
                            </div>
                            <div class="flex gap-2">
                                @if($isPaid)
                                    <a 
                                        href="{{ route('booking.ticket.detail', $booking->id) }}" 
                                        class="px-4 py-2 bg-gradient-to-r from-[#0077c8] to-[#0099e6] text-white rounded-lg text-sm font-semibold hover:shadow-lg hover:shadow-[#0077c8]/50 transition-all flex items-center gap-2"
                                    >
                                        <i class="fas fa-eye"></i>
                                        <span>Chi tiết</span>
                                    </a>
                                @endif
                                @if(!$isCancelled && $showtime && $showtime->thoi_gian_bat_dau > now())
                                    <button 
                                        onclick="cancelTicket({{ $booking->id }})"
                                        class="px-4 py-2 bg-[#ef4444]/20 text-[#ef4444] border border-[#ef4444]/50 rounded-lg text-sm font-semibold hover:bg-[#ef4444]/30 transition-all flex items-center gap-2"
                                    >
                                        <i class="fas fa-times"></i>
                                        <span>Hủy vé</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $bookings->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-gradient-to-br from-[#1a1d24] to-[#151822] border border-[#2a2d3a] rounded-xl p-12 text-center">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-[#0077c8]/20 flex items-center justify-center">
                    <i class="fas fa-ticket-alt text-[#0077c8] text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Chưa có vé nào</h3>
                <p class="text-[#a6a6b0] mb-6">Bạn chưa đặt vé nào. Hãy đặt vé để xem phim ngay!</p>
                <a 
                    href="{{ route('public.movies') }}" 
                    class="inline-block px-6 py-3 bg-gradient-to-r from-[#0077c8] to-[#0099e6] text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-[#0077c8]/50 transition-all"
                >
                    <i class="fas fa-film mr-2"></i>
                    Xem phim đang chiếu
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function cancelTicket(bookingId) {
    if (confirm('Bạn có chắc chắn muốn hủy vé này? Hành động này không thể hoàn tác.')) {
        // TODO: Implement cancel ticket API
        alert('Tính năng hủy vé đang được phát triển');
    }
}
</script>
@endsection
