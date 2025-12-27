@extends('admin.layout')

@section('title', 'Vé của tôi')

@section('content')
    {{-- Thông báo --}}
    @if (session('success'))
        <div class="text-green-400 text-sm bg-green-900/30 px-3 py-2 rounded mb-4 border border-green-900">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="text-red-400 text-sm bg-red-900/30 px-3 py-2 rounded mb-4 border border-red-900">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Thống kê nhanh --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-gradient-to-br from-blue-900/30 to-blue-800/20 border border-blue-500/30 rounded-xl p-4">
            <div class="text-xs text-blue-300">Tổng vé</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $totalBookings ?? 0 }}</div>
        </div>
        <div class="bg-gradient-to-br from-yellow-900/30 to-yellow-800/20 border border-yellow-500/30 rounded-xl p-4">
            <div class="text-xs text-yellow-300">Chờ thanh toán</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $pendingCount ?? 0 }}</div>
        </div>
        <div class="bg-gradient-to-br from-green-900/30 to-green-800/20 border border-green-500/30 rounded-xl p-4">
            <div class="text-xs text-green-300">Đã xác nhận</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $confirmedCount ?? 0 }}</div>
        </div>
        <div class="bg-gradient-to-br from-red-900/30 to-red-800/20 border border-red-500/30 rounded-xl p-4">
            <div class="text-xs text-red-300">Đã hủy</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $canceledCount ?? 0 }}</div>
        </div>
    </div>

    {{-- Card chính --}}
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-ticket-alt text-blue-500"></i>
                Vé đã đặt của tôi
            </h2>
            <a href="{{ route('admin.bookings.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                <i class="fas fa-plus"></i>
                <span>Đặt vé mới</span>
            </a>
        </div>

        {{-- Form tìm kiếm --}}
        <form method="GET" action="{{ route('admin.bookings.my-bookings') }}" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Trạng thái</label>
                    <select name="status" class="w-full bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg px-3 py-2 outline-none focus:border-blue-500">
                        <option value="">Tất cả</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Chờ thanh toán</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tên phim</label>
                    <input type="text" name="phim" value="{{ request('phim') }}" placeholder="Tìm phim..."
                        class="w-full bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg px-3 py-2 outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Ngày đặt</label>
                    <input type="date" name="booking_date" value="{{ request('booking_date') }}"
                        class="w-full bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg px-3 py-2 outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Ngày chiếu</label>
                    <input type="date" name="show_date" value="{{ request('show_date') }}"
                        class="w-full bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg px-3 py-2 outline-none focus:border-blue-500">
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('admin.bookings.my-bookings') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm transition">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>

        {{-- Bảng dữ liệu --}}
        @if ($bookings->isEmpty())
            <div class="text-center text-gray-400 py-16 border border-dashed border-[#262833] rounded-xl">
                <i class="fas fa-ticket-alt text-4xl mb-4 text-gray-600"></i>
                <p class="text-lg">Bạn chưa có vé nào</p>
                <a href="{{ route('admin.bookings.create') }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Đặt vé ngay
                </a>
            </div>
        @else
            <div class="overflow-x-auto rounded-lg border border-[#262833]">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-[#1b1e28] text-gray-400 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Mã vé</th>
                            <th class="px-4 py-3">Phim & Suất chiếu</th>
                            <th class="px-4 py-3">Ghế</th>
                            <th class="px-4 py-3">Tổng tiền</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#262833] bg-[#151822]">
                        @foreach ($bookings as $booking)
                            @php
                                $isExpired = optional($booking->suatChieu)->thoi_gian_bat_dau < now();
                                $statusColors = [
                                    0 => 'bg-yellow-900/30 text-yellow-400 border-yellow-700/50',
                                    1 => 'bg-green-900/30 text-green-400 border-green-700/50',
                                    2 => 'bg-red-900/30 text-red-400 border-red-700/50',
                                ];
                                $statusTexts = [
                                    0 => 'Chờ thanh toán',
                                    1 => 'Đã xác nhận',
                                    2 => 'Đã hủy',
                                ];
                            @endphp
                            <tr class="hover:bg-[#1b1e28]/70 transition">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-blue-400">#{{ $booking->id }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $booking->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-white mb-1">
                                        {{ $booking->suatChieu?->phim?->ten_phim ?? 'Phim đã xóa' }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('H:i d/m/Y') ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        <i class="fas fa-door-open mr-1"></i>
                                        {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($booking->chiTietDatVe && $booking->chiTietDatVe->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($booking->chiTietDatVe as $detail)
                                                @php
                                                    $loaiGhe = $detail->ghe->loaiGhe->ten_loai ?? '';
                                                    $isVip = stripos($loaiGhe, 'vip') !== false;
                                                    $isCouple = stripos($loaiGhe, 'đôi') !== false || stripos($loaiGhe, 'couple') !== false;
                                                    $badgeColor = 'bg-gray-700 text-gray-300';
                                                    if ($isVip) {
                                                        $badgeColor = 'bg-yellow-900/40 text-yellow-400 border border-yellow-700/50';
                                                    }
                                                    if ($isCouple) {
                                                        $badgeColor = 'bg-pink-900/40 text-pink-400 border border-pink-700/50';
                                                    }
                                                @endphp
                                                <span class="text-xs px-2 py-1 rounded {{ $badgeColor }}">
                                                    {{ $detail->ghe->so_ghe ?? '?' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">Không có ghế</span>
                                    @endif
                                    
                                    @if ($booking->chiTietCombo && $booking->chiTietCombo->count() > 0)
                                        <div class="mt-2 text-xs text-gray-400">
                                            @foreach ($booking->chiTietCombo as $detail)
                                                <div>+ {{ $detail->combo->ten ?? 'Combo' }} x{{ $detail->so_luong }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-green-400">
                                        {{ number_format($booking->tong_tien ?? 0, 0, ',', '.') }} đ
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ $booking->thanhToan->phuong_thuc ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs border {{ $statusColors[$booking->trang_thai] ?? 'bg-gray-700 text-gray-300' }}">
                                        {{ $statusTexts[$booking->trang_thai] ?? 'N/A' }}
                                    </span>
                                    @if ($isExpired && $booking->trang_thai == 1)
                                        <div class="text-xs text-gray-500 mt-1">Đã chiếu</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 hover:bg-blue-500 hover:text-white transition"
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $bookings->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
@endsection

