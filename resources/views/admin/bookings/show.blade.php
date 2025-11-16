@extends('admin.layout')

@section('title', 'Chi tiết Đặt Vé #' . $booking->id)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- CỘT TRÁI (THÔNG TIN CHÍNH) --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-semibold">🎟️ Chi tiết Đặt Vé #{{ $booking->id }}</h2>
                        <p class="text-sm text-gray-400">
                            Đặt lúc: {{ $booking->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-medium">Trạng thái:</span>
                        @switch($booking->trang_thai)
                            @case(0)
                                <p class="font-semibold text-yellow-400">Chờ xác nhận</p>
                            @break
                            @case(1)
                                <p class="font-semibold text-green-400">Đã xác nhận</p>
                            @break
                            @case(3)
                                <p class="font-semibold text-orange-300">Yêu cầu hủy</p>
                            @break
                            @case(2)
                                <p class="font-semibold text-red-400">Đã hủy</p>
                            @break
                            @default
                                <p class="font-semibold text-gray-400">Không xác định</p>
                        @endswitch
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-300">
                    <p><strong>Phim:</strong> {{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }}</p>
                    <p><strong>Phòng chiếu:</strong> {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}</p>
                    <p><strong>Suất chiếu:</strong> {{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('d/m/Y H:i') }}</p>
                    <p><strong>Kết thúc:</strong> {{ optional($booking->suatChieu?->thoi_gian_ket_thuc)->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
                <h3 class="font-semibold mb-4 text-lg">💺 Danh sách ghế ({{ $booking->chiTietDatVe->count() }} ghế)</h3>
                @if ($booking->chiTietDatVe->isEmpty())
                    <p class="text-gray-400">Không có ghế nào được đặt.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-300">
                            <thead class="text-xs text-gray-400 uppercase bg-[#1d202a]">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Số ghế</th>
                                    <th scope="col" class="px-4 py-3">Loại ghế</th>
                                    <th scope="col" class="px-4 py-3">Giá vé</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($booking->chiTietDatVe as $detail)
                                    <tr class="border-b border-[#262833] hover:bg-[#1d202a]">
                                        <td class="px-4 py-3 font-medium">{{ $detail->ghe?->so_ghe ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $detail->ghe?->loaiGhe?->ten_loai ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ number_format($detail->gia ?? 0) }} VND</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
                <h3 class="font-semibold mb-4 text-lg">🍿 Combo đi kèm</h3>
                @if ($booking->chiTietCombo->isEmpty())
                    <p class="text-gray-400">Không có combo.</p>
                @else
                     <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-300">
                            <thead class="text-xs text-gray-400 uppercase bg-[#1d202a]">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Tên Combo</th>
                                    <th scope="col" class="px-4 py-3">Số lượng</th>
                                    <th scope="col" class="px-4 py-3">Đơn giá</th>
                                    <th scope="col" class="px-4 py-3">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $tongTienCombo = 0; @endphp
                                @foreach ($booking->chiTietCombo as $combo)
                                    @php
                                        $gia = $combo->gia_ap_dung ?? 0;
                                        $soLuong = $combo->so_luong ?? 0;
                                        $tong = $gia * $soLuong;
                                        $tongTienCombo += $tong;
                                    @endphp
                                    <tr class="border-b border-[#262833] hover:bg-[#1d202a]">
                                        <td class="px-4 py-3 font-medium">{{ $combo->combo?->ten ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $soLuong }}</td>
                                        <td class="px-4 py-3">{{ number_format($gia) }} VND</td>
                                        <td class="px-4 py-3">{{ number_format($tong) }} VND</td>
                                    </tr>
                                @endforeach
                                <tr class="font-semibold text-white">
                                    <td colspan="3" class="px-4 py-3 text-right">Tổng tiền Combo:</td>
                                    <td class="px-4 py-3">{{ number_format($tongTienCombo) }} VND</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        {{-- CỘT PHẢI (THAO TÁC & THÔNG TIN PHỤ) --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
                <h3 class="text-lg font-semibold mb-4">👤 Thông tin người dùng</h3>
                <div class="space-y-3 text-sm text-gray-300">
                    <p><strong>Họ tên:</strong> {{ $booking->nguoiDung->ho_ten ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $booking->nguoiDung->email ?? 'N/A' }}</p>
                    <p><strong>SĐT:</strong> {{ $booking->nguoiDung->sdt ?? 'N/A' }}</p>
                    
                    <hr class="my-2 border-[#262833]">
                    
                    {{-- 
                        Lưu ý: Để $booking->nguoiDung->hangThanhVien hoạt động, 
                        bạn cần đảm bảo đã eager load 'nguoiDung.hangThanhVien' trong Controller
                        hoặc Model NguoiDung có relationship 'hangThanhVien'
                    --}}
                    @if ($booking->nguoiDung)
                        <p><strong>Điểm tích lũy:</strong> {{ $booking->nguoiDung->diemThanhVien?->tong_diem ?? 0 }} điểm</p>
                        
                        <p><strong>Hạng thành viên:</strong> 
                            @if ($booking->nguoiDung->hangThanhVien)
                                <span class="font-medium text-yellow-400">{{ $booking->nguoiDung->hangThanhVien->ten_hang }}</span>
                            @else
                                <span class="text-gray-400">Chưa có hạng</span>
                            @endif
                        </p>
                        
                        @if ($booking->nguoiDung->diemThanhVien)
                            <p><strong>Ngày hết hạn:</strong>
                                {{ \Carbon\Carbon::parse($booking->nguoiDung->diemThanhVien->ngay_het_han)->format('d/m/Y') }}
                            </p>
                        @endif
                    @else
                        <p class="text-gray-400">Người dùng này chưa có điểm thành viên.</p>
                    @endif
                </div>
            </div>

            <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
                <h3 class="text-lg font-semibold mb-4">💳 Thanh toán & Khuyến mãi</h3>
                <div class="space-y-3 text-sm text-gray-300">

                    <p><strong>Tổng giá trị vé:</strong> 
                        <span class="text-xl font-bold text-green-400">{{ number_format($booking->tong_tien_hien_thi) }} VND</span>
                    </p>
                    
                    <p><strong>Số tiền đã T.Toán:</strong> 
                        <span class="font-medium text-gray-200">{{ number_format($booking->thanhToan?->so_tien ?? 0) }} VND</span>
                    </p>

                    <p><strong>P.Thức T.Toán:</strong> {{ $booking->thanhToan?->phuong_thuc ?? 'Chưa thanh toán' }}</p>
                    <p><strong>Trạng thái T.Toán:</strong>
                        @if(optional($booking->thanhToan)->trang_thai === 1)
                            <span class="font-medium text-green-400">Thành công</span>
                        @else
                            <span class="font-medium text-yellow-400">Chưa hoàn tất / Lỗi</span>
                        @endif
                    </p>
                    <p><strong>Mã Giao Dịch:</strong> {{ $booking->thanhToan?->ma_giao_dich ?? '—' }}</p>
                    <p><strong>Thời gian T.Toán:</strong> {{ optional($booking->thanhToan?->thoi_gian)->format('d/m/Y H:i') ?? '—' }}</p>

                    <hr class="my-2 border-[#262833]">

                    <p><strong>Khuyến mãi:</strong> {{ $booking->khuyenMai?->ma_km ?? 'Không áp dụng' }}</p>
                    @if ($booking->khuyenMai)
                        <p><strong>Giá trị giảm:</strong>
                            @if ($booking->khuyenMai->loai_giam == 'phantram')
                                {{ $booking->khuyenMai->gia_tri_giam }}%
                            @else
                                {{ number_format($booking->khuyenMai->gia_tri_giam) }} VND
                            @endif
                        </p>
                    @endif
                </div>
            </div>
            <div class="mt-6">
                <a href="{{ route('admin.bookings.index') }}"
                    class="inline-block w-full text-center bg-[#F53003] px-4 py-2 rounded text-sm hover:bg-[#d92903] transition-colors">
                    ← Quay lại danh sách
                </a>
            </div>

        </div>
    </div>
@endsection