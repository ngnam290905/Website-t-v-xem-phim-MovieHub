@extends('layouts.main')

@section('title', $movie->ten_phim . ' - MovieHub')

@section('content')
<div class="min-h-screen bg-[#0F1117] pt-8">
  <div class="max-w-7xl mx-auto px-4">
    
    <!-- Breadcrumb -->
    <nav class="mb-6">
      <ol class="flex items-center gap-2 text-sm text-[#a6a6b0]">
        <li><a href="{{ route('home') }}" class="hover:text-[#F53003] transition-colors">Trang chủ</a></li>
        <li><i class="fas fa-chevron-right text-xs"></i></li>
        <li><a href="{{ route('movies.index') }}" class="hover:text-[#F53003] transition-colors">Phim</a></li>
        <li><i class="fas fa-chevron-right text-xs"></i></li>
        <li class="text-white">{{ $movie->ten_phim }}</li>
      </ol>
    </nav>

    <!-- Movie Header -->
    <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 md:p-8 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
        <!-- Poster -->
        <div class="md:col-span-1">
          <div class="relative group">
            <x-image 
              src="{{ $movie->poster_url ?? $movie->poster }}" 
              alt="{{ $movie->ten_phim }}"
              aspectRatio="2/3"
              class="w-full rounded-xl shadow-2xl"
              quality="high"
              :lazy="false"
            />
            @if($movie->diem_danh_gia)
              <div class="absolute top-4 right-4 bg-yellow-500 text-black px-3 py-1 rounded-full text-sm font-bold flex items-center gap-1">
                <i class="fas fa-star text-xs"></i>
                <span>{{ number_format($movie->diem_danh_gia, 1) }}</span>
              </div>
            @endif
            @if($movie->trang_thai === 'dang_chieu')
              <div class="absolute top-4 left-4 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                Đang chiếu
              </div>
            @elseif($movie->trang_thai === 'sap_chieu')
              <div class="absolute top-4 left-4 bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                Sắp chiếu
            </div>
            @endif
          </div>
        </div>
          
        <!-- Movie Info -->
        <div class="md:col-span-2">
          <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ $movie->ten_phim }}</h1>
          
          @if($movie->ten_goc)
            <p class="text-lg text-[#a6a6b0] mb-4 italic">{{ $movie->ten_goc }}</p>
          @endif

          <!-- Movie Details -->
          <div class="flex flex-wrap items-center gap-4 text-sm text-[#a6a6b0] mb-6">
            @if($movie->do_dai)
              <span class="flex items-center gap-2">
                <i class="far fa-clock text-[#FF784E]"></i>
                <span>{{ $movie->do_dai }} phút</span>
              </span>
            @endif
            @if($movie->do_tuoi)
              <span class="flex items-center gap-2">
                <i class="fas fa-user-shield text-[#FF784E]"></i>
                <span>{{ $movie->do_tuoi }}</span>
              </span>
            @endif
            @if($movie->the_loai)
              <span class="flex items-center gap-2">
                <i class="fas fa-tags text-[#FF784E]"></i>
                <span>{{ $movie->the_loai }}</span>
              </span>
            @endif
            @if($movie->quoc_gia)
              <span class="flex items-center gap-2">
                <i class="fas fa-globe text-[#FF784E]"></i>
                <span>{{ $movie->quoc_gia }}</span>
              </span>
            @endif
            @if($movie->ngay_khoi_chieu)
              <span class="flex items-center gap-2">
                <i class="far fa-calendar text-[#FF784E]"></i>
                <span>{{ $movie->ngay_khoi_chieu->format('d/m/Y') }}</span>
              </span>
            @endif
          </div>

          <!-- Director & Cast -->
          @if($movie->dao_dien)
            <div class="mb-4">
              <p class="text-sm text-[#a6a6b0] mb-1">Đạo diễn</p>
              <p class="text-white font-semibold">{{ $movie->dao_dien }}</p>
            </div>
          @endif

          @if($movie->dien_vien)
            <div class="mb-6">
              <p class="text-sm text-[#a6a6b0] mb-1">Diễn viên</p>
              <p class="text-white">{{ $movie->dien_vien }}</p>
            </div>
          @endif
            
          <!-- Description -->
          @if($movie->mo_ta)
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-white mb-2">Nội dung</h3>
              <p class="text-[#a6a6b0] leading-relaxed">{{ $movie->mo_ta }}</p>
            </div>
          @endif
            
          <!-- Action Buttons -->
          <div class="flex flex-wrap gap-4">
            <a href="{{ route('booking.showtimes', $movie->id) }}" 
               class="inline-flex items-center gap-2 bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
              <i class="fas fa-ticket-alt"></i>
              <span>Đặt vé</span>
            </a>
            @if($movie->trailer)
              <button onclick="openTrailer()" 
                      class="inline-flex items-center gap-2 border-2 border-[#2A2F3A] text-white px-6 py-3 rounded-lg font-semibold hover:border-[#F53003] transition-all">
                <i class="fas fa-play"></i>
                  <span>Xem trailer</span>
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-8">
      
      <!-- Trailer Section -->
        @if($movie->trailer)
          <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
            <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
          <i class="fas fa-film text-[#FF784E]"></i>
              <span>Trailer</span>
        </h2>
            <div class="relative aspect-video rounded-lg overflow-hidden">
            <iframe 
                src="{{ str_replace('watch?v=', 'embed/', $movie->trailer) }}" 
              title="Trailer {{ $movie->ten_phim }}"
              class="w-full h-full"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen>
            </iframe>
            </div>
          </div>
        @endif

        <!-- Related Movies -->
        @php
          $relatedMovies = App\Models\Phim::where('id', '!=', $movie->id)
            ->where(function($query) use ($movie) {
              if($movie->the_loai) {
                $query->where('the_loai', 'like', '%' . $movie->the_loai . '%');
              }
            })
            ->orWhere('dao_dien', $movie->dao_dien)
            ->where('id', '!=', $movie->id)
            ->where('trang_thai', 'dang_chieu')
            ->limit(6)
            ->get();
        @endphp

        @if($relatedMovies->count() > 0)
          <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
              <i class="fas fa-th-large text-[#FF784E]"></i>
              <span>Phim liên quan</span>
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              @foreach($relatedMovies as $related)
                <a href="{{ route('movie-detail', $related->id) }}" 
                   class="group bg-[#1a1d24] border border-[#2A2F3A] rounded-lg overflow-hidden hover:border-[#F53003] transition-all">
                  <img src="{{ $related->poster_url ?? $related->poster ?? asset('images/no-poster.svg') }}" 
                       alt="{{ $related->ten_phim }}" 
                       class="w-full aspect-[2/3] object-cover group-hover:scale-105 transition-transform">
                  <div class="p-3">
                    <h3 class="text-sm font-semibold text-white line-clamp-2 group-hover:text-[#F53003] transition-colors">
                      {{ $related->ten_phim }}
                    </h3>
                  </div>
                </a>
              @endforeach
              </div>
            </div>
          @endif
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
      
      <!-- Showtimes -->
        @php
          // Sử dụng dữ liệu từ controller, nếu không có thì query lại
          $showtimesByDate = $showtimesByDate ?? [];
          $availableDates = $availableDates ?? [];
          $selectedDate = $selectedDate ?? request('date', now()->format('Y-m-d'));
          
          // Nếu không có dữ liệu từ controller, query lại
          if (empty($showtimesByDate)) {
            $showtimes = App\Models\SuatChieu::where('id_phim', $movie->id)
              ->where('trang_thai', 1)
              ->where('thoi_gian_bat_dau', '>', now())
              ->orderBy('thoi_gian_bat_dau')
              ->with('phongChieu')
              ->get()
              ->groupBy(function($showtime) {
                return $showtime->thoi_gian_bat_dau->format('Y-m-d');
              });
            
            $showtimesByDate = $showtimes->toArray();
            $availableDates = $showtimes->keys()->take(7)->toArray();
            $selectedDate = request('date', $availableDates[0] ?? date('Y-m-d'));
          }
        @endphp

        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 sticky top-6">
          <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <i class="fas fa-calendar-alt text-[#FF784E]"></i>
          <span>Lịch chiếu</span>
        </h3>
        
          @if(count($availableDates) > 0)
        <!-- Date Selector -->
          <div class="mb-4 overflow-x-auto">
            <div class="flex gap-2 pb-2">
              @foreach($availableDates as $date)
                @php
                  $carbonDate = \Carbon\Carbon::parse($date);
                    $isSelected = $date === $selectedDate;
                @endphp
                <a href="{{ route('movie-detail', ['movie' => $movie->id, 'date' => $date]) }}"
                     class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-semibold transition-all
                   {{ $isSelected 
                       ? 'bg-gradient-to-r from-[#F53003] to-orange-400 text-white' 
                       : 'bg-[#1a1d24] text-[#a6a6b0] hover:bg-[#2A2F3A]' }}">
                  {{ $carbonDate->format('d/m') }}
                  @if($carbonDate->isToday())
                      <span class="text-xs">(Hôm nay)</span>
                  @elseif($carbonDate->isTomorrow())
                      <span class="text-xs">(Ngày mai)</span>
                  @endif
                </a>
              @endforeach
            </div>
          </div>
        
        <!-- Showtimes List -->
        <div class="space-y-3 max-h-[500px] overflow-y-auto">
              @php
                // Debug: Kiểm tra dữ liệu
                $hasShowtimes = isset($showtimesByDate[$selectedDate]) && is_array($showtimesByDate[$selectedDate]) && count($showtimesByDate[$selectedDate]) > 0;
              @endphp
              
              @if($hasShowtimes)
                @foreach($showtimesByDate[$selectedDate] as $showtime)
                  <div class="bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-4 hover:border-[#F53003] transition-colors">
                    <div class="flex items-center justify-between mb-3">
                      <div>
                        <p class="text-xl font-bold text-white">
                          {{ $showtime->thoi_gian_bat_dau->format('H:i') }}
                        </p>
                        <p class="text-xs text-[#a6a6b0]">
                          @if($showtime->phongChieu)
                            {{ $showtime->phongChieu->name ?? $showtime->phongChieu->ten_phong ?? 'N/A' }}
                          @else
                            Phòng chiếu
                          @endif
                        </p>
                      </div>
                      <div class="text-right">
                        <p class="text-sm font-semibold text-[#F53003] mb-2">Từ 50.000đ</p>
                        @auth
                          <a href="{{ route('booking.seats', $showtime->id) }}" 
                             class="inline-block bg-gradient-to-r from-[#F53003] to-orange-400 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:shadow-lg transition-all">
                            Chọn ghế
                          </a>
                        @else
                          <a href="{{ route('login.form') }}" 
                             class="inline-block bg-[#2A2F3A] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#3A3F4A] transition-all">
                            Đăng nhập
                          </a>
                        @endauth
                      </div>
                    </div>
                  </div>
                @endforeach
                    @else
                <div class="text-center py-8">
                  <p class="text-[#a6a6b0] mb-2">Không có suất chiếu cho ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</p>
                  @if(count($availableDates) > 0)
                    <p class="text-xs text-[#a6a6b0]">Vui lòng chọn ngày khác</p>
                    @endif
                </div>
              @endif
              </div>
          @else
            <div class="text-center py-8">
              <p class="text-[#a6a6b0]">Chưa có lịch chiếu</p>
            </div>
          @endif
        </div>

        <!-- Movie Stats -->
        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
          <h3 class="text-lg font-bold text-white mb-4">Thông tin</h3>
          <div class="space-y-3 text-sm">
            @if($movie->ngon_ngu)
              <div class="flex justify-between">
                <span class="text-[#a6a6b0]">Ngôn ngữ</span>
                <span class="text-white font-semibold">{{ $movie->ngon_ngu }}</span>
              </div>
            @endif
            @if($movie->so_luot_danh_gia)
              <div class="flex justify-between">
                <span class="text-[#a6a6b0]">Lượt đánh giá</span>
                <span class="text-white font-semibold">{{ number_format($movie->so_luot_danh_gia) }}</span>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
        </div>
      </div>
      
