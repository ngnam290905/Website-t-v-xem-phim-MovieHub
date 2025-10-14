<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - MovieHub')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css','resources/js/app.js'])
    @else
      <script src="https://cdn.tailwindcss.com"></script>
    @endif
  </head>
  <body class="min-h-screen bg-[#0d0f14] text-white">
    <header class="bg-[#151822] border-b border-[#262833]">
      <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-6">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 shrink-0">
          <img src="{{ asset('images/logo.jpg') }}" alt="MovieHub" class="h-16 w-16 object-contain rounded">
          <span class="text-xl font-semibold">Admin</span>
        </a>
        <a href="{{ route('home') }}" class="text-sm hover:text-[#F53003]">← Về trang chủ</a>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8 flex gap-6">
      <aside class="hidden lg:block w-64 shrink-0">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
          <h3 class="font-semibold mb-3">Quản trị</h3>
          <nav class="flex flex-col gap-1 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded hover:bg-[#222533]">Bảng điều khiển</a>
            <a href="#movies" class="px-3 py-2 rounded hover:bg-[#222533]">Phim</a>
            <a href="#showtimes" class="px-3 py-2 rounded hover:bg-[#222533]">Suất chiếu</a>
            <a href="#tickets" class="px-3 py-2 rounded hover:bg-[#222533]">Vé</a>
            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded hover:bg-[#222533]">Người dùng</a>
          </nav>
        </div>
      </aside>

      <main class="flex-1">
        @yield('content')
      </main>   
    </div>

    <footer class="mt-8 border-t border-[#262833]">
      <div class="max-w-7xl mx-auto px-4 py-6 text-sm text-[#a6a6b0]">Admin · {{ date('Y') }} · MovieHub</div>
    </footer>
  </body>
 </html>


