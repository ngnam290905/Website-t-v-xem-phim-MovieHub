@extends('admin.layout')

@section('title', 'Chi tiết Người dùng - Staff')
@section('page-title', 'Chi tiết Người dùng')
@section('page-description', 'Xem thông tin chi tiết người dùng')

@section('content')
<div class="space-y-6">
    <!-- User Info -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Avatar -->
            <div class="md:w-1/4">
                <div class="h-32 w-32 bg-[#262833] rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-6xl text-[#a6a6b0]"></i>
                </div>
            </div>
            
            <!-- User Details -->
            <div class="md:w-3/4 space-y-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $user->ho_ten }}</h1>
                    <p class="text-lg text-[#a6a6b0]">{{ $user->email }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Số điện thoại:</span>
                            <span class="text-white">{{ $user->so_dien_thoai ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Ngày sinh:</span>
                            <span class="text-white">{{ $user->ngay_sinh ? date('d/m/Y', strtotime($user->ngay_sinh)) : 'N/A' }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Giới tính:</span>
                            <span class="text-white">{{ $user->gioi_tinh == 1 ? 'Nam' : ($user->gioi_tinh == 2 ? 'Nữ' : 'Khác') }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Vai trò:</span>
                            @if($user->vaiTro)
                                <span class="px-2 py-1 text-xs bg-blue-500/20 text-blue-400 rounded">
                                    {{ $user->vaiTro->ten }}
                                </span>
                            @else
                                <span class="text-white">N/A</span>
                            @endif
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Trạng thái:</span>
                            @if($user->trang_thai == 1)
                                <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">Hoạt động</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-500/20 text-red-400 rounded">Khóa</span>
                            @endif
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="text-[#a6a6b0] w-24">Ngày tạo:</span>
                            <span class="text-white">{{ $user->created_at ? date('d/m/Y H:i', strtotime($user->created_at)) : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                
                @if($user->dia_chi)
                <div>
                    <h3 class="text-lg font-semibold text-white mb-2">Địa chỉ</h3>
                    <p class="text-[#a6a6b0]">{{ $user->dia_chi }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Back Button -->
    <div class="flex justify-start">
        <a href="{{ route('staff.users.index') }}" class="px-6 py-2 bg-[#262833] text-white rounded-lg hover:bg-[#262833]/80 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại danh sách
        </a>
    </div>
</div>
@endsection
