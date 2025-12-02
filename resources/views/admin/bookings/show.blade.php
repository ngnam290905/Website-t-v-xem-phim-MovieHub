@extends('admin.layout')

@section('title', 'Chi tiết Đặt Vé #' . $booking->id)

@section('content')
    @php
        // Tính toán lại để hiển thị breakdown cho chính xác
        $seatTotal = (float) $booking->chiTietDatVe->sum('gia');
        $comboTotal = (float) ($booking->chiTietCombo->sum(function($i){ 
            return ($i->gia_ap_dung ?? 0) * max(1, (int)$i->so_luong); 
        }) ?? 0);
        
        $discount = 0;
        if ($booking->khuyenMai) {
            $type = strtolower($booking->khuyenMai->loai_giam);
            $val  = (float) $booking->khuyenMai->gia_tri_giam;
            $base = $seatTotal + $comboTotal;
            
            if ($type === 'phantram') {
                $discount = round($base * ($val / 100));
            } else {
                $discount = ($val >= 1000) ? $val : $val * 1000;
            }
            if ($discount > $base) $discount = $base;
        }
        
        // Tổng tiền cuối cùng (ưu tiên số tiền đã thanh toán -> tổng lưu trong booking -> tính toán)
        $base = $seatTotal + $comboTotal;
        $calculated = max(0, $base - $discount);
        $paid = optional($booking->thanhToan)->so_tien;
        $stored = $booking->tong_tien;
        $total = is_numeric($paid) && $paid > 0 ? (float)$paid : (is_numeric($stored) && $stored > 0 ? (float)$stored : (float)$calculated);
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.bookings.index') }}" class="text-gray-400 hover:text-white transition">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-white">Chi tiết Vé #{{ $booking->id }}</h1>
                    
                    @switch($booking->trang_thai)
                        @case(0) <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">Chờ xác nhận</span> @break
                        @case(1) <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">Đã xác nhận</span> @break
                        @case(2) <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400 border border-red-500/30">Đã hủy</span> @break
                        @case(3) <span class="px-3 py-1 rounded-full text-xs font-medium bg-orange-500/20 text-orange-400 border border-orange-500/30">Yêu cầu hủy</span> @break
                    @endswitch
                </div>
                <p class="text-sm text-gray-400 mt-1 ml-8">
                    Ngày tạo: {{ $booking->created_at->format('H:i d/m/Y') }}
                </p>
            </div>

            <div class="flex gap-2">
                @auth
                    @if(in_array(optional(auth()->user()->vaiTro)->ten, ['admin','staff']))
                        {{-- Nút gửi Email (Chỉ hiện khi đã xác nhận) --}}
                        @if($booking->trang_thai == 1)
                            <form action="{{ route('admin.bookings.send-ticket', $booking->id) }}" method="POST" onsubmit="return confirm('Gửi lại email vé cho khách?');">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-blue-600/20 text-blue-400 hover:bg-blue-600/30 border border-blue-600/30 rounded-lg text-sm transition flex items-center gap-2">
                                    <i class="fas fa-envelope"></i> Gửi Email
                                </button>
                            </form>
                        @endif

                        {{-- Nút Duyệt/Hủy (Chỉ hiện khi chưa hoàn tất/hủy) --}}
                        @if($booking->trang_thai == 0 || $booking->trang_thai == 3)
                            <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST">
                                @csrf 
                                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition shadow-lg shadow-green-900/20 flex items-center gap-2">
                                    <i class="fas fa-check"></i> Xác nhận
                                </button>
                            </form>
                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy vé này không?');">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-600/20 text-red-400 hover:bg-red-600/30 border border-red-600/30 rounded-lg text-sm transition flex items-center gap-2">
                                    <i class="fas fa-times"></i> Hủy vé
                                </button>
                            </form>
                        @endif
                    @endif
                @endauth
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
                    <h3 class="text-lg font-semibold text-white mb-4 border-b border-[#262833] pb-3">🎬 Thông tin Phim</h3>
                    <div class="flex gap-4">
                        <img src="{{ $booking->suatChieu?->phim?->poster ?? 'https://via.placeholder.com/150x225' }}" 
                             alt="Poster" 
                             class="w-24 h-36 object-cover rounded-lg shadow-lg">
                        <div>
                            <h4 class="text-xl font-bold text-blue-400">{{ $booking->suatChieu?->phim?->ten_phim ?? 'Phim không tồn tại' }}</h4>
                            <div class="mt-2 space-y-1 text-sm text-gray-300">
                                <p><i class="far fa-clock w-5 text-center"></i> Suất chiếu: <span class="text-white font-medium">{{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('H:i - d/m/Y') }}</span></p>
                                <p><i class="fas fa-door-open w-5 text-center"></i> Phòng: <span class="text-white font-medium">{{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}</span></p>
                                <p><i class="fas fa-film w-5 text-center"></i> Thời lượng: {{ $booking->suatChieu?->phim?->do_dai ?? 0 }} phút</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
                    <h3 class="text-lg font-semibold text-white mb-4 border-b border-[#262833] pb-3">💺 Ghế đã đặt</h3>
                    @if($booking->chiTietDatVe->isEmpty())
                        <p class="text-gray-500 italic text-center py-4">Không có thông tin ghế.</p>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach($booking->chiTietDatVe as $detail)
                                <div class="bg-[#1d202a] p-3 rounded-lg border border-[#262833] flex flex-col items-center justify-center">
                                    <span class="text-2xl font-bold text-white mb-1">{{ $detail->ghe->so_ghe ?? '?' }}</span>
                                    <span class="text-xs text-gray-400">{{ $detail->ghe->loaiGhe->ten_loai ?? 'Thường' }}</span>
                                    <span class="text-xs text-green-400 mt-1 font-mono">{{ number_format($detail->gia, 0) }}đ</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($booking->chiTietCombo->isNotEmpty())
                <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
                    <h3 class="text-lg font-semibold text-white mb-4 border-b border-[#262833] pb-3">🍿 Combo / Bắp nước</h3>
                    <div class="space-y-3">
                        @foreach($booking->chiTietCombo as $detail)
                            <div class="flex items-center justify-between bg-[#1d202a] p-3 rounded-lg border border-[#262833]">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center text-xl">🥤</div>
                                    <div>
                                        <div class="text-white font-medium">{{ $detail->combo->ten ?? 'Combo đã xóa' }}</div>
                                        <div class="text-xs text-gray-500">{{ number_format($detail->gia_ap_dung, 0) }}đ x {{ $detail->so_luong }}</div>
                                    </div>
                                </div>
                                <div class="text-green-400 font-mono font-bold">
                                    {{ number_format($detail->gia_ap_dung * $detail->so_luong, 0) }}đ
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <div class="space-y-6">
                
                <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
                    <h3 class="text-lg font-semibold text-white mb-4 border-b border-[#262833] pb-3">👤 Khách hàng</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-blue-600/20 text-blue-400 rounded-full flex items-center justify-center text-xl">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="text-white font-bold text-lg">{{ $booking->nguoiDung->ho_ten ?? 'Khách vãng lai' }}</div>
                            <div class="text-sm text-gray-400">{{ $booking->nguoiDung ? 'Thành viên' : 'Guest' }}</div>
                        </div>
                    </div>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-envelope w-5 text-center text-gray-500"></i>
                            <span>{{ $booking->nguoiDung->email ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-phone w-5 text-center text-gray-500"></i>
                            <span>{{ $booking->nguoiDung->sdt ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
                    <h3 class="text-lg font-semibold text-white mb-4 border-b border-[#262833] pb-3">💰 Thanh toán</h3>
                    
                    <div class="space-y-3 text-sm mb-4">
                        <div class="flex justify-between text-gray-400">
                            <span>Tổng tiền ghế</span>
                            <span class="text-white font-mono">{{ number_format($seatTotal, 0) }}đ</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Tổng tiền Combo</span>
                            <span class="text-white font-mono">{{ number_format($comboTotal, 0) }}đ</span>
                        </div>
                        
                        @if($discount > 0)
                            <div class="flex justify-between text-red-400">
                                <span>Khuyến mãi ({{ $booking->khuyenMai->ma_km ?? '' }})</span>
                                <span class="font-mono">-{{ number_format($discount, 0) }}đ</span>
                            </div>
                        @endif

                        <div class="border-t border-[#262833] pt-3 mt-3 flex justify-between items-center">
                            <span class="text-white font-bold text-lg">TỔNG CỘNG</span>
                            <span class="text-[#F53003] font-bold text-2xl font-mono">{{ number_format($total, 0) }}đ</span>
                        </div>
                    </div>

                    <div class="bg-[#1d202a] rounded-lg p-3 text-sm space-y-2 border border-[#262833]">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Phương thức</span>
                            <span class="text-white font-medium">{{ $booking->thanhToan->phuong_thuc ?? 'Chưa thanh toán' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Trạng thái TT</span>
                            @if(optional($booking->thanhToan)->trang_thai == 1)
                                <span class="text-green-400 font-medium"><i class="fas fa-check-circle mr-1"></i>Thành công</span>
                            @else
                                <span class="text-yellow-400 font-medium"><i class="fas fa-clock mr-1"></i>Chờ xử lý</span>
                            @endif
                        </div>
                        @if(!empty($booking->thanhToan->ma_giao_dich))
                            <div class="flex justify-between">
                                <span class="text-gray-500">Mã GD</span>
                                <span class="text-gray-300 font-mono">{{ Str::limit($booking->thanhToan->ma_giao_dich, 15) }}</span>
                            </div>
                        @endif
                        @if(!empty($booking->thanhToan->thoi_gian))
                             <div class="flex justify-between">
                                <span class="text-gray-500">Thời gian</span>
                                <span class="text-gray-300 text-xs">{{ \Carbon\Carbon::parse($booking->thanhToan->thoi_gian)->format('H:i:s d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection