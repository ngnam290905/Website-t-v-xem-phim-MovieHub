@extends('layouts.user')

@section('title', 'Đổi mật khẩu')

@section('content')
<div class="max-w-md mx-auto">
    <!-- Header -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Đổi mật khẩu</h1>
                <p class="text-gray-400 mt-1">Cập nhật mật khẩu tài khoản của bạn</p>
            </div>
            <a href="{{ route('user.profile') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại
            </a>
        </div>
    </div>

    <!-- Change Password Form -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <form action="{{ route('user.change-password') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-300 mb-2">
                    Mật khẩu hiện tại <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="current_password" name="current_password" required
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent pr-10">
                    <button type="button" onclick="togglePassword('current_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i id="current_password_icon" class="fas fa-eye text-gray-400 hover:text-gray-300"></i>
                    </button>
                </div>
                @error('current_password')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                    Mật khẩu mới <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent pr-10">
                    <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i id="password_icon" class="fas fa-eye text-gray-400 hover:text-gray-300"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-400">Mật khẩu phải có ít nhất 8 ký tự</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                    Xác nhận mật khẩu mới <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent pr-10">
                    <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i id="password_confirmation_icon" class="fas fa-eye text-gray-400 hover:text-gray-300"></i>
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Security Tips -->
            <div class="bg-gray-700 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-yellow-400 mt-1"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-white">Lưu ý bảo mật</h3>
                        <div class="mt-2 text-sm text-gray-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Sử dụng mật khẩu mạnh, kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                                <li>Không sử dụng mật khẩu dễ đoán như ngày sinh, số điện thoại</li>
                                <li>Thay đổi mật khẩu định kỳ để bảo vệ tài khoản</li>
                                <li>Không chia sẻ mật khẩu với người khác</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('user.profile') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    Hủy
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-key mr-2"></i>Đổi mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Check password match in real-time
    document.getElementById('password_confirmation').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmation = this.value;
        
        if (password !== confirmation && confirmation !== '') {
            this.setCustomValidity('Mật khẩu xác nhận không khớp');
        } else {
            this.setCustomValidity('');
        }
    });
</script>
@endsection
