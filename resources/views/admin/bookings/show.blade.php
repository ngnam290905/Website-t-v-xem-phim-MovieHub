@extends('admin.layout')

@section('title', 'Chi tiết Đặt Vé #' . $booking->id)

@section('content')
    @php
        $seatTotal = (float) $booking->chiTietDatVe->sum('gia');
        $comboTotal = (float) ($booking->chiTietCombo->sum(function($i){ return ($i->gia_ap_dung ?? 0) * max(1, (int)$i->so_luong); }) ?? 0);
        $discount = 0;
        if ($booking->khuyenMai) {
            $type = strtolower($booking->khuyenMai->loai_giam);
            $val  = (float) $booking->khuyenMai->gia_tri_giam;
            $base = $seatTotal + $comboTotal;
            if ($type === 'phantram') $discount = round($base * ($val/100));
            else $discount = ($val >= 1000) ? $val : $val * 1000;
            if ($discount > $base) $discount = $base;
        }
        $total = $booking->tong_tien ?? max(0, $seatTotal + $comboTotal - $discount);
    @endphp
    <div class="space-y-6">
        <!-- Header + Status -->
        <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
            <div class="flex items-start justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-white">🎟️ Chi tiết Đặt Vé #{{ $booking->id }}</h2>
                    <p class="text-sm text-gray-400 mt-1">{{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }} • {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-400">{{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @switch($booking->trang_thai)
                        @case(0)
                            <span class="px-3 py-1 rounded-full text-xs bg-yellow-500/20 text-yellow-300">Chờ xác nhận</span>
                        @break
                        @case(1)
                            <span class="px-3 py-1 rounded-full text-xs bg-green-500/20 text-green-300">Đã xác nhận</span>
                        @break
                        @case(3)
                            <span class="px-3 py-1 rounded-full text-xs bg-orange-500/20 text-orange-300">Yêu cầu hủy</span>
                        @break
                        @case(2)
                            <span class="px-3 py-1 rounded-full text-xs bg-red-500/20 text-red-300">Đã hủy</span>
                        @break
                        @default
                            <span class="px-3 py-1 rounded-full text-xs bg-gray-500/20 text-gray-300">Không xác định</span>
                    @endswitch
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                <div class="text-sm text-gray-400">Khách hàng</div>
                <div class="mt-2 text-white font-semibold">{{ $booking->nguoiDung->ho_ten ?? 'Khách vãng lai' }}</div>
                <div class="text-xs text-gray-500">{{ $booking->nguoiDung->email ?? '—' }}</div>
            </div>
            <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                <div class="text-sm text-gray-400">Thanh toán</div>
                <div class="mt-2 text-white font-semibold">{{ $booking->thanhToan?->phuong_thuc ?? '—' }}</div>
                <div class="text-xs text-gray-500">Mã KM: {{ $booking->khuyenMai?->ma_km ?? '—' }}</div>
            </div>
            <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-400">Tổng tiền</div>
                    <div class="text-xs text-gray-500">(ghế + combo − KM)</div>
                </div>
                <div class="mt-2 text-2xl font-bold text-[#F53003]">{{ number_format($total, 0) }}đ</div>
            </div>
        </div>

        <!-- Seats & Combos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                    <h3 class="font-semibold mb-3 text-white">💺 Ghế đã đặt</h3>
                    @if ($booking->chiTietDatVe->isEmpty())
                        <p class="text-gray-400">Không có ghế nào được đặt.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach ($booking->chiTietDatVe as $detail)
                                <div class="bg-[#1d202a] px-3 py-2 rounded border border-[#262833] text-sm text-center">
                                    <span class="text-white font-medium">{{ optional($detail->ghe)->so_ghe ?? '—' }}</span>
                                    <span class="block text-xs text-gray-400">{{ optional($detail->ghe->loaiGhe)->ten_loai ?? 'Ghế' }}</span>
                                    <span class="block text-xs text-gray-300 mt-1">{{ number_format($detail->gia ?? 0, 0) }}đ</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                    <h3 class="font-semibold mb-3 text-white">🍿 Combo đi kèm</h3>
                    @if ($booking->chiTietCombo->isEmpty())
                        <p class="text-gray-400">Không có combo.</p>
                    @else
                        <ul class="divide-y divide-[#262833]">
                            @foreach ($booking->chiTietCombo as $combo)
                                <li class="py-2 flex items-center justify-between text-sm">
                                    <div class="text-gray-300">{{ $combo->combo->ten ?? 'Combo' }} × {{ max(1,(int)$combo->so_luong) }}</div>
                                    <div class="text-white">{{ number_format($combo->gia_ap_dung ?? 0, 0) }}đ</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Breakdown -->
            <div class="space-y-6">
                <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                    <h3 class="font-semibold mb-3 text-white">🧮 Chi tiết thanh toán</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between text-gray-300">
                            <span>Tiền ghế</span>
                            <span>{{ number_format($seatTotal, 0) }}đ</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-300">
                            <span>Combo</span>
                            <span>{{ number_format($comboTotal, 0) }}đ</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-300">
                            <span>Khuyến mãi {{ $booking->khuyenMai?->ma_km ? '(' . $booking->khuyenMai->ma_km . ')' : '' }}</span>
                            <span class="text-red-400">-{{ number_format($discount, 0) }}đ</span>
                        </div>
                        <div class="border-t border-[#262833] my-2"></div>
                        <div class="flex items-center justify-between text-white font-semibold">
                            <span>Tổng cộng</span>
                            <span>{{ number_format($total, 0) }}đ</span>
                        </div>
                    </div>
                </div>

                <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                    <h3 class="font-semibold mb-3 text-white">👤 Thông tin khách</h3>
                    <div class="text-sm text-gray-300 space-y-1">
                        <p>Họ tên: <span class="text-white">{{ $booking->nguoiDung->ho_ten ?? '—' }}</span></p>
                        <p>Email: <span class="text-white">{{ $booking->nguoiDung->email ?? '—' }}</span></p>
                        <p>SĐT: <span class="text-white">{{ $booking->nguoiDung->sdt ?? '—' }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-2">
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 bg-[#F53003] px-4 py-2 rounded text-sm hover:bg-[#d92903]">
                ← Quay lại danh sách
            </a>
        </div>
    </div>
@endsection
