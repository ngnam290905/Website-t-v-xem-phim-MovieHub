<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MovieHub - Đặt vé xem phim')</title>
    @yield('meta')
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css','resources/js/app.js'])
    @else
      <script src="https://cdn.tailwindcss.com"></script>
    @endif
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #0d0f14 0%, #1a1d24 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="min-h-screen bg-[#0d0f14] text-white">
    <!-- Header -->
    <header class="bg-[#1a1d24] border-b border-[#262833] sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3">
                        <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-10 w-10 object-contain rounded">
                        <span class="text-2xl font-bold text-white">MovieHub</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-white hover:text-[#F53003] transition-colors duration-300 font-medium">Trang chủ</a>
                    <a href="#movies" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Phim</a>
                    <a href="#cinemas" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Rạp</a>
                    <a href="#promotions" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Khuyến mãi</a>
                </nav>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-white hover:text-[#F53003] transition-colors duration-300">
                                <i class="fas fa-user-circle text-xl"></i>
                                <span class="hidden sm:block">{{ auth()->user()->ho_ten ?? 'User' }}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-[#1a1d24] border border-[#262833] rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                                <div class="py-2">
                                    @if(auth()->user()->vaiTro && auth()->user()->vaiTro->ten === 'admin')
                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-white hover:bg-[#222533] transition-colors duration-300">
                                            <i class="fas fa-cog mr-2"></i>Admin Panel
                                        </a>
                                    @endif
                                    <a href="#" class="block px-4 py-2 text-sm text-white hover:bg-[#222533] transition-colors duration-300">
                                        <i class="fas fa-user mr-2"></i>Thông tin cá nhân
                                    </a>
                                    <a href="#" class="block px-4 py-2 text-sm text-white hover:bg-[#222533] transition-colors duration-300">
                                        <i class="fas fa-ticket-alt mr-2"></i>Vé của tôi
                                    </a>
                                    <hr class="my-2 border-[#262833]">
                                    <form action="{{ route('logout') }}" method="POST" class="block">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-white hover:bg-[#222533] transition-colors duration-300">
                                            <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login.form') }}" class="text-[#a6a6b0] hover:text-white transition-colors duration-300 font-medium">Đăng nhập</a>
                        <a href="{{ route('register.form') }}" class="bg-[#F53003] hover:bg-[#e02a00] text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">Đăng ký</a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <button class="md:hidden p-2 text-white hover:text-[#F53003] transition-colors duration-300" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
            </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="md:hidden bg-[#1a1d24] border-t border-[#262833] hidden">
            <div class="px-4 py-2 space-y-2">
                <a href="{{ route('home') }}" class="block py-2 text-white hover:text-[#F53003] transition-colors duration-300">Trang chủ</a>
                <a href="#movies" class="block py-2 text-[#a6a6b0] hover:text-white transition-colors duration-300">Phim</a>
                <a href="#cinemas" class="block py-2 text-[#a6a6b0] hover:text-white transition-colors duration-300">Rạp</a>
                <a href="#promotions" class="block py-2 text-[#a6a6b0] hover:text-white transition-colors duration-300">Khuyến mãi</a>
                @auth
                    @if(auth()->user()->vaiTro && auth()->user()->vaiTro->ten === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="block py-2 text-white hover:text-[#F53003] transition-colors duration-300">
                            <i class="fas fa-cog mr-2"></i>Admin Panel
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left py-2 text-white hover:text-[#F53003] transition-colors duration-300">
                            <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                        </button>
                    </form>
                @else
                    <a href="{{ route('login.form') }}" class="block py-2 text-[#a6a6b0] hover:text-white transition-colors duration-300">Đăng nhập</a>
                    <a href="{{ route('register.form') }}" class="block py-2 text-[#a6a6b0] hover:text-white transition-colors duration-300">Đăng ký</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#1a1d24] border-t border-[#262833] mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-10 w-10 object-contain rounded">
                        <span class="text-2xl font-bold text-white">MovieHub</span>
                    </div>
                    <p class="text-[#a6a6b0] mb-6 leading-relaxed">
                        Trải nghiệm điện ảnh tuyệt vời với công nghệ đặt vé hiện đại. 
                        Chọn phim, chọn ghế, thanh toán nhanh chóng.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-[#222533] rounded-full flex items-center justify-center text-white hover:bg-[#F53003] transition-colors duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-[#222533] rounded-full flex items-center justify-center text-white hover:bg-[#F53003] transition-colors duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-[#222533] rounded-full flex items-center justify-center text-white hover:bg-[#F53003] transition-colors duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-[#222533] rounded-full flex items-center justify-center text-white hover:bg-[#F53003] transition-colors duration-300">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Liên kết nhanh</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Tất cả phim</a></li>
                        <li><a href="#" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Phim sắp chiếu</a></li>
                        <li><a href="#" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Rạp chiếu</a></li>
                        <li><a href="#" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Khuyến mãi</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Hỗ trợ</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Trung tâm trợ giúp</a></li>
                        <li><a href="#" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Điều khoản sử dụng</a></li>
                        <li><a href="#" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Chính sách bảo mật</a></li>
                        <li><a href="#" class="text-[#a6a6b0] hover:text-white transition-colors duration-300">Liên hệ</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-[#262833] mt-8 pt-8">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <p class="text-[#a6a6b0] text-sm">
                        &copy; {{ date('Y') }} MovieHub. All rights reserved.
                    </p>
                    <div class="flex items-center space-x-6 mt-4 md:mt-0">
                        <span class="text-[#a6a6b0] text-sm">Đối tác:</span>
                        <div class="flex items-center space-x-4">
                            <span class="text-xs bg-red-500 text-white px-3 py-1 rounded-full">CGV</span>
                            <span class="text-xs bg-blue-500 text-white px-3 py-1 rounded-full">Lotte</span>
                            <span class="text-xs bg-yellow-500 text-black px-3 py-1 rounded-full">Galaxy</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuButton = event.target.closest('button[onclick="toggleMobileMenu()"]');
            
            if (!mobileMenuButton && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>