@section('content')
    <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Chi tiết Đặt Vé #{{ $booking->id }}</h1>
                <p class="text-sm text-gray-400">Thông tin chi tiết về đặt vé</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.bookings.index') }}" class="px-3 py-2 rounded bg-gray-700 text-white">Quay lại</a>
                <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="px-3 py-2 bg-blue-600 text-white rounded">Chỉnh sửa</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="col-span-2">
                <h2 class="text-lg font-semibold mb-3">Thông tin Khách hàng</h2>
                <div class="bg-[#1d202a] border border-[#262833] rounded p-4">
                    <p class="text-gray-300"><strong>Tên:</strong> {{ $booking->ten }}</p>
                    <p class="text-gray-300"><strong>Email:</strong> {{ $booking->email }}</p>
                    <p class="text-gray-300"><strong>Phone:</strong> {{ $booking->so_dien_thoai }}</p>
                    <p class="text-gray-300"><strong>Ghế:</strong> {{ implode(', ', $selectedGhe ?? []) }}</p>
                    <p class="text-gray-300"><strong>Suất chiếu:</strong> {{ optional($booking->suatChieu)->ten_suat ?? '-' }}</p>
                </div>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-3">Chi tiết Thanh toán</h2>
                <div class="bg-[#1d202a] border border-[#262833] rounded p-4">
                    <p class="text-gray-300"><strong>Tổng tiền:</strong> {{ number_format($booking->tong_tien_hien_thi ?? $booking->tong_tien, 0, ',', '.') }}đ</p>
                    <p class="text-gray-300"><strong>Trạng thái Thanh toán:</strong>
                        @if ($booking->trang_thai_thanh_toan == 1)
                            <span class="text-green-400">Đã thanh toán</span>
                        @elseif($booking->trang_thai_thanh_toan == 2)
                            <span class="text-yellow-400">Đã hoàn tiền</span>
                        @else
                            <span class="text-red-400">Chưa thanh toán</span>
                        @endif
                    </p>
                    <p class="text-gray-300"><strong>Ngày đặt:</strong> {{ $booking->created_at }}</p>
                    <p class="text-gray-300"><strong>Mã giảm giá:</strong> {{ optional($booking->khuyenMai)->ma_km ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Phòng chiếu</p>
                    <p class="text-white">{{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}</p>
                </div>
            </div>
            
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Thời gian chiếu</p>
                    <p class="text-white font-medium">{{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Thời lượng</p>
                    <p class="text-white">{{ $booking->suatChieu?->phim?->do_dai ?? $booking->suatChieu?->phim?->thoi_luong ?? 'N/A' }} phút</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Status Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-check-circle text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Trạng thái đặt vé</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Trạng thái</p>
                @switch($booking->trang_thai)
                    @case(0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-300">
                            <i class="fas fa-clock mr-1"></i> Chờ xác nhận
                        </span>
                    @break
                    @case(1)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300">
                            <i class="fas fa-check mr-1"></i> Đã xác nhận
                        </span>
                    @break
                    @case(3)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-500/20 text-orange-300">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Yêu cầu hủy
                        </span>
                    @break
                    @case(2)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-300">
                            <i class="fas fa-times mr-1"></i> Đã hủy
                        </span>
                    @break
                    @default
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-400">
                            Không xác định
                        </span>
                @endswitch
            </div>
            
            <div>
                <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Thanh toán</p>
                @switch($booking->trang_thai_thanh_toan ?? 0)
                    @case(0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-400">
                            <i class="fas fa-wallet mr-1"></i> Chưa thanh toán
                        </span>
                    @break
                    @case(1)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300">
                            <i class="fas fa-check mr-1"></i> Đã thanh toán
                        </span>
                    @break
                    @case(2)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300">
                            <i class="fas fa-undo mr-1"></i> Đã hoàn tiền
                        </span>
                    @break
                    @default
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-400">
                            Không xác định
                        </span>
                @endswitch
            </div>
            
            <div>
                <p class="text-xs text-[#a6a6b0] uppercase tracking-wide mb-1">Phương thức thanh toán</p>
                <p class="text-white">{{ $booking->thanhToan?->phuong_thuc ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Seats Information Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-couch text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Ghế đã đặt</h2>
        </div>
        
        @if ($booking->chiTietDatVe->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-couch text-4xl text-[#a6a6b0] mb-3"></i>
                <p class="text-[#a6a6b0]">Không có ghế nào được đặt</p>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach ($booking->chiTietDatVe as $detail)
                    <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-3 text-center hover:border-[#F53003] transition-colors">
                        <div class="text-lg font-bold text-white mb-1">{{ $detail->ghe->id_loai ?? 'N/A' }}</div>
                        <div class="text-xs text-[#a6a6b0]">{{ $detail->ghe->loaiGhe->ten_loai ?? '' }}</div>
                        <div class="text-sm text-green-400 font-medium mt-1">{{ number_format($detail->gia_tien) }}đ</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Combo Information Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-pink-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-popcorn text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Combo đi kèm</h2>
        </div>
        
        @if ($booking->chiTietCombo->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-popcorn text-4xl text-[#a6a6b0] mb-3"></i>
                <p class="text-[#a6a6b0]">Không có combo nào được đặt</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($booking->chiTietCombo as $combo)
                    <div class="flex items-center justify-between bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-pink-600/20 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-popcorn text-pink-400 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">{{ $combo->combo->ten ?? 'N/A' }}</p>
                                <p class="text-xs text-[#a6a6b0]">Số lượng: {{ $combo->so_luong }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-green-400 font-medium">{{ number_format($combo->gia_tien) }}đ</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Pricing Summary Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-calculator text-white"></i>
            </div>
            <h2 class="text-lg font-semibold text-white">Chi tiết thanh toán</h2>
        </div>
        
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-[#a6a6b0]">Tiền ghế:</span>
                <span class="text-white">{{ number_format($booking->chiTietDatVe->sum('gia_tien')) }}đ</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[#a6a6b0]">Tiền combo:</span>
                <span class="text-white">{{ number_format($booking->chiTietCombo->sum('gia_tien')) }}đ</span>
            </div>
            @if ($booking->khuyenMai)
                <div class="flex justify-between items-center">
                    <span class="text-[#a6a6b0]">Mã giảm giá ({{ $booking->khuyenMai->ma_km }}):</span>
                    <span class="text-red-400">-{{ number_format($booking->khuyenMai->gia_tri_giam ?? 0) }}đ</span>
                </div>
            @endif
            <div class="border-t border-[#262833] pt-3">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-white">Tổng cộng:</span>
                    <span class="text-lg font-bold text-green-400">{{ number_format($booking->tong_tien ?? 0) }}đ</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end gap-3">
        @if ($booking->trang_thai == 0)
            <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                    <i class="fas fa-check mr-2"></i> Xác nhận đặt vé
                </button>
            </form>
        @endif
        
        @if (in_array($booking->trang_thai, [0, 1]))
            <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn hủy vé này?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                    <i class="fas fa-times mr-2"></i> Hủy vé
                </button>
            </form>
        @endif
        
        <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
            <i class="fas fa-edit mr-2"></i> Chỉnh sửa
        </a>
    </div>
</div>
@endsection
=======
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
>>>>>>> nguyen
