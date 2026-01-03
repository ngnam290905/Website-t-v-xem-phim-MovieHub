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
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-3">
            <div class="text-xs text-[#a6a6b0]">Doanh thu hôm nay</div>
            <div class="text-xl font-bold text-blue-400 mt-1 truncate" title="{{ number_format($revenueToday ?? 0) }} VNĐ">
                {{ number_format($revenueToday ?? 0) }} ₫
            </div>
        </div>
    </div>

    {{-- 3. Card chính (Lọc + Bảng) --}}

    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-ticket-alt text-blue-500"></i> Danh sách Đặt Vé
            </h2>
            <a href="{{ route('admin.bookings.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                <i class="fas fa-plus"></i>
                <span>Đặt vé mới</span>
            </a>
            {{-- FORM TÌM KIẾM ĐÃ TỐI ƯU --}}
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3">

                    

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
                            
                            <th class="px-4 py-3 text-center sticky right-0 bg-[#1b1e28] z-20">Hành động</th>

                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#262833] bg-[#151822]">
                        @foreach ($bookings as $booking)
                            @php
                                $isExpired = optional($booking->suatChieu)->thoi_gian_bat_dau < now();
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
                                </td>

                                {{-- Cột 3: Phim & Suất chiếu --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="font-medium text-blue-300 mb-1 max-w-[220px] truncate"
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

                                {{-- Cột 4: Chi tiết đặt chỗ --}}
                                <td class="px-4 py-3 align-top max-w-[260px]">
                                    <div class="mb-2">
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
                                                    <span class="text-[11px] px-1.5 py-0.5 rounded {{ $badgeColor }}" title="{{ $loaiGhe }}">
                                                        {{ $detail->ghe->so_ghe ?? '?' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 italic">Không có ghế</span>
                                        @endif
                                    </div>

                                    @if ($booking->chiTietCombo && $booking->chiTietCombo->count() > 0)
                                        <div class="border-t border-gray-700/50 pt-1 mt-1">
                                            @foreach ($booking->chiTietCombo as $detail)
                                                <div class="text-xs text-gray-400 truncate" title="{{ $detail->combo->ten ?? 'Combo' }} x{{ $detail->so_luong }}">
                                                    + {{ $detail->combo->ten ?? 'Combo' }} <span class="text-white">x{{ $detail->so_luong }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                {{-- Cột 5: Thanh toán --}}
                                <td class="px-4 py-3 align-top">
                                    @php
                                        // Tính tổng tiền để hiển thị: ưu tiên thanhToan->so_tien, sau đó tong_tien, cuối cùng là tính toán
                                        $comboItems = $booking->chiTietCombo ?? collect();
                                        $promo = $booking->khuyenMai ?? null;
                                        $comboTotal = $comboItems->sum(function($i){ return (float)$i->gia_ap_dung * max(1, (int)$i->so_luong); });
                                        $seatTotal = (float) ($booking->chiTietDatVe ? $booking->chiTietDatVe->sum('gia') : 0);
                                        $subtotal = $seatTotal + $comboTotal;
                                        $promoDiscount = 0;
                                        if ($promo) {
                                            $type = strtolower($promo->loai_giam);
                                            $val  = (float)($promo->gia_tri_giam);
                                            if ($type === 'phantram') { $promoDiscount = round($subtotal * ($val/100)); }
                                            else { $promoDiscount = ($val >= 1000) ? $val : $val * 1000; }
                                        }
                                        $calculated = max(0, $subtotal - $promoDiscount);
                                        $paidTotal = optional($booking->thanhToan)->so_tien;
                                        $storedTotal = $booking->tong_tien ?? null;
                                        $displayTotal = is_numeric($paidTotal) && $paidTotal > 0
                                            ? (float)$paidTotal
                                            : (is_numeric($storedTotal) && $storedTotal > 0 ? (float)$storedTotal : (float)$calculated);
                                    @endphp
                                    <div class="font-bold text-green-400 whitespace-nowrap">
                                        {{ number_format($displayTotal, 0) }} đ
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

                                

                                {{-- Cột 7: Hành động --}}
                                <td
                                    class="px-4 py-3 align-middle text-center sticky right-0 bg-[#1b1e28] group-hover:bg-[#232732] transition-colors border-l border-[#262833] z-10 relative">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-500/10 text-blue-500 hover:bg-blue-500 hover:text-white transition"
                                           title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Chỉ xem thông tin, không có nút xác nhận --}}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 px-2">
                {{ $bookings->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

    
@endsection
