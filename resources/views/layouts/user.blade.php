<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MovieHub')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#F53003',
                    }
                }
            }
        }
    </script>
    <style>
        /* Fallback styles when Vite chưa chạy */
        body { background:#0f0f12; color:#fff; }
        .seat{width:28px;height:28px;border-radius:6px;background:#2a2d3a;border:1px solid #2f3240;transition:all .15s ease}
        .seat:hover{filter:brightness(1.2)}
        .seat-vip{background:#3b2a1a;border-color:#5a3b22}
        .seat-booked{background:#3a3a3a;border-color:#555;cursor:not-allowed;opacity:.6}
        .seat-selected{background:#F53003;border-color:#F53003;box-shadow:0 0 0 2px #2a2d3a inset}
    </style>
    <link rel="icon" href="/favicon.ico">
</head>
<body class="min-h-screen bg-[#0f0f12] text-white">
    @include('partials.header')
    <div class="max-w-7xl mx-auto px-4 py-8 flex gap-6">
        <!-- User Sidebar -->
        <aside class="w-64 shrink-0">
            <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-4">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-[#F53003] rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-white text-xl font-bold">{{ strtoupper(substr(Auth::user()->ho_ten, 0, 1)) }}</span>
                    </div>
                    <h3 class="text-white font-medium">{{ Auth::user()->ho_ten }}</h3>
                    <p class="text-[#a6a6b0] text-sm">{{ Auth::user()->email }}</p>
                </div>
                
                <nav class="space-y-1">
                    <a href="{{ route('user.profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-[#F53003] text-white">
                        <i class="fas fa-user text-sm"></i>
                        <span>Thông tin tài khoản</span>
                    </a>
                    <a href="{{ route('user.edit-profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition">
                        <i class="fas fa-edit text-sm"></i>
                        <span>Chỉnh sửa hồ sơ</span>
                    </a>
                    <a href="{{ route('user.change-password') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition">
                        <i class="fas fa-key text-sm"></i>
                        <span>Đổi mật khẩu</span>
                    </a>
                    <a href="{{ route('user.booking-history') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition">
                        <i class="fas fa-ticket-alt text-sm"></i>
                        <span>Vé của tôi</span>
                    </a>
                    
                    @if(in_array(optional(Auth::user()->vaiTro)->ten, ['admin', 'staff']))
                        <div class="border-t border-[#262833] my-2"></div>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition">
                            <i class="fas fa-cog text-sm"></i>
                            <span>Quản trị</span>
                        </a>
                    @endif
                    
                    <div class="border-t border-[#262833] my-2"></div>
                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition text-left">
                            <i class="fas fa-sign-out-alt text-sm"></i>
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </nav>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-600/20 border border-green-600 text-green-400 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-600/20 border border-red-600 text-red-400 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @include('partials.footer')

    <!-- Scripts -->
    <script>
        // Auto hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.bg-green-600\\/20, .bg-red-600\\/20');
            messages.forEach(msg => msg.remove());
        }, 5000);
    </script>

    @yield('scripts')
</body>
</html>
