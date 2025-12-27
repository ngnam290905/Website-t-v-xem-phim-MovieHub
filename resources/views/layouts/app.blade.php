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
    @yield('hero')
    <div class="max-w-7xl mx-auto px-4 py-8 flex gap-6">
      <main class="flex-1">
      @yield('content')
      </main>
    </div>
    @include('partials.footer')
    @include('partials.chatbot')
    
    <!-- Floating Quick Booking Button -->
    <div id="quick-booking-btn" class="fixed bottom-6 right-6 z-40">
        <button onclick="openQuickBooking()" class="group w-16 h-16 bg-gradient-to-r from-[#F53003] to-[#ff5c3a] rounded-full shadow-2xl shadow-[#F53003]/50 hover:shadow-[#F53003]/70 flex items-center justify-center text-white transition-all duration-300 transform hover:scale-110 hover:rotate-12">
            <i class="fas fa-ticket-alt text-2xl group-hover:scale-110 transition-transform"></i>
            <span class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-black text-xs font-bold animate-pulse">
                !
            </span>
        </button>
    </div>
    
    <!-- Quick Booking Modal -->
    <div id="quick-booking-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="relative w-full max-w-2xl mx-4 bg-[#151822] border border-[#262833] rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#262833] bg-[#0c0f16]">
                <h3 class="text-white text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-bolt text-[#F53003]"></i>
                    Đặt vé nhanh
                </h3>
                <button onclick="closeQuickBooking()" class="text-[#a6a6b0] hover:text-white text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-[#a6a6b0] mb-2">Chọn phim</label>
                        <select id="quick-movie-select" class="w-full bg-[#1a1d24] border border-[#262833] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#F53003]">
                            <option value="">-- Chọn phim --</option>
                            @php
                                $quickMovies = \App\Models\Phim::where('trang_thai', 'dang_chieu')->limit(10)->get();
                            @endphp
                            @foreach($quickMovies as $movie)
                                <option value="{{ $movie->id }}">{{ $movie->ten_phim }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-[#a6a6b0] mb-2">Chọn ngày</label>
                        <input type="date" id="quick-date-select" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full bg-[#1a1d24] border border-[#262833] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#F53003]">
                    </div>
                    <div>
                        <label class="block text-sm text-[#a6a6b0] mb-2">Chọn suất chiếu</label>
                        <select id="quick-showtime-select" class="w-full bg-[#1a1d24] border border-[#262833] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#F53003]" disabled>
                            <option value="">-- Chọn suất chiếu --</option>
                        </select>
                    </div>
                    <button onclick="goToQuickBooking()" class="w-full px-6 py-4 bg-gradient-to-r from-[#F53003] to-[#ff5c3a] hover:from-[#ff5c3a] hover:to-[#F53003] text-white rounded-lg font-bold text-lg transition-all duration-300 shadow-lg shadow-[#F53003]/50 hover:shadow-xl hover:shadow-[#F53003]/70 transform hover:scale-105">
                        <i class="fas fa-arrow-right mr-2"></i>
                        Tiếp tục đặt vé
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function openQuickBooking() {
            const modal = document.getElementById('quick-booking-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeQuickBooking() {
            const modal = document.getElementById('quick-booking-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }
        }
        
        function goToQuickBooking() {
            const movieId = document.getElementById('quick-movie-select')?.value;
            const date = document.getElementById('quick-date-select')?.value;
            const showtimeId = document.getElementById('quick-showtime-select')?.value;
            
            if (!movieId || !date) {
                alert('Vui lòng chọn phim và ngày chiếu');
                return;
            }
            
            if (showtimeId) {
                window.location.href = `/shows/${showtimeId}/seats`;
            } else {
                window.location.href = `/booking/movie/${movieId}/showtimes?date=${date}`;
            }
        }
        
        // Load showtimes when movie and date are selected
        document.getElementById('quick-movie-select')?.addEventListener('change', function() {
            const movieId = this.value;
            const date = document.getElementById('quick-date-select')?.value;
            if (movieId && date) {
                loadQuickShowtimes(movieId, date);
            }
        });
        
        document.getElementById('quick-date-select')?.addEventListener('change', function() {
            const movieId = document.getElementById('quick-movie-select')?.value;
            const date = this.value;
            if (movieId && date) {
                loadQuickShowtimes(movieId, date);
            }
        });
        
        function loadQuickShowtimes(movieId, date) {
            const select = document.getElementById('quick-showtime-select');
            if (!select) return;
            
            select.disabled = true;
            select.innerHTML = '<option value="">Đang tải...</option>';
            
            fetch(`/api/booking/movie/${movieId}/showtimes?date=${date}`)
                .then(res => res.json())
                .then(data => {
                    select.innerHTML = '<option value="">-- Chọn suất chiếu --</option>';
                    if (data.success && data.data.length > 0) {
                        data.data.forEach(st => {
                            const option = document.createElement('option');
                            option.value = st.id;
                            option.textContent = `${st.time} - ${st.room_name} (${st.available_seats} ghế trống)`;
                            select.appendChild(option);
                        });
                    } else {
                        select.innerHTML = '<option value="">Không có suất chiếu</option>';
                    }
                    select.disabled = false;
                })
                .catch(err => {
                    select.innerHTML = '<option value="">Lỗi khi tải suất chiếu</option>';
                    select.disabled = false;
                });
        }
        
        // Close modal on outside click
        document.getElementById('quick-booking-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuickBooking();
            }
        });
    </script>

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