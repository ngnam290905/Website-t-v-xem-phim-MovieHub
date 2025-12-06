@extends('layouts.app')

@section('title', 'MovieHub - Đặt vé xem phim')

@section('content')
<div class="min-h-screen bg-[#0d0f14]">
    <!-- Hero Section -->
    <section class="relative h-[70vh] overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-[#1a1d29] via-[#151822] to-[#0d0f14]"></div>
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(ellipse at 20% 10%, #F53003 0%, transparent 35%), radial-gradient(circle at 80% 30%, #ff7a5f 0%, transparent 25%), radial-gradient(circle at 50% 80%, #ffb199 0%, transparent 25%);"></div>
        <div class="relative max-w-7xl mx-auto px-4 h-full flex items-center">
            <div class="max-w-2xl text-white animate-fade-in">
                <h1 class="text-5xl md:text-6xl font-extrabold mb-6">
                    <span class="bg-gradient-to-r from-[#F53003] via-[#ff7a5f] to-[#ffa07a] bg-clip-text text-transparent">MovieHub</span>
                </h1>
                <p class="text-lg md:text-xl mb-8 text-gray-300 leading-relaxed">
                    Trải nghiệm điện ảnh đỉnh cao với hệ thống rạp hiện đại, ưu đãi hấp dẫn và thao tác đặt vé cực nhanh.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('movies.now-showing') }}" class="px-6 py-3 bg-[#F53003] hover:bg-[#e02a00] rounded-lg font-semibold transition-all duration-200 shadow-md shadow-[#F53003]/30">
                        Đặt vé ngay
                    </a>
                    <a href="{{ route('movies.showtimes') }}" class="px-6 py-3 border border-white/20 hover:border-white/40 text-white/90 hover:text-white rounded-lg font-semibold transition-all">
                        Lịch chiếu
                    </a>
                </div>
                <div class="mt-8 flex flex-wrap gap-2 text-xs">
                    <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10">Phim mới cập nhật</span>
                    <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10">Ưu đãi mỗi ngày</span>
                    <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10">Thanh toán nhanh</span>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-[#0d0f14] to-transparent"></div>
    </section>
    
<!-- Ticket Check Section (#ve) -->
<section id="ticket-check" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/70">
  <div class="w-[min(720px,92vw)] rounded-2xl border border-[#262833] bg-[#10131a] shadow-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-[#262833] bg-[#0c0f16]">
      <h3 class="text-white text-lg font-semibold">Kiểm tra vé</h3>
      <button type="button" id="ticket-close" class="text-[#a6a6b0] hover:text-white">×</button>
    </div>
    <div class="p-6 space-y-5">
      <div class="flex gap-3">
        <input id="ticket-id-input" type="text" placeholder="Nhập mã vé (ví dụ: 123 hoặc MV000123)" class="flex-1 bg-[#151822] border border-[#262833] text-white rounded-xl px-4 py-3 outline-none" />
        <button id="ticket-load" class="px-4 py-3 rounded-xl bg-gradient-to-r from-[#F53003] to-[#ff7849] text-white font-semibold">Xem vé</button>
      </div>
      <div id="ticket-error" class="hidden text-red-400 text-sm"></div>
      <div id="ticket-loading" class="hidden text-[#a6a6b0]">Đang tải vé...</div>
      <div id="ticket-view" class="hidden grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-3">
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <div class="text-[#a6a6b0]">Mã vé</div>
              <div id="t-code" class="text-white font-semibold">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Trạng thái thanh toán</div>
              <div id="t-status" class="text-white font-semibold">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Tên khách hàng</div>
              <div id="t-customer" class="text-white">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Ngày mua</div>
              <div id="t-created" class="text-white">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Phương thức thanh toán</div>
              <div id="t-method" class="text-white">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Giá vé</div>
              <div id="t-price" class="text-white">—</div>
            </div>
          </div>
          <div class="rounded-xl border border-[#262833] p-4">
            <div class="text-[#a6a6b0] text-sm mb-2">Suất chiếu</div>
            <div id="t-show" class="text-white">—</div>
          </div>
          <div class="rounded-xl border border-[#262833] p-4">
            <div class="text-[#a6a6b0] text-sm mb-2">Ghế</div>
            <div id="t-seats" class="text-white">—</div>
          </div>
        </div>
        <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-[#262833] p-4">
          <div class="w-44 h-44 bg-[#151822] rounded-md flex items-center justify-center">
            <div class="text-center">
              <p class="text-white font-mono text-lg font-bold" id="t-code-display">—</p>
              <p class="text-[#a6a6b0] text-xs mt-2">Mã vé</p>
            </div>
          </div>
          <div class="text-[#a6a6b0] text-xs">Xuất trình mã vé khi đến rạp</div>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function(){
      var overlay = document.getElementById('ticket-check');
      if(!overlay){return}
      var closeBtn = document.getElementById('ticket-close');
      var btn = document.getElementById('ticket-load');
      var input = document.getElementById('ticket-id-input');
      var err = document.getElementById('ticket-error');
      var loading = document.getElementById('ticket-loading');
      var view = document.getElementById('ticket-view');
      var codeEl = document.getElementById('t-code');
      var statusEl = document.getElementById('t-status');
      var customerEl = document.getElementById('t-customer');
      var showEl = document.getElementById('t-show');
      var seatsEl = document.getElementById('t-seats');
      var priceEl = document.getElementById('t-price');
      var createdEl = document.getElementById('t-created');
      var methodEl = document.getElementById('t-method');
      var codeDisplayEl = document.getElementById('t-code-display');

      function openOverlay(){ overlay.classList.remove('hidden'); overlay.classList.add('flex'); }
      function closeOverlay(){ overlay.classList.add('hidden'); overlay.classList.remove('flex'); }
      function parseId(raw){
        if(!raw) return null; raw = String(raw).trim();
        var m = raw.match(/(\d+)/); return m? m[1] : null;
      }
      function formatVND(x){ try{ return Number(x).toLocaleString('vi-VN') + ' đ'; }catch(e){ return x; }}
      function statusLabel(s){ return s==1? 'Đã thanh toán' : (s===0? 'Chờ thanh toán' : '—'); }
      function methodLabel(m){ return m==1? 'Thanh toán online' : (m==2? 'Thanh toán tại quầy' : '—'); }
      function initFromUrl(){
        if(location.hash === '#ve'){
          var params = new URLSearchParams(location.search);
          var id = parseId(params.get('id') || params.get('ticket'));
          var url = (window.location.origin||'') + '/ve' + (id? ('?id='+id) : '');
          window.location.replace(url);
          return;
        }
      }
      function render(t){
        codeEl.textContent = t.code || '—';
        statusEl.textContent = statusLabel(t.status);
        customerEl.textContent = t.customer && t.customer.name ? t.customer.name : '—';
        createdEl.textContent = t.created_at || '—';
        methodEl.textContent = methodLabel(t.payment_method);
        priceEl.textContent = formatVND(t.price || 0);
        var showParts = [];
        if(t.showtime){ if(t.showtime.movie) showParts.push(t.showtime.movie); if(t.showtime.room) showParts.push(t.showtime.room); if(t.showtime.start) showParts.push(t.showtime.start); }
        showEl.textContent = showParts.join(' • ');
        seatsEl.textContent = Array.isArray(t.seats) ? t.seats.join(', ') : '—';
        codeDisplayEl.textContent = t.code || '—';
      }
      function load(id){
        err.classList.add('hidden'); loading.classList.remove('hidden'); view.classList.add('hidden');
        fetch((window.location.origin||'') + '/api/ticket/' + id)
          .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
          .then(function(j){ if(!j.success) throw new Error(j.message||'Không tìm thấy'); render(j.ticket); view.classList.remove('hidden'); })
          .catch(function(e){ err.textContent = 'Lỗi: '+ e.message; err.classList.remove('hidden'); })
          .finally(function(){ loading.classList.add('hidden'); });
      }
      btn.addEventListener('click', function(){ var id = parseId(input.value); if(!id){ err.textContent='Vui lòng nhập mã vé hợp lệ'; err.classList.remove('hidden'); return;} load(id); });
      closeBtn.addEventListener('click', closeOverlay);
      window.addEventListener('hashchange', initFromUrl);
      initFromUrl();
    })();
  </script>
</section>

    <!-- Phim Hot Section -->
    <section class="py-16 max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-white mb-2">Phim Hot</h2>
                <p class="text-gray-400">Những bộ phim được yêu thích nhất</p>
            </div>
            <a href="{{ route('movies.hot') }}" class="text-[#F53003] hover:text-red-400 font-medium flex items-center gap-2">
                Xem tất cả
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            @forelse($hotMovies as $movie)
                <div class="group relative">
                    <div class="relative overflow-hidden rounded-xl bg-[#1a1d29]">
                        <x-image 
                            src="{{ $movie->poster_url }}" 
                            alt="{{ $movie->ten_phim }}"
                            class="w-full h-[300px] transition-transform duration-300 group-hover:scale-110"
                            aspectRatio="2/3"
                            quality="high"
                            :lazy="false"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute top-3 left-3 z-10 flex items-center gap-2">
                            <span class="px-2 py-1 rounded bg-black/60 text-white text-xs backdrop-blur">
                                {{ $movie->the_loai ?? 'Phim' }}
                            </span>
                            <span class="px-2 py-1 rounded bg-yellow-500 text-black text-xs font-semibold">{{ number_format($movie->diem_danh_gia ?? 8.5, 1) }}★</span>
                        </div>
                        <a href="{{ route('movies.show', $movie->id) }}" class="absolute bottom-4 left-4 right-4 w-full bg-[#F53003] hover:bg-red-600 text-white py-2 rounded-lg font-medium transition text-center opacity-0 group-hover:opacity-100">
                            Xem chi tiết
                        </a>
                        @if($movie->trang_thai === 'sap_chieu')
                            <div class="absolute top-3 right-3 bg-yellow-500 text-black px-3 py-1 rounded-full text-xs font-bold">
                                Sắp chiếu
                            </div>
                        @endif
                    </div>
                    <div class="mt-3">
                        <h3 class="font-semibold text-white truncate">{{ $movie->ten_phim }}</h3>
                        <div class="flex items-center gap-3 text-sm text-gray-400 mt-1">
                            <span>{{ $movie->do_dai ?? 120 }} phút</span>
                            <span>•</span>
                            <span>{{ $movie->the_loai ?? 'Hành động' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4"></path>
                        </svg>
                        <p>Hiện chưa có phim hot nào</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Phim Đang Chiếu Section -->
    <section class="py-16 bg-[#151822]">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Phim Đang Chiếu</h2>
                    <p class="text-gray-400">Những bộ phim đang chiếu tại rạp</p>
                </div>
                <a href="{{ route('movies.now-showing') }}" class="text-[#F53003] hover:text-red-400 font-medium flex items-center gap-2">
                    Xem tất cả
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($nowShowing as $movie)
                    <div class="group bg-[#1a1d29] rounded-xl overflow-hidden hover:transform hover:scale-[1.02] transition-all duration-300 border border-white/5">
                        <div class="relative">
                            <x-image 
                                src="{{ $movie->poster_url }}" 
                                alt="{{ $movie->ten_phim }}"
                                class="w-full h-[200px]"
                                aspectRatio="2/3"
                                quality="high"
                                :lazy="false"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div class="absolute bottom-4 left-4 right-4 flex gap-2">
                                    <a href="{{ route('movies.show', $movie->id) }}" class="flex-1 bg-white/20 backdrop-blur text-white py-2 rounded-lg text-center font-medium hover:bg-white/30 transition">
                                        Xem chi tiết
                                    </a>
                                    <a href="{{ route('booking', $movie->id) }}" class="flex-1 bg-[#F53003] hover:bg-red-600 text-white py-2 rounded-lg text-center font-medium transition">
                                        Đặt vé
                                    </a>
                                </div>
                            </div>
                            <div class="absolute top-3 left-3 px-2 py-1 rounded bg-black/60 text-white text-xs">{{ $movie->the_loai ?? 'Phim' }}</div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-white text-lg mb-2">{{ $movie->ten_phim }}</h3>
                            <div class="flex items-center gap-3 text-sm text-gray-400">
                                <span>{{ $movie->do_dai ?? 120 }} phút</span>
                                <span>•</span>
                                <span>{{ $movie->the_loai ?? 'Hành động' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div class="text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <p>Hiện chưa có phim nào đang chiếu</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Phim Sắp Chiếu Section -->
    <section class="py-16 max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-white mb-2">Phim Sắp Chiếu</h2>
                <p class="text-gray-400">Những bộ phim đáng mong đợi nhất</p>
            </div>
            <a href="{{ route('movies.coming-soon') }}" class="text-[#F53003] hover:text-red-400 font-medium flex items-center gap-2">
                Xem tất cả
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($comingSoon as $movie)
                <div class="group bg-[#1a1d29] rounded-xl overflow-hidden hover:transform hover:scale-[1.02] transition-all duration-300 border border-white/5">
                    <div class="relative">
                        <x-image 
                            src="{{ $movie->poster_url }}" 
                            alt="{{ $movie->ten_phim }}"
                            class="w-full h-[200px]"
                            aspectRatio="2/3"
                            quality="high"
                            :lazy="false"
                        />
                        <div class="absolute top-3 right-3 bg-yellow-500 text-black px-3 py-1 rounded-full text-xs font-bold">
                            Sắp chiếu
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="absolute bottom-4 left-4 right-4">
                                <a href="{{ route('movies.show', $movie->id) }}" class="w-full bg-[#F53003] hover:bg-red-600 text-white py-2 rounded-lg text-center font-medium transition">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-white text-lg mb-2">{{ $movie->ten_phim }}</h3>
                        <p class="text-gray-400 text-sm mb-3">Khởi chiếu: {{ $movie->ngay_khoi_chieu ? date('d/m/Y', strtotime($movie->ngay_khoi_chieu)) : 'Sắp tới' }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400 text-sm">{{ $movie->do_dai ?? 120 }} phút</span>
                            <button class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black rounded-lg font-medium transition">
                                Nhắc nhở
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p>Hiện chưa có phim sắp chiếu nào</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Tất Cả Phim Section -->
    <section class="py-16 bg-[#151822]">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-white mb-4">Tất Cả Phim</h2>
                <p class="text-gray-400">Khám phá toàn bộ bộ sưu tập phim của chúng tôi</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                @forelse($allMovies as $movie)
                    <div class="group">
                        <div class="relative overflow-hidden rounded-lg bg-[#1a1d29] border border-white/5">
                            <x-image 
                                src="{{ $movie->poster_url }}" 
                                alt="{{ $movie->ten_phim }}"
                                class="w-full h-[250px] transition-transform duration-300 group-hover:scale-110"
                                aspectRatio="2/3"
                                quality="high"
                                :lazy="false"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div class="absolute bottom-3 left-3 right-3">
                                    <a href="{{ route('movies.show', $movie->id) }}" class="w-full bg-[#F53003] hover:bg-red-600 text-white py-2 rounded text-center text-sm font-medium transition">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                            @if($movie->trang_thai === 'sap_chieu')
                                <div class="absolute top-2 right-2 bg-yellow-500 text-black px-2 py-1 rounded text-xs font-bold">
                                    Sắp chiếu
                                </div>
                            @elseif($movie->hot)
                                <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                    Hot
                                </div>
                            @endif
                        </div>
                        <div class="mt-2">
                            <h4 class="font-medium text-white text-sm truncate">{{ $movie->ten_phim }}</h4>
                            <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                                <span>{{ $movie->do_dai ?? 120 }} phút</span>
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                    <span>{{ number_format($movie->diem_danh_gia ?? 8.5, 1) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div class="text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p>Chưa có dữ liệu phim nào</p>
                        </div>
                    </div>
                @endforelse
            </div>
            
            @if($allMovies->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $allMovies->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
