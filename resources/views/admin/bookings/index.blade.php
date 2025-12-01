@extends('admin.layout')

@section('title', 'Quản lý đặt vé')

@section('content')
    {{-- 1. Thông báo --}}
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

    {{-- 2. Thống kê nhanh --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3 mb-6">
        <a href="{{ route('admin.bookings.index') }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-3 hover:border-blue-500 transition {{ !request('status') ? 'border-blue-500 ring-1 ring-blue-500' : '' }}">
            <div class="text-xs text-[#a6a6b0]">Tổng đơn</div>
            <div class="text-xl font-bold text-white mt-1">{{ $totalBookings ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.bookings.index', ['status' => 0]) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-3 hover:border-yellow-400 transition {{ request('status') == '0' ? 'border-yellow-400 ring-1 ring-yellow-400' : '' }}">
            <div class="text-xs text-[#a6a6b0]">Chờ xác nhận</div>
            <div class="text-xl font-bold text-yellow-400 mt-1">{{ $pendingCount ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.bookings.index', ['status' => 1]) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-3 hover:border-green-400 transition {{ request('status') == '1' ? 'border-green-400 ring-1 ring-green-400' : '' }}">
            <div class="text-xs text-[#a6a6b0]">Đã xác nhận</div>
            <div class="text-xl font-bold text-green-400 mt-1">{{ $confirmedCount ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.bookings.index', ['status' => 3]) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-3 hover:border-orange-300 transition {{ request('status') == '3' ? 'border-orange-300 ring-1 ring-orange-300' : '' }}">
            <div class="text-xs text-[#a6a6b0]">Yêu cầu hủy</div>
            <div class="text-xl font-bold text-orange-300 mt-1">{{ $requestCancelCount ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.bookings.index', ['status' => 2]) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-3 hover:border-red-400 transition {{ request('status') == '2' ? 'border-red-400 ring-1 ring-red-400' : '' }}">
            <div class="text-xs text-[#a6a6b0]">Đã hủy</div>
            <div class="text-xl font-bold text-red-400 mt-1">{{ $canceledCount ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.bookings.index', ['status' => 'expired']) }}"
            class="block bg-[#151822] border border-[#262833] rounded-xl p-3 hover:border-gray-500 transition {{ request('status') == 'expired' ? 'border-gray-500 ring-1 ring-gray-500' : '' }}">
            <div class="text-xs text-[#a6a6b0]">Đã hết hạn</div>
            <div class="text-xl font-bold text-gray-500 mt-1">{{ $expiredCount ?? 0 }}</div>
        </a>
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-3">
            <div class="text-xs text-[#a6a6b0]">Doanh thu hôm nay</div>
            <div class="text-xl font-bold text-blue-400 mt-1 truncate" title="{{ number_format($revenueToday ?? 0) }} VNĐ">
                {{ number_format($revenueToday ?? 0) }} ₫
            </div>
        </div>
    </div>

    {{-- 3. Card chính (Lọc + Bảng) --}}

        <div class="text-green-400 text-sm bg-green-900/30 px-3 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="text-red-400 text-sm bg-red-900/30 px-3 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-ticket-alt text-blue-500"></i> Danh sách Đặt Vé
            </h2>
            {{-- FORM TÌM KIẾM ĐÃ TỐI ƯU --}}
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3">

                    {{-- 1. Trạng thái (2 cột) --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1 ml-1">Trạng thái</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-filter text-gray-500 text-xs"></i>
                            </div>
                            <select name="status"
                                class="w-full bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-8 p-2 outline-none appearance-none">
                                <option value="">-- Tất cả --</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Chờ xác nhận
                                </option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đã xác nhận
                                </option>
                                <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Yêu cầu hủy
                                </option>
                                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Đã hủy</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Đã hết hạn
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- 2. Ngày đặt vé (2 cột) --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1 ml-1">Ngày đặt</label>
                        <input type="date" name="booking_date" value="{{ request('booking_date') }}"
                            class="w-full bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2 outline-none">
                    </div>

                    {{-- 3. Suất chiếu (Ngày + Giờ) (3 cột) --}}
                    <div class="lg:col-span-3">
                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1 ml-1">Suất chiếu (Ngày -
                            Giờ)</label>
                        <div class="flex gap-2">
                            <input type="date" name="show_date" value="{{ request('show_date') }}"
                                class="w-2/3 bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2 outline-none"
                                placeholder="Ngày">
                            <input type="time" name="show_time" value="{{ request('show_time') }}"
                                class="w-1/3 bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2 outline-none">
                        </div>
                    </div>

                    {{-- 4. Tìm kiếm từ khóa (Phim + Khách) (4 cột) --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1 ml-1">Phim</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-film text-gray-500 text-xs"></i>
                            </div>
                            <input type="text" name="phim" value="{{ request('phim') }}" placeholder="Tên phim..."
                                class="w-full bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-8 p-2 outline-none">
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1 ml-1">Khách hàng</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-500 text-xs"></i>
                            </div>
                            <input type="text" name="nguoi_dung" value="{{ request('nguoi_dung') }}"
                                placeholder="Tên/SĐT/Email"
                                class="w-full bg-[#1b1e28] border border-[#262833] text-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-8 p-2 outline-none">
                        </div>
                    </div>

                    {{-- 5. Nút bấm (1 cột) --}}
                    <div class="lg:col-span-1 flex items-end gap-2">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm px-4 py-2.5 transition duration-200 flex items-center justify-center gap-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if ($bookings->isEmpty())

            <div class="text-center text-gray-400 py-16 border border-dashed border-[#262833] rounded-xl bg-[#1a1d26]/50">
                <i class="fas fa-search text-4xl mb-3 text-gray-600"></i>

            <div class="text-center text-gray-400 py-10 border border-dashed border-[#262833] rounded-xl">
                <p>Chưa có dữ liệu đặt vé. Kiểm tra database hoặc chạy seeder.</p>
    {{-- 2. Thống kê nhanh --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-7 gap-4 mb-6">
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
            <div class="overflow-x-auto rounded-lg border border-[#262833]">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-[#1b1e28] text-gray-400 uppercase text-xs font-semibold">
                        <tr>

                            <th class="px-4 py-3 whitespace-nowrap">Thông tin Vé</th>
                            <th class="px-4 py-3">Khách hàng</th>
                            <th class="px-4 py-3">Phim & Suất chiếu</th>
                            <th class="px-4 py-3">Chi tiết đặt chỗ</th>
                            <th class="px-4 py-3">Thanh toán</th>
                            <th class="px-4 py-3 text-center">Trạng thái</th>
                            <th class="px-4 py-3 text-center sticky right-0 bg-[#1b1e28]">Hành động</th>

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
                    <tbody class="divide-y divide-[#262833] bg-[#151822]">
                        @foreach ($bookings as $booking)
                            @php

                                $isExpired = optional($booking->suatChieu)->thoi_gian_bat_dau < now();
                                $isEditable = $booking->trang_thai != 2 && !$isExpired;
                            @endphp
                            {{-- ID ROW ĐỂ JS XÓA --}}
                            <tr id="row-{{ $booking->id }}" class="hover:bg-[#1b1e28]/70 transition group">
                                {{-- Cột 1: Thông tin Vé & Ngày tạo --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="font-bold text-blue-400 whitespace-nowrap">#{{ $booking->id }}</div>
                                    <div class="text-xs text-gray-500 mt-1" title="Ngày đặt">
                                        {{ $booking->created_at->format('d/m/Y') }}<br>
                                        {{ $booking->created_at->format('H:i') }}
                                    </div>
                                    @if ($booking->ghi_chu_noi_bo)
                                        <div
                                            class="mt-1 text-[10px] bg-yellow-900/20 text-yellow-500 px-1 py-0.5 rounded border border-yellow-900/30 inline-block">
                                            <i class="fas fa-sticky-note mr-1"></i>Note
                                        </div>
                                    @endif
                                </td>

                                {{-- Cột 2: Khách hàng --}}
                                <td class="px-4 py-3 align-top">
                                    @if ($booking->nguoiDung)
                                        <div class="font-medium text-gray-200">{{ $booking->nguoiDung->ho_ten }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            <i
                                                class="fas fa-envelope text-[10px] mr-1 w-3"></i>{{ $booking->nguoiDung->email }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            <i
                                                class="fas fa-phone text-[10px] mr-1 w-3"></i>{{ $booking->nguoiDung->sdt ?? '---' }}
                                        </div>
                                    @else
                                        <span class="text-gray-500 italic">Khách vãng lai</span>
                                    @endif

                                // Kiểm tra vé hết hạn suất chiếu
                                $isExpired = $booking->suatChieu?->thoi_gian_bat_dau < now();
                                // Vé có thể chỉnh sửa
                                $isEditable = $booking->trang_thai != 2 && !$isExpired;
                            @endphp
                            <tr class="hover:bg-[#1b1e28]/70 transition">
                                <td class="px-4 py-3 font-medium">#{{ $booking->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-200">{{ $booking->nguoiDung->ho_ten ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $booking->nguoiDung->email ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-200">{{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $booking->suatChieu?->thoi_gian_bat_dau?->format('d/m/Y H:i') ?? 'N/A' }}
                                        • {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $seatLabels = $booking->chiTietDatVe
                                            ->map(fn($d) => optional($d->ghe)->so_ghe)
                                            ->filter()
                                            ->implode(', ');
                                        $comboLabels = $booking->chiTietCombo
                                            ->map(function ($c) {
                                                $name = $c->combo->ten ?? '—';
                                                $qty = $c->so_luong > 1 ? ' × ' . $c->so_luong : ' × 1';
                                                return $name . $qty;
                                            })
                                            ->filter()
                                            ->implode(', ');
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
                                            <span class="px-2 py-1 text-yellow-400 bg-yellow-900/30 rounded-full text-xs">Chờ thanh toán</span>
                                            @break
                                        @case(1)
                                            @php
                                                $pt = $booking->phuong_thuc_thanh_toan;
                                                if (!$pt) {
                                                    $map = optional($booking->thanhToan)->phuong_thuc;
                                                    $pt = $map === 'online' ? 1 : ($map === 'offline' ? 2 : null);
                                                }
                                                $pt = $pt ? (int)$pt : 2;
                                            @endphp
                                            @if($pt === 1)
                                                <span class="px-2 py-1 text-green-400 bg-green-900/30 rounded-full text-xs">Đã thanh toán</span>
                                            @else
                                                <span class="px-2 py-1 text-blue-400 bg-blue-900/30 rounded-full text-xs">Đã xác nhận</span>
                                            @endif
                                            @break
                                        @case(3)
                                            <span class="px-2 py-1 text-orange-300 bg-orange-900/30 rounded-full text-xs">Yêu cầu hủy</span>
                                            @break
                                        @case(2)
                                            <span class="px-2 py-1 text-red-400 bg-red-900/30 rounded-full text-xs">Đã hủy</span>
                                            @break
                                        @default
                                            <span class="px-2 py-1 text-gray-400 bg-gray-800 rounded-full text-xs">Không xác định</span>
                                            {{-- LOGIC: Đếm ngược 5 phút cho vé Tiền mặt --}}
                                            @php
                                                $isCash =
                                                    optional($booking->thanhToan)->phuong_thuc == 'Tiền mặt' ||
                                                    empty($booking->thanhToan);
                                                $expireTime = $booking->created_at->addMinutes(1    );
                                                $isOver = now()->greaterThan($expireTime);
                                            @endphp

                                            <div class="flex flex-col gap-1">
                                                <span class="px-2 py-1 text-yellow-400 bg-yellow-900/30 rounded-full text-xs w-fit">
                                                    Chờ xác nhận
                                                </span>
                                                @if ($isCash && !$isOver)
                                                    <span class="text-xs font-bold text-red-400 countdown-timer"
                                                        data-expire="{{ $expireTime->format('Y-m-d H:i:s') }}">
                                                        Đang tính giờ...
                                                    </span>
                                                @elseif($isCash && $isOver)
                                                    <span class="text-xs text-gray-500 italic">Đang hủy...</span>
                                                @endif
                                            </div>
                                        @break

                                        @case(1)
                                            @if ($isExpired)
                                                <span class="px-2 py-1 text-gray-400 bg-gray-800/50 rounded-full text-xs">Đã hết
                                                    hạn</span>
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

                                {{-- Cột 3: Phim --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="font-medium text-blue-300 mb-1 max-w-[200px] truncate"
                                        title="{{ $booking->suatChieu?->phim?->ten_phim }}">
                                        {{ $booking->suatChieu?->phim?->ten_phim ?? 'Phim đã xóa' }}
                                    </div>
                                    <div class="text-xs text-gray-400 flex items-center gap-1">
                                        <i class="far fa-clock"></i>
                                        {{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('H:i d/m/Y') ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        <i class="fas fa-door-open text-[10px]"></i>
                                        {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'Phòng ?' }}
                                    </div>
                                </td>
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- 1. Nút XÁC NHẬN --}}
                                        @if ($booking->trang_thai == 0)
                                            <form action="{{ route('admin.bookings.update', $booking->id) }}"
                                                method="POST">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="trang_thai" value="1">
                                                <button type="submit" title="Xác nhận vé"
                                                    class="p-2 rounded-lg hover:bg-gray-700/50 transition-colors duration-200 group">
                                                    <i class="fas fa-check text-green-500 group-hover:text-green-400"></i>
                                                </button>
                                            </form>


                                {{-- Cột 4: Chi tiết --}}
                                <td class="px-4 py-3 align-top max-w-[250px]">
                                    <div class="mb-2">
                                        @if ($booking->chiTietDatVe->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($booking->chiTietDatVe as $detail)
                                                    @php
                                                        $loaiGhe = $detail->ghe->loaiGhe->ten_loai ?? '';
                                                        $isVip = stripos($loaiGhe, 'vip') !== false;
                                                        $isCouple =
                                                            stripos($loaiGhe, 'đôi') !== false ||
                                                            stripos($loaiGhe, 'couple') !== false;
                                                        $badgeColor = 'bg-gray-700 text-gray-300';
                                                        if ($isVip) {
                                                            $badgeColor =
                                                                'bg-yellow-900/40 text-yellow-400 border border-yellow-700/50';
                                                        }
                                                        if ($isCouple) {
                                                            $badgeColor =
                                                                'bg-pink-900/40 text-pink-400 border border-pink-700/50';
                                                        }
                                                    @endphp
                                                    <span class="text-[11px] px-1.5 py-0.5 rounded {{ $badgeColor }}"
                                                        title="{{ $loaiGhe }}">
                                                        {{ $detail->ghe->so_ghe ?? '?' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 italic">Không có ghế</span>
                                        @endif
                                    </div>
                                    @if ($booking->chiTietCombo->count() > 0)
                                        <div class="border-t border-gray-700/50 pt-1 mt-1">
                                            @foreach ($booking->chiTietCombo as $detail)
                                                <div class="text-xs text-gray-400">
                                                    + {{ $detail->combo->ten ?? 'Combo cũ' }}
                                                    <span class="text-white">x{{ $detail->so_luong }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                {{-- Cột 5: Thanh toán --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="font-bold text-green-400 whitespace-nowrap">
                                        {{ number_format($booking->tong_tien > 0 ? $booking->tong_tien : $booking->thanhToan->so_tien ?? 0) }}
                                        đ
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ $booking->thanhToan->phuong_thuc ?? 'Chưa chọn TT' }}
                                    </div>
                                    <div class="mt-1">
                                        @if (optional($booking->thanhToan)->trang_thai === 1)
                                            <span class="text-[10px] text-green-500 flex items-center gap-1">
                                                <i class="fas fa-check-circle"></i> Đã thanh toán
                                            </span>
                                        @else
                                            <span class="text-[10px] text-yellow-500 flex items-center gap-1">
                                                <i class="fas fa-hourglass-half"></i> Chờ thanh toán
                                            </span>
                                        @endif
                                    </div>
                                    @if (!empty($booking->thanhToan->ma_giao_dich))
                                        <div class="text-[10px] text-gray-500 mt-1 font-mono bg-gray-800 px-1 rounded inline-block"
                                            title="Mã giao dịch">
                                            {{ Str::limit($booking->thanhToan->ma_giao_dich, 10) }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Cột 6: Trạng thái Vé --}}
                                <td class="px-4 py-3 align-top text-center">
                                    @switch($booking->trang_thai)
                                        @case(0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-700/30">
                                                Chờ xác nhận
                                            </span>
                                            @php
                                                $expireTime =
                                                    $booking->expires_at ?? $booking->created_at->addMinutes(15);
                                                $isFuture = now()->lessThan($expireTime);
                                                // Kiểm tra khách vãng lai (id_nguoi_dung == null)
                                                $isGuest = is_null($booking->id_nguoi_dung);
                                            @endphp
                                            @if ($isFuture)
                                                {{-- DATA ATTRIBUTES QUAN TRỌNG CHO JS --}}
                                                <div class="text-[11px] font-bold text-red-400 mt-1 countdown-timer"
                                                    data-id="{{ $booking->id }}" data-guest="{{ $isGuest ? 'true' : 'false' }}"
                                                    data-expire="{{ $expireTime->format('Y-m-d H:i:s') }}">
                                                    Hủy sau: {{ $expireTime->format('H:i') }}
                                                </div>
                                            @else
                                                <div class="text-[10px] text-gray-500 mt-1 italic">
                                                    Đang xử lý...
                                                </div>
                                            @endif
                                        @break

                                        @case(1)
                                            @if ($isExpired)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">Đã
                                                    hết hạn</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900/30 text-green-400 border border-green-700/30">Đã
                                                    xác nhận</span>
                                            @endif
                                        @break

                                        @case(3)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-900/30 text-orange-400 border border-orange-700/30">Yêu
                                                cầu hủy</span>
                                        @break

                                        @case(2)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900/30 text-red-400 border border-red-700/30">Đã
                                                hủy</span>
                                        @break
                                    @endswitch
                                </td>

                                {{-- Cột 7: Hành động --}}
                                <td
                                    class="px-4 py-3 align-middle text-center sticky right-0 bg-[#1b1e28] group-hover:bg-[#232732] transition-colors border-l border-[#262833]">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-500/10 text-blue-500 hover:bg-blue-500 hover:text-white transition"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($booking->trang_thai == 0)
                                            <form action="{{ route('admin.bookings.update', $booking->id) }}"
                                                method="POST" class="inline">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="trang_thai" value="1">
                                                <button type="submit"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-500/10 text-green-500 hover:bg-green-500 hover:text-white transition"
                                                    title="Xác nhận">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}"
                                                method="POST" onsubmit="return confirm('Hủy vé này?');" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition"
                                                    title="Hủy vé">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if ($booking->trang_thai == 3)
                                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}"
                                                method="POST" onsubmit="return confirm('Chấp nhận hủy?');"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-orange-500/10 text-orange-500 hover:bg-orange-500 hover:text-white transition animate-pulse"
                                                    title="Duyệt hủy">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @auth
                                            @if (optional(auth()->user()->vaiTro)->ten === 'admin' && $isEditable)
                                                <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-yellow-500/10 text-yellow-500 hover:bg-yellow-500 hover:text-white transition"
                                                    title="Sửa vé">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                            {{-- 2. Nút HỦY NHANH --}}
                                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Bạn chắc chắn muốn HỦY vé này không?');">
                                                @csrf
                                                <button type="submit" title="Hủy vé"
                                                    class="p-2 rounded-lg hover:bg-gray-700/50 transition-colors duration-200 group">
                                                    <i class="fas fa-times text-red-500 group-hover:text-red-400"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- 3. Nút CHẤP NHẬN HỦY --}}
                                        @if ($booking->trang_thai == 3)
                                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Xác nhận chấp nhận hủy vé này? Ghế sẽ được mở bán lại.');">
                                                @csrf
                                                <button type="submit" title="Chấp nhận hủy"
                                                    class="p-2 rounded-lg hover:bg-gray-700/50 transition-colors duration-200 group">
                                                    <i class="fas fa-check text-red-500 group-hover:text-red-400"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- 4. Nút XEM --}}
                                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                            class="p-2 rounded-lg hover:bg-gray-700/50 transition-colors duration-200 group"
                                            title="Xem vé">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-blue-400"></i>
                                        </a>

                                        {{-- 5. Nút SỬA --}}
                                        @auth
                                            @if (optional(auth()->user()->vaiTro)->ten === 'admin' && $isEditable)
                                                <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                                    class="p-2 rounded-lg hover:bg-gray-700/50 transition-colors duration-200 group"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit text-yellow-500 group-hover:text-yellow-400"></i>
                                                </a>
                                            @else
                                                <span class="p-2 rounded-lg cursor-not-allowed opacity-50"
                                                    title="Không thể sửa vé đã hủy hoặc hết hạn">
                                                    <i class="fas fa-edit text-gray-500"></i>
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

            <div class="mt-6 px-2">
                {{ $bookings->links('pagination::tailwind') }}


            <div class="mt-6">
                {{ $bookings->links('pagination.custom') }}

            </div>
        @endif
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateCountdowns() {
                const timers = document.querySelectorAll('.countdown-timer');
                const now = new Date().getTime();

                timers.forEach(timer => {
                    const expireString = timer.getAttribute('data-expire');

                    const isGuest = timer.getAttribute('data-guest') === 'true';
                    const bookingId = timer.getAttribute('data-id');


                    const expireDate = new Date(expireString).getTime();
                    const distance = expireDate - now;

                    if (distance > 0) {
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        timer.innerHTML = `Hủy sau: ${minutes}:${seconds < 10 ? '0'+seconds : seconds}`;
                    } else {
                        // LOGIC KHI HẾT GIỜ
                        if (isGuest) {
                            // Nếu là khách vãng lai -> Xóa luôn dòng (Hiệu ứng mờ dần)
                            const row = document.getElementById('row-' + bookingId);
                            if (row) {
                                row.style.transition = "opacity 0.5s ease";
                                row.style.opacity = "0";
                                setTimeout(() => row.remove(), 500);
                            }
                        } else {
                            // Nếu là thành viên -> Reload trang để cập nhật trạng thái "Đã hủy"
                            timer.innerHTML = "Đang xử lý...";
                            timer.className = "text-[10px] text-gray-500 mt-1 italic";
                            if (!timer.dataset.reloading) {
                                timer.dataset.reloading = "true";
                                setTimeout(() => location.reload(), 2000);
                            }

                        timer.innerHTML = `Hủy sau: ${minutes}p ${seconds}s`;
                    } else {
                        timer.innerHTML = "Đang xử lý hủy...";
                        timer.classList.remove('text-red-400');
                        timer.classList.add('text-gray-500');

                        // Reload trang sau 2 giây để Controller xử lý hủy
                        if (!timer.dataset.reloading) {
                            timer.dataset.reloading = "true";
                            setTimeout(() => location.reload(), 2000);

                        }
                    }
                });
            }
            setInterval(updateCountdowns, 1000);
            updateCountdowns();
        });
    </script>
@endsection
