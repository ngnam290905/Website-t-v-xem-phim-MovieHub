<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập - MovieHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Tự động refresh CSRF token nếu trang load lâu (tránh lỗi 419)
        setTimeout(function() {
            fetch('{{ route('login.form') }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => response.text())
              .then(html => {
                  const parser = new DOMParser();
                  const doc = parser.parseFromString(html, 'text/html');
                  const newToken = doc.querySelector('meta[name="csrf-token"]');
                  if (newToken) {
                      document.querySelector('meta[name="csrf-token"]').content = newToken.content;
                      const tokenInput = document.querySelector('input[name="_token"]');
                      if (tokenInput) {
                          tokenInput.value = newToken.content;
                      }
                  }
              });
        }, 60000); // Refresh sau 1 phút
    </script>
    <style>
        body {
            background-color: #0d0f14;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .form-container {
            background-color: #151822;
            border: 1px solid #262833;
            border-radius: 1rem;
            padding: 2rem;
            width: 100%;
            max-width: 28rem;
        }
    </style>
</head>
<body>
    <div class="w-full max-w-md">
        <!-- Logo và Tiêu đề -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="w-full h-full object-contain rounded">
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Đăng nhập MovieHub</h1>
            <p class="text-gray-400">Chào mừng trở lại!</p>
        </div>
        <!-- Form đăng nhập -->
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

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                
                <!-- Email -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required
                           class="w-full px-4 py-3 bg-[#1b1e28] border border-[#262833] rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition duration-300"
                           placeholder="email@example.com">
                </div>

                <!-- Mật khẩu -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-lock mr-2"></i>Mật khẩu
                    </label>
                    <input type="password" 
                           name="password" 
                           required
                           class="w-full px-4 py-3 bg-[#1b1e28] border border-[#262833] rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition duration-300"
                           placeholder="Nhập mật khẩu">
                </div>

                <!-- Xác nhận mật khẩu -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-lock mr-2"></i>Xác nhận mật khẩu
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           required
                           class="w-full px-4 py-3 bg-[#1b1e28] border border-[#262833] rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition duration-300"
                           placeholder="Nhập lại mật khẩu">
                </div>

                <!-- Ghi nhớ đăng nhập -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="remember" 
                           id="remember"
                           class="w-4 h-4 text-[#F53003] bg-[#1b1e28] border-[#262833] rounded focus:ring-[#F53003] focus:ring-2">
                    <label for="remember" class="ml-2 text-sm text-gray-300">
                        Ghi nhớ đăng nhập
                    </label>
                </div>

                <!-- Nút đăng nhập -->
                <button type="submit" 
                        class="w-full bg-[#F53003] hover:bg-[#e02a00] text-white font-bold py-3 px-4 rounded-lg transition duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:ring-opacity-50">
                    <i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập
                </button>
            </form>

            <!-- Link đăng ký -->
            <div class="mt-6 text-center">
                <p class="text-gray-400">
                    Chưa có tài khoản? 
                    <a href="{{ route('register.form') }}" 
                       class="text-[#F53003] hover:text-[#e02a00] font-medium transition duration-300">
                        Đăng ký ngay
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-gray-400 text-sm">
                <i class="fas fa-shield-alt mr-2"></i>
                Đăng nhập an toàn và bảo mật
            </p>
        </div>
    </div>
</body>
</html>