<!-- Trailer Modal -->
@if($movie->trailer)
  <div id="trailer-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/90 backdrop-blur-sm">
    <div class="relative w-full max-w-4xl mx-4">
      <button onclick="closeTrailer()" class="absolute -top-12 right-0 text-white hover:text-[#F53003] transition-colors">
        <i class="fas fa-times text-3xl"></i>
      </button>
      <div class="relative aspect-video rounded-lg overflow-hidden">
        <iframe 
          id="trailer-iframe"
          src="" 
          title="Trailer {{ $movie->ten_phim }}"
          class="w-full h-full"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen>
        </iframe>
      </div>
    </div>
  </div>
@endif

  <script>
  function openTrailer() {
    const modal = document.getElementById('trailer-modal');
    const iframe = document.getElementById('trailer-iframe');
    if (modal && iframe) {
      iframe.src = '{{ str_replace('watch?v=', 'embed/', $movie->trailer) }}';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
        }
  }

  function closeTrailer() {
    const modal = document.getElementById('trailer-modal');
    const iframe = document.getElementById('trailer-iframe');
    if (modal && iframe) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      iframe.src = '';
      document.body.style.overflow = 'auto';
    }
  }

  // Close modal on outside click
  document.getElementById('trailer-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
      closeTrailer();
        }
  });
  </script>
@endsection
