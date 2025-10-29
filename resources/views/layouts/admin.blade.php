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
        color: white !important;
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

    /* Additional nuclear option - override system colors */
    select {
        background-color: #1a1d24 !important;
        color: white !important;
        color-scheme: dark !important;
        forced-color-adjust: none !important;
    }

    select option {
        background-color: #1a1d24 !important;
        color: white !important;
        color-scheme: dark !important;
        forced-color-adjust: none !important;
    }

    /* Specific fixes for different browsers */
    @-moz-document url-prefix() {
        select option {
            background-color: #1a1d24 !important;
            color: white !important;
        }
    }

    /* Webkit specific fixes */
    select option::-webkit-option {
        background-color: #1a1d24 !important;
        color: white !important;
    }

    /* Additional specificity for stubborn browsers */
    body select option {
        background-color: #1a1d24 !important;
        color: white !important;
    }

    html body select option {
        background-color: #1a1d24 !important;
        color: white !important;
    }

    /* Nuclear option - force all select styling */
    select {
        background-color: #1a1d24 !important;
        color: white !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
    }

    select option {
        background-color: #1a1d24 !important;
        color: white !important;
        padding: 8px 12px !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
    }

    /* Force option styling with maximum specificity */
    html body div select option {
        background-color: #1a1d24 !important;
        color: white !important;
    }

    /* Alternative approach - use CSS custom properties */
    :root {
        --dropdown-bg: #1a1d24;
        --dropdown-text: white;
        --dropdown-hover: #262833;
        --dropdown-selected: #F53003;
    }

    select {
        background-color: var(--dropdown-bg) !important;
        color: var(--dropdown-text) !important;
    }

    select option {
        background-color: var(--dropdown-bg) !important;
        color: var(--dropdown-text) !important;
    }

    /* Restore appearance for other elements */
    input, button, textarea {
        -webkit-appearance: initial;
        -moz-appearance: initial;
        appearance: initial;
    }

    /* JavaScript fallback for stubborn browsers */
    .force-dropdown-styles {
        background-color: #1a1d24 !important;
        color: white !important;
    }

    .force-dropdown-styles option {
        background-color: #1a1d24 !important;
        color: white !important;
    }
    </style>

    <!-- JavaScript fallback for dropdown styling -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Force style all select elements and their options
        function forceDropdownStyles() {
            const selects = document.querySelectorAll('select');
            selects.forEach(select => {
                // Force select styling
                select.style.setProperty('background-color', '#1a1d24', 'important');
                select.style.setProperty('color', 'white', 'important');
                select.style.setProperty('-webkit-appearance', 'none', 'important');
                select.style.setProperty('-moz-appearance', 'none', 'important');
                select.style.setProperty('appearance', 'none', 'important');
                
                // Force option styling
                const options = select.querySelectorAll('option');
                options.forEach(option => {
                    option.style.setProperty('background-color', '#1a1d24', 'important');
                    option.style.setProperty('color', 'white', 'important');
                    option.style.setProperty('padding', '8px 12px', 'important');
                });
            });
        }

        // Apply styles immediately
        forceDropdownStyles();

        // Reapply styles when dropdowns are opened/focused
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'SELECT') {
                setTimeout(forceDropdownStyles, 10);
            }
        });

        document.addEventListener('focus', function(e) {
            if (e.target.tagName === 'SELECT') {
                setTimeout(forceDropdownStyles, 10);
            }
        });

        // Reapply styles when page content changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    setTimeout(forceDropdownStyles, 50);
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Additional fallback - periodically check and fix
        setInterval(forceDropdownStyles, 1000);
    });
    </script>
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
              <a href="{{ route('admin.suat-chieu.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.suat-chieu.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-calendar-alt w-5"></i>
                <span>Suất chiếu</span>
              </a>
            </div>
          @else
            <!-- Staff only menu items -->
            <div class="space-y-1">
              <div class="text-xs text-[#666] font-semibold uppercase tracking-wider px-3 py-1">Xem thông tin</div>
              <a href="{{ route('staff.suat-chieu.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('staff.suat-chieu.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-calendar-alt w-5"></i>
                <span>Suất chiếu</span>
              </a>
              <a href="{{ route('staff.phong-chieu.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('staff.phong-chieu.*') ? 'bg-[#F53003] text-white' : 'text-[#a6a6b0] hover:bg-[#222533] hover:text-white' }}">
                <i class="fas fa-video w-5"></i>
                <span>Phòng chiếu</span>
              </a>
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
  </body>
 </html>
