@extends('layouts.user')

@section('title', 'Chỉnh sửa hồ sơ')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Chỉnh sửa hồ sơ</h1>
                <p class="text-gray-400 mt-1">Cập nhật thông tin cá nhân của bạn</p>
            </div>
            <a href="{{ route('user.profile') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <form action="{{ route('user.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Avatar Section -->
            <div class="text-center mb-6">
                <div class="w-24 h-24 bg-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-white text-3xl font-bold">{{ strtoupper(substr($user->ho_ten, 0, 1)) }}</span>
                </div>
                <p class="text-gray-400 text-sm">Ảnh đại diện</p>
            </div>

            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-medium text-white mb-4">Thông tin cá nhân</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ho_ten" class="block text-sm font-medium text-gray-300 mb-2">
                            Họ và tên <span class="text-red-400">*</span>
                        </label>
                        <input type="text" id="ho_ten" name="ho_ten" value="{{ old('ho_ten', $user->ho_ten) }}" required
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        @error('ho_ten')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                            Email <span class="text-red-400">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="so_dien_thoai" class="block text-sm font-medium text-gray-300 mb-2">
                            Số điện thoại
                        </label>
                        <input type="tel" id="so_dien_thoai" name="so_dien_thoai" value="{{ old('so_dien_thoai', $user->so_dien_thoai) }}"
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        @error('so_dien_thoai')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ngay_sinh" class="block text-sm font-medium text-gray-300 mb-2">
                            Ngày sinh
                        </label>
                        <input type="date" id="ngay_sinh" name="ngay_sinh" value="{{ old('ngay_sinh', $user->ngay_sinh) }}"
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        @error('ngay_sinh')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="gioi_tinh" class="block text-sm font-medium text-gray-300 mb-2">
                            Giới tính
                        </label>
                        <select id="gioi_tinh" name="gioi_tinh" 
                                class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <option value="">Chọn giới tính</option>
                            <option value="Nam" {{ old('gioi_tinh', $user->gioi_tinh) == 'Nam' ? 'selected' : '' }}>Nam</option>
                            <option value="Nữ" {{ old('gioi_tinh', $user->gioi_tinh) == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                            <option value="Khác" {{ old('gioi_tinh', $user->gioi_tinh) == 'Khác' ? 'selected' : '' }}>Khác</option>
                        </select>
                        @error('gioi_tinh')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Account Actions -->
            <div class="border-t border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-white mb-4">Thao tác tài khoản</h3>
                <div class="space-y-3">
                    <a href="{{ route('user.change-password.form') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-key mr-2"></i>Đổi mật khẩu
                    </a>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
                <a href="{{ route('user.profile') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    Hủy
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
