@extends('admin.layout')

@section('title', 'Danh sách Phòng chiếu - Staff')
@section('page-title', 'Danh sách Phòng chiếu')
@section('page-description', 'Xem thông tin tất cả phòng chiếu')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Tổng phòng chiếu</div>
                    <div class="text-3xl font-bold text-white">{{ $totalRooms }}</div>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-door-open text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Hoạt động</div>
                    <div class="text-3xl font-bold text-green-400">{{ $activeRooms }}</div>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Ngừng hoạt động</div>
                    <div class="text-3xl font-bold text-red-400">{{ $inactiveRooms }}</div>
                </div>
                <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Tổng ghế</div>
                    <div class="text-3xl font-bold text-purple-400">{{ $totalSeats }}</div>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chair text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form method="GET" action="{{ route('staff.phong-chieu.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Tìm kiếm theo tên phòng chiếu..." 
                    class="w-full px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#F53003]/90 transition-colors">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('staff.phong-chieu.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Rooms List -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phòng chiếu</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Sức chứa</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Loại phòng</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($phongChieus as $phongChieu)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-[#262833] rounded-lg flex items-center justify-center">
                                    <i class="fas fa-door-open text-[#a6a6b0]"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $phongChieu->ten_phong }}</div>
                                    <div class="text-sm text-[#a6a6b0]">{{ $phongChieu->created_at ? date('d/m/Y', strtotime($phongChieu->created_at)) : 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $phongChieu->so_luong_ghe ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $phongChieu->loai_phong ?? 'Thường' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($phongChieu->status == 'active' || $phongChieu->status == 'hoạt động' || $phongChieu->status == 1)
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Hoạt động</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Ngừng hoạt động</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('staff.phong-chieu.show', $phongChieu->id) }}" class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded hover:bg-blue-500/30 transition-colors">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <i class="fas fa-door-open text-4xl mb-2"></i>
                            <p>Không có dữ liệu phòng chiếu</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($phongChieus->hasPages())
        <div class="px-6 py-4 border-t border-[#262833]">
            {{ $phongChieus->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
