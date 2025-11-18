<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký - MovieHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0d0f14;
        }
        .form-container {
            background-color: #151822;
            border: 1px solid #262833;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo và Tiêu đề -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="w-full h-full object-contain rounded">
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Đăng ký MovieHub</h1>
            <p class="text-gray-400">Tạo tài khoản để trải nghiệm điện ảnh tuyệt vời</p>
        </div>

        <!-- Form đăng ký -->
        <div class="form-container rounded-xl p-8">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-500 bg-opacity-20 border border-red-400 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-400 mt-1 mr-3"></i>
                        <div>
                            <p class="text-red-200 font-medium mb-2">Có lỗi xảy ra:</p>
                            <ul class="text-red-100 text-sm space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                
                <!-- Họ tên -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-user mr-2"></i>Họ và tên
                    </label>
                    <input type="text" 
                           name="ho_ten" 
                           value="{{ old('ho_ten') }}" 
                           required
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition duration-300"
                           placeholder="Nhập đầy đủ họ và tên">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition duration-300"
                           placeholder="email@example.com">
                </div>

                <!-- Số điện thoại -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-phone mr-2"></i>Số điện thoại
                    </label>
                    <input type="tel" 
                           name="sdt" 
                           value="{{ old('sdt') }}" 
                           required
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition duration-300"
                           placeholder="Nhập số điện thoại">
                </div>

                <!-- Địa chỉ -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-map-marker-alt mr-2"></i>Địa chỉ
                    </label>
                    <textarea name="dia_chi" 
                              rows="3"
                              required
                              class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition duration-300 resize-none"
                              placeholder="Nhập địa chỉ của bạn">{{ old('dia_chi') }}</textarea>
                </div>

                <!-- Mật khẩu -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-lock mr-2"></i>Mật khẩu
                    </label>
                    <input type="password" 
                           name="password" 
                           required
                           minlength="6"
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition duration-300"
                           placeholder="Tối thiểu 6 ký tự">
                </div>

                <!-- Xác nhận mật khẩu -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-lock mr-2"></i>Xác nhận mật khẩu
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           required
                           minlength="6"
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition duration-300"
                           placeholder="Nhập lại mật khẩu">
                </div>

                <!-- Nút đăng ký -->
                <button type="submit" 
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-opacity-50">
                    <i class="fas fa-user-plus mr-2"></i>Tạo tài khoản
                </button>
            </form>

            <!-- Link đăng nhập -->
            <div class="mt-6 text-center">
                <p class="text-gray-200">
                    Đã có tài khoản? 
                    <a href="{{ route('login.form') }}" 
                       class="text-purple-300 hover:text-purple-200 font-medium transition duration-300">
                        Đăng nhập ngay
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-gray-300 text-sm">
                <i class="fas fa-shield-alt mr-2"></i>
                Thông tin của bạn được bảo mật an toàn
            </p>
        </div>
    </div>
</body>
</html>


