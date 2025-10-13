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
  </head>
  <body class="min-h-screen bg-[#0d0f14] text-white">
    <div class="flex h-screen">
      <!-- Mobile menu button -->
      <button id="mobile-menu-button" class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-[#151822] border border-[#262833] rounded-md text-white hover:bg-[#222533] transition-colors duration-200">
        <i class="fas fa-bars"></i>
      </button>

      <!-- Sidebar cố định -->
      <aside id="sidebar" class="w-64 bg-[#151822] border-r border-[#262833] flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out fixed lg:static inset-y-0 left-0 z-40">
        <!-- Header -->
        <div class="p-6 border-b border-[#262833]">
          <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="MovieHub" class="h-12 w-12 object-contain rounded">
            <div>
              <span class="text-xl font-bold text-white">MovieHub</span>
              <p class="text-xs text-[#a6a6b0]">Admin Panel</p>
            </div>
          </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-2">
          <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
            <i class="fas fa-tachometer-alt w-5"></i>
            Bảng điều khiển
          </a>
          <a href="#movies" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition-colors duration-200">
            <i class="fas fa-film w-5"></i>
            Phim
          </a>
          <a href="{{ route('admin.suat-chieu.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.suat-chieu.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
            <i class="fas fa-calendar-alt w-5"></i>
            Suất chiếu
          </a>
          <a href="{{ route('admin.ghe.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.ghe.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
            <i class="fas fa-chair w-5"></i>
            Ghế
          </a>
          <a href="#tickets" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition-colors duration-200">
            <i class="fas fa-ticket-alt w-5"></i>
            Vé
          </a>
          <a href="#users" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition-colors duration-200">
            <i class="fas fa-users w-5"></i>
            Người dùng
          </a>
        </nav>

        <!-- Footer -->
        <div class="p-4 border-t border-[#262833]">
          <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-[#a6a6b0] hover:bg-[#222533] hover:text-white transition-colors duration-200">
            <i class="fas fa-home w-5"></i>
            Về trang chủ
          </a>
        </div>

      </aside>

      <!-- Mobile overlay -->
      <div id="mobile-overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

      <!-- Main content -->
      <div class="flex-1 flex flex-col overflow-hidden lg:ml-0">
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
  </body>
 </html>
