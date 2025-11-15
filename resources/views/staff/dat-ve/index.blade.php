@extends('admin.layout')

@section('title', 'Danh sách Đặt vé - Staff')
@section('page-title', 'Danh sách Đặt vé')
@section('page-description', 'Xem thông tin tất cả đặt vé')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Tổng đặt vé</div>
                    <div class="text-3xl font-bold text-white">{{ $totalBookings }}</div>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-ticket-alt text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Đã thanh toán</div>
                    <div class="text-3xl font-bold text-green-400">{{ $paidBookings }}</div>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Chờ thanh toán</div>
                    <div class="text-3xl font-bold text-orange-400">{{ $pendingBookings }}</div>
                </div>
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Tổng doanh thu</div>
                    <div class="text-3xl font-bold text-purple-400">{{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form method="GET" action="{{ route('staff.dat-ve.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Tìm kiếm theo tên người dùng, phim..." 
                    class="w-full px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
            </div>
            <div class="flex gap-2">
                <select name="status" class="px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Chờ thanh toán</option>
                </select>
                <button type="submit" class="px-6 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#F53003]/90 transition-colors">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('staff.dat-ve.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Bookings List -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Mã đặt vé</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Người dùng</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phim</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Suất chiếu</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số lượng</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Tổng tiền</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($datVes as $datVe)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-[#262833] rounded-lg flex items-center justify-center">
                                    <i class="fas fa-ticket-alt text-[#a6a6b0]"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">#{{ $datVe->id }}</div>
                                    <div class="text-sm text-[#a6a6b0]">{{ $datVe->ngay_dat_ve ? date('d/m/Y H:i', strtotime($datVe->ngay_dat_ve)) : 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-white">{{ $datVe->nguoiDung->ho_ten ?? 'N/A' }}</div>
                            <div class="text-sm text-[#a6a6b0]">{{ $datVe->nguoiDung->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-white">{{ $datVe->suatChieu->phim->ten_phim ?? 'N/A' }}</div>
                            <div class="text-sm text-[#a6a6b0]">{{ $datVe->suatChieu->phim->the_loai ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $datVe->suatChieu->thoi_gian_bat_dau ? date('d/m/Y H:i', strtotime($datVe->suatChieu->thoi_gian_bat_dau)) : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-white font-medium">
                            {{ $datVe->chiTietDatVe->count() }} vé
                        </td>
                        <td class="px-6 py-4 text-sm text-white font-medium">
                            0 đ
                        </td>
                        <td class="px-6 py-4">
                            @if($datVe->trang_thai == 1)
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Đã thanh toán</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-orange-500/20 text-orange-400 rounded">Chờ thanh toán</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('staff.dat-ve.show', $datVe->id) }}" class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded hover:bg-blue-500/30 transition-colors">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <i class="fas fa-ticket-alt text-4xl mb-2"></i>
                            <p>Không có dữ liệu đặt vé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($datVes->hasPages())
        <div class="px-6 py-4 border-t border-[#262833]">
            {{ $datVes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
