@extends('admin.layout')

@section('title', 'Chi tiết Phòng chiếu - Staff')
@section('page-title', 'Chi tiết Phòng chiếu')
@section('page-description', 'Xem thông tin chi tiết phòng chiếu')

@section('content')
<div class="space-y-6">
    <!-- Room Info -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">{{ $phongChieu->ten_phong }}</h2>
            <div class="flex items-center gap-2">
                @if($phongChieu->status == 'active' || $phongChieu->status == 'hoạt động' || $phongChieu->status == 1)
                    <span class="px-3 py-1 text-sm bg-green-500/20 text-green-400 rounded">Hoạt động</span>
                @else
                    <span class="px-3 py-1 text-sm bg-red-500/20 text-red-400 rounded">Ngừng hoạt động</span>
                @endif
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin cơ bản</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">ID:</span>
                        <span class="text-white font-medium">{{ $phongChieu->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Tên phòng:</span>
                        <span class="text-white">{{ $phongChieu->ten_phong ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Loại phòng:</span>
                        <span class="text-white">{{ $phongChieu->loai_phong ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Số lượng ghế:</span>
                        <span class="text-white font-medium">{{ $phongChieu->so_luong_ghe ?? 0 }}</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Thông tin trạng thái</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Trạng thái:</span>
                        <span class="text-white">
                            @if($phongChieu->status == 'active' || $phongChieu->status == 'hoạt động' || $phongChieu->status == 1)
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Hoạt động</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Ngừng hoạt động</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Ngày tạo:</span>
                        <span class="text-white">{{ $phongChieu->created_at ? date('d/m/Y H:i:s', strtotime($phongChieu->created_at)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Cập nhật:</span>
                        <span class="text-white">{{ $phongChieu->updated_at ? date('d/m/Y H:i:s', strtotime($phongChieu->updated_at)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#a6a6b0]">Người tạo:</span>
                        <span class="text-white">Admin</span>
                    </div>
                </div>
            </div>
        </div>
        
        @if($phongChieu->mo_ta)
        <div class="mt-6">
            <h3 class="text-sm font-medium text-[#a6a6b0] mb-2">Mô tả phòng chiếu</h3>
            <p class="text-white">{{ $phongChieu->mo_ta }}</p>
        </div>
        @endif
    </div>

    <!-- Room Statistics -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Thống kê phòng chiếu</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-400">{{ $phongChieu->so_luong_ghe ?? 0 }}</div>
                <div class="text-sm text-[#a6a6b0]">Tổng số ghế</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-400">0</div>
                <div class="text-sm text-[#a6a6b0]">Ghế đã đặt</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-400">{{ $phongChieu->so_luong_ghe ?? 0 }}</div>
                <div class="text-sm text-[#a6a6b0]">Ghế trống</div>
            </div>
        </div>
    </div>

    <!-- Seat List -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Danh sách ghế</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số ghế</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Hàng</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Loại ghế</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($phongChieu->ghes ?? [] as $ghe)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4 text-sm text-white font-medium">{{ $ghe->so_ghe ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">{{ $ghe->hang_ghe ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded">Không xác định</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded">Không xác định</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <i class="fas fa-chair text-4xl mb-2"></i>
                            <p>Không có dữ liệu ghế</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Showtimes -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Suất chiếu gần đây</h3>
        <div class="text-center py-8 text-[#a6a6b0]">
            <i class="fas fa-clock text-4xl mb-2"></i>
            <p>Chưa có dữ liệu suất chiếu</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
        <a href="{{ route('staff.phong-chieu.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>
</div>
@endsection
