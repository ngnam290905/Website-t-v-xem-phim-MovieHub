@extends('admin.layout')

@section('title', 'Quản lý đặt vé')

@section('content')
    @if (session('error'))
        <div class="text-red-400 text-sm bg-red-900/30 px-3 py-2 rounded mb-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">🎟️ Quản lý Đặt Vé</h2>
        </div>

        {{-- Bộ lọc --}}
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="w-full bg-[#151822] border border-[#262833] rounded-xl p-4 flex flex-wrap items-end gap-3 mb-6">
                {{-- Lọc theo trạng thái --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Tổng đơn</div>
                <div class="text-2xl font-bold text-white mt-1">{{ $totalBookings ?? 0 }}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Chờ xác nhận</div>
                <div class="text-2xl font-bold text-yellow-400 mt-1">{{ $pendingCount ?? 0 }}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Đã xác nhận</div>
                <div class="text-2xl font-bold text-green-400 mt-1">{{ $confirmedCount ?? 0 }}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Đã hủy</div>
                <div class="text-2xl font-bold text-red-400 mt-1">{{ $canceledCount ?? 0 }}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Yêu cầu hủy</div>
                <div class="text-2xl font-bold text-orange-300 mt-1">{{ $requestCancelCount ?? 0 }}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Doanh thu hôm nay</div>
                <div class="text-2xl font-bold text-blue-400 mt-1">{{ number_format($revenueToday ?? 0) }} VNĐ</div>
            </div>
        </div>
                <div>
                    <label class="block text-xs text-[#a6a6b0] mb-1">Trạng thái</label>
                    <select name="status"
                        class="w-48 bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Chờ xác nhận</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Yêu cầu hủy</option>
                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>

                {{-- Lọc theo phim --}}
                <div>
                    <label class="block text-xs text-[#a6a6b0] mb-1">Phim</label>
                    <input type="text" name="phim" value="{{ request('phim') }}" placeholder="Tên phim..."
                        class="w-56 bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300 placeholder-gray-500">
                </div>

                {{-- Lọc theo người dùng --}}
                <div>
                    <label class="block text-xs text-[#a6a6b0] mb-1">Người dùng</label>
                    <input type="text" name="nguoi_dung" value="{{ request('nguoi_dung') }}" placeholder="Tên người dùng..."
                        class="w-56 bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300 placeholder-gray-500">
                </div>

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm text-white transition flex items-center gap-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>

                @if (request()->hasAny(['status', 'phim', 'nguoi_dung']))
                    <a href="{{ route('admin.bookings.index') }}"
                        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm text-white transition">
                        Xóa bộ lọc
                    </a>
                @endif
        </form>


            @if (session('success'))
                <div class="text-green-400 text-sm bg-green-900/30 px-3 py-2 rounded">
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <!-- Quick Stats -->
        

        @if ($bookings->isEmpty())
            <div class="text-center text-gray-400 py-10 border border-dashed border-[#262833] rounded-xl">
                <p>Chưa có dữ liệu đặt vé. Kiểm tra database hoặc chạy seeder.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-[#262833] rounded-xl">
                    <thead class="bg-[#1b1e28] text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Mã đơn hàng</th>
                            <th class="px-4 py-3">Tên khách hàng</th>
                            <th class="px-4 py-3">Phim / Suất chiếu</th>
                            <th class="px-4 py-3">Ghế</th>
                            <th class="px-4 py-3">Combo</th>
                            <th class="px-4 py-3">Tổng tiền</th>
                            <th class="px-4 py-3">Mã KM</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3">Thời gian đặt</th>
                            <th class="px-4 py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#262833]">
                        @foreach ($bookings as $booking)
                            <tr class="hover:bg-[#1b1e28]/70 transition">
                                <td class="px-4 py-3 font-medium">#{{ $booking->id }}</td>
                                <td class="px-4 py-3">{{ $booking->nguoiDung->ho_ten ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-200">{{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ $booking->suatChieu?->thoi_gian_bat_dau?->format('d/m/Y H:i') ?? 'N/A' }}
                                        • Phòng: {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $seatLabels = $booking->chiTietDatVe->map(function($d){ return optional($d->ghe)->so_ghe; })->filter()->implode(', ');
                                    @endphp
                                    {{ $seatLabels ?: 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $comboLabels = $booking->chiTietCombo->map(function($c){
                                            $name = $c->combo->ten ?? '—';
                                            $qty = $c->so_luong ? (' × ' . $c->so_luong) : '';
                                            return $name . $qty;
                                        })->filter()->implode(', ');
                                    @endphp
                                    {{ $comboLabels ?: '—' }}
                                </td>
                                @php
                                    $totalToShow = $booking->tong_tien ?? $booking->tong_tien_hien_thi ?? 0;
                                @endphp
                                <td class="px-4 py-3">{{ number_format($totalToShow) }} VNĐ</td>
                                <td class="px-4 py-3">{{ $booking->khuyenMai?->ma_km ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @switch($booking->trang_thai)
                                        @case(0)
                                            <span class="px-2 py-1 text-yellow-400 bg-yellow-900/30 rounded-full text-xs">Chờ xác nhận</span>
                                            @break
                                        @case(1)
                                            <span class="px-2 py-1 text-green-400 bg-green-900/30 rounded-full text-xs">Đã xác nhận</span>
                                            @break
                                        @case(3)
                                            <span class="px-2 py-1 text-orange-300 bg-orange-900/30 rounded-full text-xs">Yêu cầu hủy</span>
                                            @break
                                        @case(2)
                                            <span class="px-2 py-1 text-red-400 bg-red-900/30 rounded-full text-xs">Đã hủy</span>
                                            @break
                                        @default
                                            <span class="px-2 py-1 text-gray-400 bg-gray-800 rounded-full text-xs">Không xác định</span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3">{{ optional($booking->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1.5">

                                        {{-- Xem chi tiết --}}
                                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                            class="p-1.5 rounded-md bg-blue-600/80 hover:bg-blue-600 transition"
                                            title="Xem vé">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        {{-- Chỉnh sửa (admin + chưa hủy) --}}
                                        @auth
                                            @if (optional(auth()->user()->vaiTro)->ten === 'admin' && $booking->trang_thai != 2)
                                                <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                                    class="p-1.5 rounded-md bg-yellow-500/80 hover:bg-yellow-500 transition"
                                                    title="Chỉnh sửa">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2 2 0 112.828 2.828L11.828 13.828a2 2 0 01-.828.5L7 15l1.172-4a2 2 0 01.5-.828z" />
                                                    </svg>
                                                </a>
                                            @endif
                                        @endauth

                                    </div>
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
