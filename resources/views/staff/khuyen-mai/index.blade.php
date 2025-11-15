@extends('admin.layout')

@section('title', 'Danh sách Khuyến mãi - Staff')
@section('page-title', 'Danh sách Khuyến mãi')
@section('page-description', 'Xem thông tin tất cả khuyến mãi')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Tổng khuyến mãi</div>
                    <div class="text-3xl font-bold text-white">{{ $totalPromotions }}</div>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tags text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Đang diễn ra</div>
                    <div class="text-3xl font-bold text-green-400">{{ $activePromotions }}</div>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-play-circle text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Sắp diễn ra</div>
                    <div class="text-3xl font-bold text-orange-400">{{ $upcomingPromotions }}</div>
                </div>
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Đã kết thúc</div>
                    <div class="text-3xl font-bold text-red-400">{{ $expiredPromotions }}</div>
                </div>
                <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-stop-circle text-red-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form method="GET" action="{{ route('staff.khuyen-mai.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Tìm kiếm theo tên khuyến mãi..." 
                    class="w-full px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
            </div>
            <div class="flex gap-2">
                <select name="status" class="px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang diễn ra</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Sắp diễn ra</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Đã kết thúc</option>
                </select>
                <button type="submit" class="px-6 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#F53003]/90 transition-colors">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('staff.khuyen-mai.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Promotions List -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Khuyến mãi</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Mã giảm giá</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Giảm giá</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời gian</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($khuyenMais as $khuyenMai)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($khuyenMai->anh)
                                    <img src="{{ asset('storage/' . $khuyenMai->anh) }}" alt="{{ $khuyenMai->ten_khuyen_mai }}" class="h-12 w-12 object-cover rounded">
                                @else
                                    <div class="h-12 w-12 bg-[#262833] rounded-lg flex items-center justify-center">
                                        <i class="fas fa-tags text-[#a6a6b0]"></i>
                                    </div>
                                @endif
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $khuyenMai->ten_khuyen_mai }}</div>
                                    <div class="text-sm text-[#a6a6b0]">{{ $khuyenMai->ngay_tao ? date('d/m/Y', strtotime($khuyenMai->ngay_tao)) : 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-white font-medium">
                            {{ $khuyenMai->ma_giam_gia ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-white font-medium">
                            @if($khuyenMai->loai_giam_gia == 'phan_tram')
                                {{ $khuyenMai->gia_tri_giam }}%
                            @else
                                {{ number_format($khuyenMai->gia_tri_giam, 0, ',', '.') }} đ
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            <div>{{ $khuyenMai->ngay_bat_dau ? date('d/m/Y', strtotime($khuyenMai->ngay_bat_dau)) : 'N/A' }}</div>
                            <div>{{ $khuyenMai->ngay_ket_thuc ? date('d/m/Y', strtotime($khuyenMai->ngay_ket_thuc)) : 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($khuyenMai->ngay_bat_dau <= now() && $khuyenMai->ngay_ket_thuc >= now())
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Đang diễn ra</span>
                            @elseif($khuyenMai->ngay_bat_dau > now())
                                <span class="px-2 py-1 text-xs bg-orange-500/20 text-orange-400 rounded">Sắp diễn ra</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Đã kết thúc</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('staff.khuyen-mai.show', $khuyenMai->id) }}" class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded hover:bg-blue-500/30 transition-colors">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <i class="fas fa-tags text-4xl mb-2"></i>
                            <p>Không có dữ liệu khuyến mãi</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($khuyenMais->hasPages())
        <div class="px-6 py-4 border-t border-[#262833]">
            {{ $khuyenMais->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
