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
    <!-- Main Content -->
    <div class="flex flex-col h-screen overflow-hidden">
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
      // Initialize any admin scripts here
      document.addEventListener('DOMContentLoaded', function() {
        // Add any initialization code here
      });
    </script>
    
    @stack('scripts')
</body>
</html>
