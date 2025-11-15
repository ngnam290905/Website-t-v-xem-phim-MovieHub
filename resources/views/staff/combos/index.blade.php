@extends('admin.layout')

@section('title', 'Danh sách Combo - Staff')
@section('page-title', 'Danh sách Combo')
@section('page-description', 'Xem thông tin tất cả combo')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Tổng combo</div>
                    <div class="text-3xl font-bold text-white">{{ $totalCombos }}</div>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-popcorn text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Đang bán</div>
                    <div class="text-3xl font-bold text-green-400">{{ $activeCombos }}</div>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Ngừng bán</div>
                    <div class="text-3xl font-bold text-red-400">{{ $inactiveCombos }}</div>
                </div>
                <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Giá trung bình</div>
                    <div class="text-3xl font-bold text-orange-400">{{ number_format($avgPrice, 0, ',', '.') }}</div>
                </div>
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tag text-orange-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form method="GET" action="{{ route('staff.combos.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Tìm kiếm theo tên combo..." 
                    class="w-full px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#F53003]/90 transition-colors">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('staff.combos.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Combos List -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Combo</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Mô tả</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Giá</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($combos as $combo)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($combo->anh)
                                    <img src="{{ asset('storage/' . $combo->anh) }}" alt="{{ $combo->ten_combo }}" class="h-12 w-12 object-cover rounded">
                                @else
                                    <div class="h-12 w-12 bg-[#262833] rounded-lg flex items-center justify-center">
                                        <i class="fas fa-popcorn text-[#a6a6b0]"></i>
                                    </div>
                                @endif
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $combo->ten_combo }}</div>
                                    <div class="text-sm text-[#a6a6b0]">{{ $combo->created_at ? date('d/m/Y', strtotime($combo->created_at)) : 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ Str::limit($combo->mo_ta, 50) ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-white font-medium">
                            {{ number_format($combo->gia, 0, ',', '.') }} đ
                        </td>
                        <td class="px-6 py-4">
                            @if($combo->trang_thai == 'active')
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Đang bán</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Ngừng bán</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('staff.combos.show', $combo->id) }}" class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded hover:bg-blue-500/30 transition-colors">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <i class="fas fa-popcorn text-4xl mb-2"></i>
                            <p>Không có dữ liệu combo</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($combos->hasPages())
        <div class="px-6 py-4 border-t border-[#262833]">
            {{ $combos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
