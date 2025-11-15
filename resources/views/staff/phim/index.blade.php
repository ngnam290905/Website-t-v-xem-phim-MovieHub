@extends('admin.layout')

@section('title', 'Danh sách Phim - Staff')
@section('page-title', 'Danh sách Phim')
@section('page-description', 'Xem thông tin tất cả các phim')

@section('content')
<div class="space-y-6">
    <!-- Search and Filter -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form method="GET" action="{{ route('staff.movies.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Tìm kiếm theo tên phim..." 
                    class="w-full px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#F53003]/90 transition-colors">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('staff.movies.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Movies List -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phim</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thể loại</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời lượng</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($movies as $movie)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-16 w-12 flex-shrink-0 bg-[#262833] rounded-lg overflow-hidden">
                                    @if($movie->poster)
                                        <img src="{{ asset('storage/' . $movie->poster) }}" alt="{{ $movie->ten_phim }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center">
                                            <i class="fas fa-film text-[#a6a6b0]"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $movie->ten_phim }}</div>
                                    <div class="text-sm text-[#a6a6b0]">{{ $movie->ten_phim_en }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($movie->the_loai)
                                    @foreach(explode(',', $movie->the_loai) as $genre)
                                        <span class="px-2 py-1 text-xs bg-[#262833] text-[#a6a6b0] rounded">{{ trim($genre) }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $movie->thoi_luong }} phút
                        </td>
                        <td class="px-6 py-4">
                            @if($movie->trang_thai == 1)
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Đang chiếu</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Ngừng chiếu</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('staff.movies.show', $movie) }}" class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded hover:bg-blue-500/30 transition-colors">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <i class="fas fa-film text-4xl mb-2"></i>
                            <p>Không có dữ liệu phim</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($movies->hasPages())
        <div class="px-6 py-4 border-t border-[#262833]">
                            {{ $movies->links() }}
                        </div>
                        @endif
    </div>
</div>
@endsection
