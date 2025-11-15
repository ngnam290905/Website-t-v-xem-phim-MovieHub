@extends('admin.layout')

@section('title', 'Danh sách Người dùng - Staff')
@section('page-title', 'Danh sách Người dùng')
@section('page-description', 'Xem thông tin tất cả người dùng')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Tổng người dùng</div>
                    <div class="text-3xl font-bold text-white">{{ $totalUsers }}</div>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Đang hoạt động</div>
                    <div class="text-3xl font-bold text-green-400">{{ $activeUsers }}</div>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Đã khóa</div>
                    <div class="text-3xl font-bold text-red-400">{{ $inactiveUsers }}</div>
                </div>
                <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-lock text-red-400 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-[#a6a6b0] mb-1">Admin</div>
                    <div class="text-3xl font-bold text-purple-400">{{ $adminUsers }}</div>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-shield text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form method="GET" action="{{ route('staff.users.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Tìm kiếm theo tên, email..." 
                    class="w-full px-4 py-2 bg-[#1a1d2e] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#F53003]/90 transition-colors">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('staff.users.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users List -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d2e] border-b border-[#262833]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thông tin</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số điện thoại</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Vai trò</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Ngày tạo</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($users as $user)
                    <tr class="hover:bg-[#1a1d2e] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 bg-[#262833] rounded-full flex items-center justify-center">
                                    @if($user->anh_dai_dien)
                                        <img src="{{ asset('storage/' . $user->anh_dai_dien) }}" alt="{{ $user->ho_ten }}" class="h-full w-full object-cover rounded-full">
                                    @else
                                        <i class="fas fa-user text-[#a6a6b0]"></i>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $user->ho_ten }}</div>
                                    <div class="text-sm text-[#a6a6b0]">{{ $user->ngay_sinh ? date('d/m/Y', strtotime($user->ngay_sinh)) : 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $user->so_dien_thoai ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($user->vaiTro)
                                <span class="px-2 py-1 text-xs {{ $user->vaiTro->ten === 'admin' ? 'bg-purple-500/20 text-purple-400' : ($user->vaiTro->ten === 'staff' ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-500/20 text-gray-400') }} rounded">
                                    {{ $user->vaiTro->ten }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded">
                                    N/A
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->trang_thai == 1)
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Hoạt động</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Khóa</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-[#a6a6b0]">
                            {{ $user->created_at ? date('d/m/Y', strtotime($user->created_at)) : 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('staff.users.show', $user->id) }}" class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded hover:bg-blue-500/30 transition-colors">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-[#a6a6b0]">
                            <i class="fas fa-users text-4xl mb-2"></i>
                            <p>Không có dữ liệu người dùng</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-[#262833]">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
