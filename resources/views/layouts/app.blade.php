<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
      <main class="flex-1">
      @yield('content')
      </main>
    </div>
    @include('partials.footer')
    @include('partials.chatbot')

    <!-- Global Trailer Modal -->
    <div id="global-trailer-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/90">
        <div class="relative w-full max-w-5xl mx-4">
            <button onclick="closeGlobalTrailer()" class="absolute -top-10 right-0 text-white hover:text-[#F53003] text-2xl z-10">
                <i class="fas fa-times"></i>
            </button>
            <div class="relative pb-[56.25%] h-0 overflow-hidden rounded-lg">
                <iframe id="global-trailer-iframe" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <script>
        // Global Trailer Modal Functions
        function openTrailer(trailerUrl, movieTitle) {
            const modal = document.getElementById('global-trailer-modal');
            const iframe = document.getElementById('global-trailer-iframe');
            
            if (!modal || !iframe) return;
            
            // Convert YouTube URL to embed format
            let embedUrl = trailerUrl;
            if (trailerUrl.includes('youtube.com/watch')) {
                const videoId = trailerUrl.split('v=')[1]?.split('&')[0];
                embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            } else if (trailerUrl.includes('youtu.be/')) {
                const videoId = trailerUrl.split('youtu.be/')[1]?.split('?')[0];
                embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            }
            
            iframe.src = embedUrl;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeGlobalTrailer() {
            const modal = document.getElementById('global-trailer-modal');
            const iframe = document.getElementById('global-trailer-iframe');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            if (iframe) {
                iframe.src = '';
            }
            document.body.style.overflow = '';
        }

        // Close modal on outside click
        document.getElementById('global-trailer-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeGlobalTrailer();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeGlobalTrailer();
            }
        });
    </script>
 </html>