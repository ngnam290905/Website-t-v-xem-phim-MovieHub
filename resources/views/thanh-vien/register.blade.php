@extends('layouts.app')

@section('title', 'Đăng ký thành viên')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-4">
                <i class="fas fa-crown text-yellow-400 mr-2"></i>
                Đăng ký thành viên MovieHub
            </h1>
            <p class="text-gray-300">Trở thành thành viên để nhận nhiều ưu đãi hấp dẫn</p>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500 text-green-300 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/20 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-500/20 border border-blue-500 text-blue-300 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-info-circle mr-2"></i>
                {{ session('info') }}
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Form đăng ký -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700">
                <h2 class="text-2xl font-bold text-white mb-6">
                    <i class="fas fa-user-plus mr-2 text-purple-400"></i>
                    Thông tin đăng ký
                </h2>

                <form action="{{ route('thanh-vien.register') }}" method="POST">
                    @csrf

                    <!-- Thông tin hiện tại -->
                    <div class="mb-6">
                        <label class="block text-gray-300 text-sm font-semibold mb-2">
                            <i class="fas fa-user mr-2"></i>Họ tên
                        </label>
                        <input type="text" value="{{ auth()->user()->ho_ten ?? '' }}" disabled
                            class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white">
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-300 text-sm font-semibold mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </label>
                        <input type="email" value="{{ auth()->user()->email ?? '' }}" disabled
                            class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white">
                    </div>

                    <!-- Điều khoản -->
                    <div class="mb-6">
                        <div class="bg-gray-700/30 rounded-lg p-4 max-h-48 overflow-y-auto border border-gray-600">
                            <h3 class="font-semibold text-white mb-2">Điều khoản và điều kiện:</h3>
                            <ul class="text-gray-300 text-sm space-y-2">
                                <li><i class="fas fa-check text-green-400 mr-2"></i>Thành viên được tích điểm khi mua vé và combo</li>
                                <li><i class="fas fa-check text-green-400 mr-2"></i>Điểm có hiệu lực trong 12 tháng kể từ ngày tích</li>
                                <li><i class="fas fa-check text-green-400 mr-2"></i>Điểm có thể dùng để đổi quà hoặc giảm giá</li>
                                <li><i class="fas fa-check text-green-400 mr-2"></i>Hạng thành viên được nâng cấp tự động theo điểm</li>
                                <li><i class="fas fa-check text-green-400 mr-2"></i>MovieHub có quyền thay đổi chính sách thành viên</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Checkbox đồng ý -->
                    <div class="mb-6">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" name="dong_y_dieu_khoan" value="1" required
                                class="mt-1 h-5 w-5 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500">
                            <span class="ml-3 text-gray-300">
                                Tôi đã đọc và đồng ý với 
                                <span class="text-purple-400 font-semibold">Điều khoản và điều kiện</span>
                            </span>
                        </label>
                        @error('dong_y_dieu_khoan')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4">
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:scale-105">
                            <i class="fas fa-check mr-2"></i>
                            Đăng ký ngay
                        </button>
                        <a href="{{ route('home') }}"
                            class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 text-center">
                            <i class="fas fa-times mr-2"></i>
                            Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>

            <!-- Quyền lợi thành viên -->
            <div>
                <div class="bg-gradient-to-br from-yellow-600/20 to-orange-600/20 backdrop-blur-sm rounded-2xl p-8 border border-yellow-500/30 mb-6">
                    <h2 class="text-2xl font-bold text-white mb-6">
                        <i class="fas fa-gift mr-2 text-yellow-400"></i>
                        Quyền lợi thành viên
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="bg-yellow-500/20 rounded-lg p-3 mr-4">
                                <i class="fas fa-star text-yellow-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold mb-1">Tích điểm mua sắm</h3>
                                <p class="text-gray-300 text-sm">Tích 1 điểm cho mỗi 10.000 VNĐ chi tiêu</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="bg-purple-500/20 rounded-lg p-3 mr-4">
                                <i class="fas fa-percentage text-purple-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold mb-1">Giảm giá đặc biệt</h3>
                                <p class="text-gray-300 text-sm">Giảm giá lên đến 15% cho thành viên Bạch Kim</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="bg-pink-500/20 rounded-lg p-3 mr-4">
                                <i class="fas fa-birthday-cake text-pink-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold mb-1">Ưu đãi sinh nhật</h3>
                                <p class="text-gray-300 text-sm">Voucher đặc biệt trong tháng sinh nhật</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="bg-green-500/20 rounded-lg p-3 mr-4">
                                <i class="fas fa-ticket-alt text-green-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold mb-1">Ưu tiên đặt vé</h3>
                                <p class="text-gray-300 text-sm">Đặt vé sớm cho phim hot</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hạng thành viên -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700">
                    <h2 class="text-2xl font-bold text-white mb-6">
                        <i class="fas fa-layer-group mr-2 text-blue-400"></i>
                        Các hạng thành viên
                    </h2>
                    
                    <div class="space-y-3">
                        <div class="bg-gradient-to-r from-orange-700/30 to-orange-900/30 border border-orange-600 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-orange-300 font-bold text-lg">
                                        <i class="fas fa-medal mr-2"></i>Đồng
                                    </h3>
                                    <p class="text-gray-400 text-sm">0 - 999 điểm</p>
                                </div>
                                <span class="text-orange-400 text-2xl">★</span>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-gray-600/30 to-gray-800/30 border border-gray-500 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-gray-300 font-bold text-lg">
                                        <i class="fas fa-medal mr-2"></i>Bạc
                                    </h3>
                                    <p class="text-gray-400 text-sm">1.000 - 4.999 điểm</p>
                                </div>
                                <span class="text-gray-400 text-2xl">★★</span>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-yellow-700/30 to-yellow-900/30 border border-yellow-600 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-yellow-300 font-bold text-lg">
                                        <i class="fas fa-medal mr-2"></i>Vàng
                                    </h3>
                                    <p class="text-gray-400 text-sm">5.000 - 9.999 điểm</p>
                                </div>
                                <span class="text-yellow-400 text-2xl">★★★</span>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-blue-700/30 to-purple-900/30 border border-purple-600 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-purple-300 font-bold text-lg">
                                        <i class="fas fa-crown mr-2"></i>Bạch Kim
                                    </h3>
                                    <p class="text-gray-400 text-sm">10.000+ điểm</p>
                                </div>
                                <span class="text-purple-400 text-2xl">★★★★</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
