@extends('admin.layout')

@section('title', 'Danh sách Suất chiếu - Staff')
@section('page-title', 'Danh sách Suất chiếu')
@section('page-description', 'Xem thông tin tất cả suất chiếu')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Tổng suất chiếu</div>
                    <div class="text-3xl font-bold text-white">{{ $totalShowtimes }}</div>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Hôm nay</div>
                    <div class="text-3xl font-bold text-green-400">{{ $todayShowtimes }}</div>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-day text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Sắp chiếu</div>
                    <div class="text-3xl font-bold text-orange-400">{{ $upcomingShowtimes }}</div>
                </div>
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-play text-orange-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Đã chiếu</div>
                    <div class="text-3xl font-bold text-gray-400">{{ $pastShowtimes }}</div>
                </div>
                <div class="w-12 h-12 bg-gray-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-history text-gray-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form method="GET" action="{{ route('staff.suat-chieu.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Tìm kiếm theo tên phim..." 
                    class="w-full px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#F53003]/90 transition-colors">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('staff.suat-chieu.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Showtimes List -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phim</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phòng chiếu</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời gian</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Giá vé</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($suatChieus as $suatChieu)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($suatChieu->phim->poster)
                                    <img src="{{ asset('storage/' . $suatChieu->phim->poster) }}" alt="{{ $suatChieu->phim->ten_phim }}" class="h-12 w-12 object-cover rounded">
                                @else
                                    <div class="h-12 w-12 bg-[#262833] rounded flex items-center justify-center">
                                        <i class="fas fa-film text-[#a6a6b0]"></i>
                                    </div>
                                @endif
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $suatChieu->phim->ten_phim }}</div>
                                    <div class="text-sm text-[#a6a6b0]">{{ $suatChieu->phim->the_loai }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $suatChieu->phongChieu->ten_phong ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $suatChieu->thoi_gian_bat_dau ? date('d/m/Y H:i', strtotime($suatChieu->thoi_gian_bat_dau)) : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-white font-medium">
                            {{ number_format($suatChieu->gia_ve, 0, ',', '.') }} đ
                        </td>
                        <td class="px-6 py-4">
                            @if($suatChieu->thoi_gian_bat_dau > now())
                                <span class="px-2 py-1 text-xs bg-orange-500/20 text-orange-400 rounded">Sắp chiếu</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded">Đã chiếu</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('staff.suat-chieu.show', $suatChieu->id) }}" class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded hover:bg-blue-500/30 transition-colors">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <i class="fas fa-clock text-4xl mb-2"></i>
                            <p>Không có dữ liệu suất chiếu</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($suatChieus->hasPages())
        <div class="px-6 py-4 border-t border-[#262833]">
            {{ $suatChieus->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
