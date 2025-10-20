<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MovieHub')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css','resources/js/app.js'])
    @else
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
    @endif
    <link rel="icon" href="/favicon.ico">
  </head>
  <body class="min-h-screen bg-[#0f0f12] text-white">
    @include('partials.header')
    <div class="max-w-7xl mx-auto px-4 py-8 flex gap-6">
      @include('partials.sidebar')
      <main class="flex-1">
      @yield('content')
      </main>
    </div>
    @include('partials.footer')
  </body>
 </html>