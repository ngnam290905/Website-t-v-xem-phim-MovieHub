<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - MovieHub')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css','resources/js/app.js'])
    @else
      <script src="https://cdn.tailwindcss.com"></script>
    @endif
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Global Dark Mode CSS Fixes -->
    <style>
    /* Admin Action Buttons */
    .btn-action {
        @apply px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-200 flex items-center justify-center gap-1.5;
    }
    
    .btn-view {
        @apply bg-green-600 text-white hover:bg-green-700;
    }
    
    .btn-edit {
        @apply bg-blue-600 text-white hover:bg-blue-700;
    }
    
    .btn-delete {
        @apply bg-red-600 text-white hover:bg-red-700;
    }
    
    .btn-action i {
        @apply text-sm;
    }
    
    /* Table action buttons */
    .btn-table-action {
        @apply inline-flex items-center justify-center p-1.5 rounded-md transition-colors duration-200;
        min-width: 28px;
        height: 28px;
    }
    
    .btn-table-view {
        @apply bg-green-600 text-white hover:bg-green-700;
    }
    
    .btn-table-edit {
        @apply bg-blue-600 text-white hover:bg-blue-700;
    }
    
    .btn-table-delete {
        @apply bg-red-600 text-white hover:bg-red-700;
    }
    
    /* Fix dark mode dropdown options visibility - More aggressive approach */
    select {
        color: white !important;
        background-color: #1a1d24 !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
    }

    select option {
        background-color: #1a1d24 !important;
        color: white !important;
        padding: 8px 12px !important;
    }

    select option:hover {
        background-color: #262833 !important;
        color: white !important;
    }

    select option:focus {
        background-color: #262833 !important;
        color: white !important;
    }

    select option:checked,
    select option:selected {
        background-color: #F53003 !important;
        color: white !important;
    }

    /* Force override for all browsers */
    select option[selected] {
        background-color: #F53003 !important;
        color ojos white !important;
    }

    /* Additional dark mode fixes */
    input[type="date"] {
        color-scheme: dark;
        background-color: #1a1d24 !important;
        color: white !important;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }

    /* Force dark color scheme for all form elements */
    select {
        color-scheme: dark !important;
    }

    select option {
        color-scheme: dark !important;
        }
    </style>
    
    @stack('styles')
                            </head>
                            <body class="min-h-screen bg-[#0d0f14] text-white">
    <div class="flex h-screen">
      <!-- Mobile menu button -->
      <button id="mobile-menu-button" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-[#151822] border border-[#262833] rounded-lg text-white hover:bg-[#222533] transition-colors duration-200 shadow-lg">
        <i class="fas fa-bars text-lg"></i>
      </button>

      <!-- Sidebar cố định -->
      <aside id="sidebar" class="w-64 bg-[#151822] border-r border-[#262833] flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out fixed lg:static inset-y-0 left-0 z-40 lg:z-auto">
        <!-- Header -->
        <div class="p-6 border-b border-[#262833]">
          <a href="{{ request()->routeIs('staff.*') ? route('staff.dashboard') : route('admin.dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-12 w-12 object-contain rounded">
            <div>
              <span class="text-xl font-bold text-white">MovieHub</span>
              <p class="text-xs text-[#a6a6b0]">{{ request()->routeIs('staff.*') ? 'Staff Panel' : 'Admin Panel' }}</p>
            </div>
          </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-2">
          <!-- Dashboard -->
          <a href="{{ request()->routeIs('staff.*') ? route('staff.dashboard') : route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.dashboard') || request()->routeIs('staff.dashboard') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
            <i class="fas fa-tachometer-alt w-5"></i>
            <span>Bảng điều khiển</span>
          </a>
          
          @if(request()->routeIs('admin.*'))
            <!-- Admin only menu items -->
            <div class="space-y-1">
              <div class="text-xs text-[#666] font-semibold uppercase tracking-wider px-3 py-1">Quản lý</div>
              <a href="{{ route('admin.phong-chieu.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.phong-chieu.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-video w-5"></i>
                <span>Phòng chiếu</span>
              </a>
              <a href="{{ route('admin.ghe.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.phong-chieu.manage-seats') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-chair w-5"></i>
                <span>Quản lý ghế</span>
              </a>
              <a href="{{ route('admin.suat-chieu.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.suat-chieu.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-calendar-alt w-5"></i>
                <span>Suất chiếu</span>
              </a>
              <a href="{{ route('admin.movies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.movies.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-film w-5"></i>
                <span>Phim</span>
              </a>
              <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.bookings.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-ticket-alt w-5"></i>
                <span>Đặt vé</span>
              </a>
              <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-users w-5"></i>
                <span>Người dùng</span>
              </a>
              <a href="{{ route('admin.reports.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-chart-bar w-5"></i>
                <span>Báo cáo</span>
              </a>
              <a href="{{ route('admin.khuyenmai.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.khuyenmai.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-gift w-5"></i>
                <span>Khuyến mãi</span>
              </a>
              <a href="{{ route('admin.combos.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.combos.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-box-open w-5"></i>
                <span>Combo</span>
              </a>
            </div>
          @else
            <!-- Staff only menu items -->
            <div class="space-y-1">
              <div class="text-xs text-[#666] font-semibold uppercase tracking-wider px-3 py-1">{{ auth()->user()->vaiTro->ten === 'admin' ? 'Quản lý' : 'Xem thông tin' }}</div>
            
            <!-- Movies -->
            <a href="{{ route('admin.movies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.movies.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
              <i class="fas fa-film w-5"></i>
              <span>Phim</span>
            </a>
            
            <!-- Showtimes -->
            <a href="{{ route('admin.suat-chieu.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.suat-chieu.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
              <i class="fas fa-calendar-alt w-5"></i>
              <span>Suất chiếu</span>
            </a>
            
            <!-- Rooms -->
            <a href="{{ route('admin.phong-chieu.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.phong-chieu.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
              <i class="fas fa-video w-5"></i>
              <span>Phòng chiếu</span>
            </a>
            
            <!-- Seats -->
            <a href="{{ route('admin.ghe.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.ghe.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
              <i class="fas fa-chair w-5"></i>
              <span>Ghế</span>
            </a>
            
            <!-- Tickets -->
            <a href="{{ route('admin.ve.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.ve.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
              <i class="fas fa-ticket-alt w-5"></i>
              <span>Vé</span>
            </a>
            
            <!-- Users -->
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
              <i class="fas fa-users w-5"></i>
              <span>Tài khoản</span>
            </a>
            
            <!-- Combos -->
            <a href="{{ route('admin.combos.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.combos.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
              <i class="fas fa-tags w-5"></i>
              <span>Combo</span>
            </a>
            
            <!-- Promotions -->
            <a href="{{ route('admin.khuyen-mai.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.khuyen-mai.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
              <i class="fas fa-percent w-5"></i>
              <span>Khuyến mãi</span>
            </a>
          </div>
            </div>
          @endif
            </nav>

        <!-- Footer -->
        <div class="p-4 border-t border-[#262833] space-y-2">
          <div class="space-y-1">
            <div class="text-xs text-[#666] font-semibold uppercase tracking-wider px-3 py-1">Hệ thống</div>
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition-colors duration-200">
              <i class="fas fa-home w-5"></i>
              <span>Về trang chủ</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="w-full">
              @csrf
              <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition-colors duration-200">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Đăng xuất</span>
              </button>
            </form>
          </div>
        </div>

      </aside>

      <!-- Mobile overlay -->
      <div id="mobile-overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

            <!-- Main content -->
      <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top bar -->
        <header class="bg-[#1a1d24] border-b border-[#262833] px-6 py-4">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-white">@yield('page-title', 'Admin')</h1>
              <p class="text-sm text-[#a6a6b0]">@yield('page-description', 'Quản lý hệ thống')</p>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-sm text-[#a6a6b0]">
                {{ date('d/m/Y H:i') }}
              </div>
            </div>
          </div>
        </header>

        <!-- Content area -->
        <main class="flex-1 overflow-y-auto bg-[#0d0f14]">
          <div class="max-w-6xl mx-auto p-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
      // Mobile menu toggle
      document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const sidebar = document.getElementById('sidebar');
        const mobileOverlay = document.getElementById('mobile-overlay');

        function toggleMobileMenu() {
          sidebar.classList.toggle('-translate-x-full');
          mobileOverlay.classList.toggle('hidden');
        }

        function closeMobileMenu() {
          sidebar.classList.add('-translate-x-full');
          mobileOverlay.classList.add('hidden');
        }

        mobileMenuButton.addEventListener('click', toggleMobileMenu);
        mobileOverlay.addEventListener('click', closeMobileMenu);

        // Close mobile menu when clicking on a link
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
          link.addEventListener('click', closeMobileMenu);
        });

        // Close mobile menu on escape key
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            closeMobileMenu();
          }
        });
      });
    </script>
    
    @stack('scripts')
</body>
</html>
