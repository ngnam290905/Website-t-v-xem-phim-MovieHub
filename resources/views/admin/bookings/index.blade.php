@extends('admin.layout')

@section('title', 'Quản lý đặt vé')

@section('content')
    {{-- 1. Thông báo --}}
    @if (session('success'))
        <div class="text-green-400 text-sm bg-green-900/30 px-3 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="text-red-400 text-sm bg-red-900/30 px-3 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- 2. Thống kê nhanh --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-7 gap-4 mb-6"> {{-- 💡 Thay đổi thành 7 cột --}}
        {{-- Thẻ "Tất cả" --}}
        <a href="{{ route('admin.bookings.index') }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-4 hover:border-blue-500 transition 
                   {{ !request('status') ? 'border-blue-500 ring-1 ring-blue-500' : '' }}">
            <div class="text-sm text-[#a6a6b0]">Tổng đơn</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $totalBookings ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.bookings.index', ['status' => 0]) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-4 hover:border-yellow-400 transition 
                   {{ request('status') == '0' ? 'border-yellow-400 ring-1 ring-yellow-400' : '' }}">
            <div class="text-sm text-[#a6a6b0]">Chờ xác nhận</div>
            <div class="text-2xl font-bold text-yellow-400 mt-1">{{ $pendingCount ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.bookings.index', ['status' => 1]) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-4 hover:border-green-400 transition 
                   {{ request('status') == '1' ? 'border-green-400 ring-1 ring-green-400' : '' }}">
            <div class="text-sm text-[#a6a6b0]">Đã xác nhận</div>
            <div class="text-2xl font-bold text-green-400 mt-1">{{ $confirmedCount ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.bookings.index', ['status' => 3]) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-4 hover:border-orange-300 transition 
                   {{ request('status') == '3' ? 'border-orange-300 ring-1 ring-orange-300' : '' }}">
            <div class="text-sm text-[#a6a6b0]">Yêu cầu hủy</div>
            <div class="text-2xl font-bold text-orange-300 mt-1">{{ $requestCancelCount ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.bookings.index', ['status' => 2]) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-4 hover:border-red-400 transition 
                   {{ request('status') == '2' ? 'border-red-400 ring-1 ring-red-400' : '' }}">
            <div class="text-sm text-[#a6a6b0]">Đã hủy</div>
            <div class="text-2xl font-bold text-red-400 mt-1">{{ $canceledCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.bookings.index', ['status' => 'expired']) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-4 hover:border-gray-500 transition 
                   {{ request('status') == 'expired' ? 'border-gray-500 ring-1 ring-gray-500' : '' }}">
            <div class="text-sm text-[#a6a6b0]">Đã hết hạn</div>
            <div class="text-2xl font-bold text-gray-500 mt-1">{{ $expiredCount ?? 0 }}</div>
        </a>

        <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
            <div class="text-sm text-[#a6a6b0]">Doanh thu hôm nay</div>
            <div class="text-2xl font-bold text-blue-400 mt-1">{{ number_format($revenueToday ?? 0) }} VNĐ</div>
        </div>
    </div>

    {{-- 3. Card chính (Lọc + Bảng) --}}
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">🎟️ Danh sách Đặt Vé</h2>
        </div>

        {{-- Bộ lọc --}}
        <form method="GET" action="{{ route('admin.bookings.index') }}"
            class="w-full flex flex-wrap items-end gap-3 mb-6">
            {{-- Lọc theo trạng thái --}}
            <div>
                <label class="block text-xs text-[#a6a6b0] mb-1">Trạng thái</label>
                <select name="status"
                    class="w-48 bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Chờ xác nhận</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Yêu cầu hủy</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
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
                <input type="text" name="nguoi_dung" value="{{ request('nguoi_dung') }}"
                    placeholder="Tên, email, hoặc SĐT..."
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

        {{-- Bảng dữ liệu --}}
        @if ($bookings->isEmpty())
            <div class="text-center text-gray-400 py-10 border border-dashed border-[#262833] rounded-xl">
                <p>Không tìm thấy vé nào phù hợp với bộ lọc.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-[#262833] rounded-xl">
                    <thead class="bg-[#1b1e28] text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Mã ĐV</th>
                            <th class="px-4 py-3">Khách hàng</th>
                            <th class="px-4 py-3">Phim / Suất chiếu</th>
                            <th class="px-4 py-3">Ghế & Combo</th>
                            <th class="px-4 py-3">Thanh toán</th>
                            <th class="px-4 py-3">Trạng thái Vé</th>
                            <th class="px-4 py-3">Thời gian đặt</th>
                            <th class="px-4 py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#262833]">
                        @foreach ($bookings as $booking)
                            @php
                                // 💡 LOGIC MỚI: Kiểm tra xem vé đã hết hạn (suất chiếu đã qua)
                                $isExpired = $booking->suatChieu?->thoi_gian_bat_dau < now();
                                // Vé có thể chỉnh sửa khi: Chưa bị hủy (2) VÀ Chưa hết hạn
                                $isEditable = $booking->trang_thai != 2 && !$isExpired;
                            @endphp
                            <tr class="hover:bg-[#1b1e28]/70 transition">
                                <td class="px-4 py-3 font-medium">#{{ $booking->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-200">{{ $booking->nguoiDung->ho_ten ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-400">{{ $booking->nguoiDung->email ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-200">{{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ $booking->suatChieu?->thoi_gian_bat_dau?->format('d/m/Y H:i') ?? 'N/A' }}
                                        • {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $seatLabels = $booking->chiTietDatVe->map(fn($d) => optional($d->ghe)->so_ghe)->filter()->implode(', ');
                                        $comboLabels = $booking->chiTietCombo->map(function ($c) {
                                            $name = $c->combo->ten ?? '—';
                                            $qty = $c->so_luong > 1 ? ' × ' . $c->so_luong : ' × 1';
                                            return $name . $qty;
                                        })->filter()->implode(', ');
                                    @endphp
                                    <div class="font-medium text-gray-300">Ghế: {{ $seatLabels ?: 'N/A' }}</div>
                                    <div class="text-xs text-gray-400">Combo: {{ $comboLabels ?: 'Không' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-green-400">
                                        {{ number_format($booking->thanhToan?->so_tien ?? 0) }} VNĐ
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $booking->thanhToan?->phuong_thuc ?? 'Chưa TT' }}
                                        @if (optional($booking->thanhToan)->trang_thai === 1)
                                            <span class="text-green-500">(Thành công)</span>
                                        @else
                                            <span class="text-yellow-500">(Chưa XN)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @switch($booking->trang_thai)
                                        @case(0)
                                            <span class="px-2 py-1 text-yellow-400 bg-yellow-900/30 rounded-full text-xs">Chờ xác
                                                nhận</span>
                                        @break

                                        @case(1)
                                            @if ($isExpired)
                                                <span class="px-2 py-1 text-gray-400 bg-gray-800/50 rounded-full text-xs">Đã hết hạn</span>
                                            @else
                                                <span class="px-2 py-1 text-green-400 bg-green-900/30 rounded-full text-xs">Đã xác
                                                    nhận</span>
                                            @endif
                                        @break

                                        @case(3)
                                            <span class="px-2 py-1 text-orange-300 bg-orange-900/30 rounded-full text-xs">Yêu cầu
                                                hủy</span>
                                        @break

                                        @case(2)
                                            <span class="px-2 py-1 text-red-400 bg-red-900/30 rounded-full text-xs">Đã hủy</span>
                                        @break
                                    @endswitch
                                </td>
                                <td class="px-4 py-3">{{ optional($booking->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1.5">
                                        
                                        {{-- Nút Xác nhận nhanh (Chỉ hiển thị nếu CHƯA HẾT HẠN) --}}
                                        @if ($booking->trang_thai == 0 && !$isExpired)
                                            <form action="{{ route('admin.bookings.update', $booking->id) }}"
                                                method="POST">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="trang_thai" value="1">
                                                <button type="submit" title="Xác nhận vé"
                                                    class="p-1.5 rounded-md bg-green-600/80 hover:bg-green-600 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        {{-- Nút Chấp nhận hủy (Luôn hiển thị nếu có yêu cầu) --}}
                                        @if ($booking->trang_thai == 3)
                                            <form action="{{ route('admin.bookings.update', $booking->id) }}"
                                                method="POST">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="trang_thai" value="2">
                                                <button type="submit" title="Chấp nhận hủy"
                                                    class="p-1.5 rounded-md bg-red-600/80 hover:bg-red-600 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

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

                                        @auth
                                            @if (optional(auth()->user()->vaiTro)->ten === 'admin' && $isEditable)
                                                {{-- Chỉ cho sửa nếu là Admin, vé chưa hủy VÀ vé chưa hết hạn --}}
                                                <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                                    class="p-1.5 rounded-md bg-yellow-500/80 hover:bg-yellow-500 transition"
                                                    title="Chỉnh sửa">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.8"
                                                            d="M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2 2 0 112.828 2.828L11.828 13.828a2 2 0 01-.828.5L7 15l1.172-4a2 2 0 01.5-.828z" />
                                                    </svg>
                                                </a>
                                            @else
                                                {{-- Nút sửa bị vô hiệu hóa --}}
                                                <span class="p-1.5 rounded-md bg-gray-700/50 cursor-not-allowed" 
                                                      title="Không thể sửa vé đã hủy hoặc hết hạn">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.8"
                                                            d="M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2 2 0 112.828 2.828L11.828 13.828a2 2 0 01-.828.5L7 15l1.172-4a2 2 0 01.5-.828z" />
                                                    </svg>
                                                </span>
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