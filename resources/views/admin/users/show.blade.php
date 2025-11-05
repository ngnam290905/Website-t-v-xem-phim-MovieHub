@extends('admin.layout')

@section('title', 'Chi tiết người dùng - Admin')

@section('content')
<div class="bg-[#151822] border border-[#262833] rounded-xl p-6 max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Chi tiết tài khoản #{{ $user->id }}</h2>
        <a href="{{ route('admin.users.index') }}"
           class="text-gray-400 hover:text-white underline text-sm">
            ← Quay lại danh sách
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-900 bg-opacity-50 border border-green-700 text-green-300 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Thông tin cơ bản -->
        <div class="space-y-4">
            <div class="bg-[#1a1d2a] p-4 rounded-lg border border-[#2f3240]">
                <h3 class="text-lg font-semibold text-yellow-400 mb-3">Thông tin cá nhân</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Họ tên:</span>
                        <span class="font-medium">{{ $user->ho_ten }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Email:</span>
                        <span class="font-medium">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Số điện thoại:</span>
                        <span class="font-medium">{{ $user->sdt ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Địa chỉ:</span>
                        <span class="font-medium">{{ $user->dia_chi ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Vai trò:</span>
                        <span class="px-2 py-1 rounded text-xs font-medium
                            @if($user->vaiTro->ten == 'admin') bg-red-900 text-red-300
                            @elseif($user->vaiTro->ten == 'staff') bg-blue-900 text-blue-300
                            @else bg-gray-700 text-gray-300 @endif">
                            {{ ucfirst($user->vaiTro->ten) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Trạng thái:</span>
                        <span class="px-2 py-1 rounded text-xs font-medium
                            {{ $user->trang_thai ? 'bg-green-900 text-green-300' : 'bg-gray-800 text-gray-500' }}">
                            {{ $user->trang_thai ? 'Hoạt động' : 'Bị khóa' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Ngày tạo:</span>
                        <span class="font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Thành viên VIP -->
            <div class="bg-[#1a1d2a] p-4 rounded-lg border border-[#2f3240]">
                <h3 class="text-lg font-semibold text-yellow-400 mb-3">Thành viên </h3>
                <div class="grid grid-cols-1 gap-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Tổng chi tiêu:</span>
                        <span class="text-xl font-bold text-green-400">
                            {{ number_format($user->tong_chi_tieu) }}đ
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Điểm tích lũy:</span>
                        <span class="text-xl font-bold text-yellow-400">
                            {{ $user->diemThanhVien?->tong_diem ?? 0 }} điểm
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Hạng thành viên:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-bold
                            @if(!$user->hangThanhVien) bg-gray-700 text-gray-300
                            @elseif(str_contains(strtolower($user->hangThanhVien->ten_hang), 'vip')) bg-purple-900 text-purple-300
                            @elseif(str_contains(strtolower($user->hangThanhVien->ten_hang), 'gold')) bg-yellow-900 text-yellow-300
                            @elseif(str_contains(strtolower($user->hangThanhVien->ten_hang), 'silver')) bg-gray-600 text-gray-200
                            @else bg-gray-700 text-gray-300 @endif">
                            {{ $user->hangThanhVien?->ten_hang ?? 'Thường' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avatar + Hành động -->
        <div class="flex flex-col items-center justify-center space-y-4">
            <div class="relative">
                @if ($user->hinh_anh)
                    <img src="{{ asset('storage/' . $user->hinh_anh) }}"
                         alt="{{ $user->ho_ten }}"
                         class="w-32 h-32 rounded-full object-cover border-4 border-[#2f3240] shadow-lg">
                @else
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-[#F53003] to-[#ff6b3d] flex items-center justify-center text-3xl text-white font-bold shadow-lg">
                        {{ strtoupper(substr($user->ho_ten, 0, 2)) }}
                    </div>
                @endif
            </div>

            <div class="flex gap-3 mt-4">
                <a href="{{ route('admin.users.edit', $user->id) }}"
                   class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 rounded text-sm font-medium transition">
                    Sửa thông tin
                </a>
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                      onsubmit="return confirm('Xóa tài khoản này? Dữ liệu sẽ vào thùng rác.')"
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-sm font-medium transition">
                        Xóa tài khoản
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection