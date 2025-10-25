@extends('admin.layout')

@section('title', 'Chi tiết Đặt Vé #' . $booking->id)

@section('content')
    <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
        <h2 class="text-xl font-semibold mb-4">🎟️ Chi tiết Đặt Vé #{{ $booking->id }}</h2>

        <div class="space-y-3 text-sm text-gray-300">
            <p><strong>Người dùng:</strong> {{ $booking->nguoiDung->ho_ten ?? 'N/A' }}</p>
            <p><strong>Phim:</strong> {{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }}</p>
            <p><strong>Phòng chiếu:</strong> {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}</p>
            <p><strong>Thời gian chiếu:</strong>
                {{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('d/m/Y H:i') }}</p>
            <p><strong>Trạng thái:</strong>
                @switch($booking->trang_thai)
                    @case(0)
                        <span class="text-yellow-400">Chờ xác nhận</span>
                    @break

                    @case(1)
                        <span class="text-green-400">Đã xác nhận</span>
                    @break

                    @case(2)
                        <span class="text-red-400">Đã hủy</span>
                    @break

                    @default
                        <span class="text-gray-400">Không xác định</span>
                @endswitch
            </p>
            <p><strong>Phương thức thanh toán:</strong> {{ $booking->thanhToan?->phuong_thuc ?? 'N/A' }}</p>
            <p><strong>Tổng Tiền:</strong> {{ number_format($booking->tong_tien ?? 0) }} VND</p>
            @if ($booking->nguoiDung?->diemThanhVien)
                <p><strong>Điểm tích lũy:</strong> {{ $booking->nguoiDung->diemThanhVien->tong_diem }} điểm</p>
                <p><strong>Ngày hết hạn điểm:</strong>
                    {{ \Carbon\Carbon::parse($booking->nguoiDung->diemThanhVien->ngay_het_han)->format('d/m/Y') }}
                </p>
            @else
                <p class="text-gray-400">Người dùng chưa có thông tin điểm tích lũy.</p>
            @endif

        </div>

        <hr class="my-4 border-[#262833]">

        <h3 class="font-semibold mb-2 text-lg">💺 Danh sách ghế đã đặt</h3>
        @if ($booking->chiTietDatVe->isEmpty())
            <p class="text-gray-400">Không có ghế nào được đặt.</p>
        @else
            <ul class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach ($booking->chiTietDatVe as $detail)
                    <li class="bg-[#1d202a] px-3 py-2 rounded border border-[#262833] text-sm text-center">
                        {{ $detail->ghe->id_loai ?? 'N/A' }}
                        <span class="block text-xs text-gray-400">{{ $detail->ghe->loaiGhe->ten_loai ?? '' }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        <hr class="my-4 border-[#262833]">

        <h3 class="font-semibold mb-2 text-lg">🍿 Combo đi kèm</h3>
        @if ($booking->chiTietCombo->isEmpty())
            <p class="text-gray-400">Không có combo.</p>
        @else
            <ul class="list-disc pl-6 text-gray-300">
                @foreach ($booking->chiTietCombo as $combo)
                    <li>{{ $combo->combo->ten ?? 'N/A' }} × {{ $combo->so_luong }}</li>
                @endforeach
            </ul>
        @endif

        <div class="mt-6">
            <a href="{{ route('admin.bookings.index') }}"
                class="bg-[#F53003] px-4 py-2 rounded text-sm hover:bg-[#d92903]">
                ← Quay lại danh sách
            </a>
        </div>
    </div>
@endsection
