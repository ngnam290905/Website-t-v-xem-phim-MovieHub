@extends('admin.layout')

@section('title', 'Chi tiết Đặt vé - Staff')
@section('page-title', 'Chi tiết Đặt vé')
@section('page-description', 'Xem thông tin chi tiết đặt vé')

@section('content')
<div class="space-y-6">
    <!-- Booking Info -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">Thông tin đặt vé #{{ $datVe->id }}</h2>
            <div class="flex items-center gap-2">
                @if($datVe->trang_thai == 1)
                    <span class="px-3 py-1 text-sm bg-green-500/20 text-green-400 rounded">Đã thanh toán</span>
                @else
                    <span class="px-3 py-1 text-sm bg-orange-500/20 text-orange-400 rounded">Chờ thanh toán</span>
                @endif
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin người dùng</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Họ tên:</span>
                        <span class="text-white">{{ $datVe->nguoiDung->ho_ten ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Email:</span>
                        <span class="text-white">{{ $datVe->nguoiDung->email ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Số điện thoại:</span>
                        <span class="text-white">{{ $datVe->nguoiDung->so_dien_thoai ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin đặt vé</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ngày đặt:</span>
                        <span class="text-white">{{ $datVe->ngay_dat_ve ? date('d/m/Y H:i:s', strtotime($datVe->ngay_dat_ve)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Phim:</span>
                        <span class="text-white">{{ $datVe->suatChieu->phim->ten_phim ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Suất chiếu:</span>
                        <span class="text-white">{{ $datVe->suatChieu->thoi_gian_bat_dau ? date('d/m/Y H:i', strtotime($datVe->suatChieu->thoi_gian_bat_dau)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Phòng chiếu:</span>
                        <span class="text-white">{{ $datVe->suatChieu->phongChieu->ten_phong ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
        <div class="p-6 border-b border-[#262833]">
            <h3 class="text-lg font-bold text-white">Chi tiết vé</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số ghế</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Loại ghế</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Giá vé</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Combo</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Giá combo</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($datVe->chiTietDatVe as $chiTiet)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4 text-sm text-white font-medium">
                            {{ $chiTiet->ghe->so_ghe ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded">Không xác định</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-white font-medium">
                            0 đ
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            Không có
                        </td>
                        <td class="px-6 py-4 text-sm text-white font-medium">
                            0 đ
                        </td>
                        <td class="px-6 py-4 text-sm text-white font-bold">
                            0 đ
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <p>Không có chi tiết vé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-[#1a1d2e] border-t border-[#262833]">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-white">
                            Tổng cộng:
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-white">
                            0 đ
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
        <a href="{{ route('staff.dat-ve.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>
</div>
@endsection
