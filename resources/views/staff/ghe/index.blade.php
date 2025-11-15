@extends('admin.layout')

@section('title', 'Danh sách Ghế - Staff')
@section('page-title', 'Danh sách Ghế')
@section('page-description', 'Xem thông tin tất cả ghế')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Tổng ghế</div>
                    <div class="text-3xl font-bold text-white">{{ $totalSeats }}</div>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chair text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Ghế thường</div>
                    <div class="text-3xl font-bold text-gray-400">{{ $normalSeats }}</div>
                </div>
                <div class="w-12 h-12 bg-gray-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chair text-gray-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Ghế VIP</div>
                    <div class="text-3xl font-bold text-purple-400">{{ $vipSeats }}</div>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-crown text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Còn trống</div>
                    <div class="text-3xl font-bold text-green-400">{{ $availableSeats }}</div>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form method="GET" action="{{ route('staff.ghe.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Tìm kiếm theo số ghế, phòng chiếu..." 
                    class="w-full px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#F53003]/90 transition-colors">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('staff.ghe.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Seats List -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số ghế</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phòng chiếu</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Hàng</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Loại ghế</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($ghes as $ghe)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-[#262833] rounded-lg flex items-center justify-center">
                                    <i class="fas fa-chair text-[#a6a6b0]"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $ghe->so_ghe }}</div>
                                    <div class="text-sm text-[#a6a6b0]">Ghế {{ $ghe->so_ghe }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $ghe->phongChieu->ten_phong ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $ghe->hang_ghe ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded">Không xác định</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded">Không xác định</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('staff.ghe.show', $ghe->id) }}" class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded hover:bg-blue-500/30 transition-colors">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <i class="fas fa-chair text-4xl mb-2"></i>
                            <p>Không có dữ liệu ghế</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($ghes->hasPages())
        <div class="px-6 py-4 border-t border-[#262833]">
            {{ $ghes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
