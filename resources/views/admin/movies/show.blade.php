@extends('admin.layout')

@section('title', 'Chi tiết phim')
@section('page-title', 'Chi tiết phim')
@section('page-description', $movie->ten_phim)

@section('content')
  <!-- Breadcrumb -->
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
      <li class="inline-flex items-center">
        <a href="{{ route('admin.movies.index') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-film mr-2"></i>
          Danh sách phim
        </a>
      </li>
      <li aria-current="page">
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Chi tiết</span>
        </div>
      </li>
    </ol>
  </nav>

  <div class="space-y-6">
    <!-- Header actions -->
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">{{ $movie->ten_phim }}</h1>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.movies.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]"><i class="fas fa-arrow-left mr-2"></i> Quay lại</a>
        @if(auth()->user() && in_array(optional(auth()->user()->vaiTro)->ten, ['admin','staff']))
          <a href="{{ route('admin.movies.edit', $movie) }}" class="inline-flex items-center px-3 py-2 rounded-lg bg-yellow-600/20 text-yellow-300 text-sm hover:bg-yellow-600/30"><i class="fas fa-edit mr-2"></i> Chỉnh sửa</a>
        @endif
      </div>
    </div>

    <!-- Main card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Poster -->
        <div>
          <div class="relative w-full overflow-hidden rounded-xl border border-[#262833] bg-[#0f0f12]">
            @if($movie->poster)
              @if(str_starts_with($movie->poster, 'http'))
                <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" class="w-full object-cover" style="aspect-ratio: 2/3" onerror="this.src='{{ asset('images/no-poster.svg') }}'">
              @else
                <img src="{{ asset('storage/' . $movie->poster) }}" alt="{{ $movie->ten_phim }}" class="w-full object-cover" style="aspect-ratio: 2/3" onerror="this.src='{{ asset('images/no-poster.svg') }}'">
              @endif
            @else
              <div class="w-full flex flex-col items-center justify-center text-[#a6a6b0]" style="aspect-ratio: 2/3; min-height: 400px;">
                <i class="fas fa-image text-5xl mb-4 opacity-50"></i>
                <p class="text-sm">Chưa có poster</p>
              </div>
            @endif
            <span class="absolute top-3 left-3 text-[10px] uppercase px-2 py-1 rounded-full font-semibold {{ $movie->trang_thai==='dang_chieu' ? 'bg-green-500/20 text-green-300' : ($movie->trang_thai==='sap_chieu' ? 'bg-yellow-500/20 text-yellow-300' : 'bg-gray-500/20 text-gray-300') }}">
              @switch($movie->trang_thai)
                @case('dang_chieu') Đang chiếu @break
                @case('sap_chieu') Sắp chiếu @break
                @case('ngung_chieu') Ngừng chiếu @break
                @default Khác
              @endswitch
            </span>
          </div>
        </div>

        <!-- Info -->
        <div class="lg:col-span-2 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-[#a6a6b0]">Tên phim</div>
              <div class="text-white font-medium">{{ $movie->ten_phim }} @if($movie->ten_goc)<span class="text-[#a6a6b0]">({{ $movie->ten_goc }})</span>@endif</div>
            </div>
            <div>
              <div class="text-xs text-[#a6a6b0]">Trạng thái</div>
              <div>
                <span class="text-[11px] uppercase px-2 py-1 rounded-full font-semibold {{ $movie->trang_thai==='dang_chieu' ? 'bg-green-500/20 text-green-300' : ($movie->trang_thai==='sap_chieu' ? 'bg-yellow-500/20 text-yellow-300' : 'bg-gray-500/20 text-gray-300') }}">
                  @switch($movie->trang_thai)
                    @case('dang_chieu') Đang chiếu @break
                    @case('sap_chieu') Sắp chiếu @break
                    @case('ngung_chieu') Ngừng chiếu @break
                    @default Khác
                  @endswitch
                </span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-[#a6a6b0]">Đạo diễn</div>
              <div class="text-white">{{ $movie->dao_dien }}</div>
            </div>
            <div>
              <div class="text-xs text-[#a6a6b0]">Độ dài</div>
              <div class="text-white">{{ $movie->formatted_duration }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-[#a6a6b0]">Thể loại</div>
              <div class="text-white">{{ $movie->the_loai ?: 'Chưa phân loại' }}</div>
            </div>
            <div>
              <div class="text-xs text-[#a6a6b0]">Quốc gia</div>
              <div class="text-white">{{ $movie->quoc_gia ?: 'Chưa xác định' }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-[#a6a6b0]">Ngôn ngữ</div>
              <div class="text-white">{{ $movie->ngon_ngu ?: 'Chưa xác định' }}</div>
            </div>
            <div>
              <div class="text-xs text-[#a6a6b0]">Độ tuổi</div>
              <div class="text-white">{{ $movie->do_tuoi ?: 'Chưa xác định' }}</div>
            </div>
          </div>

          @if($movie->ngay_khoi_chieu || $movie->ngay_ket_thuc)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <div class="text-xs text-[#a6a6b0]">Ngày khởi chiếu</div>
                <div class="text-white">{{ $movie->ngay_khoi_chieu ? $movie->ngay_khoi_chieu->format('d/m/Y') : 'Chưa xác định' }}</div>
              </div>
              <div>
                <div class="text-xs text-[#a6a6b0]">Ngày kết thúc</div>
                <div class="text-white">{{ $movie->ngay_ket_thuc ? $movie->ngay_ket_thuc->format('d/m/Y') : 'Chưa xác định' }}</div>
              </div>
            </div>
          @endif

          @if($movie->diem_danh_gia > 0)
            <div>
              <div class="text-xs text-[#a6a6b0]">Đánh giá</div>
              <div class="text-white">{{ $movie->formatted_rating }} <span class="text-[#a6a6b0]">({{ $movie->so_luot_danh_gia }} lượt)</span></div>
            </div>
          @endif

          <div>
            <div class="text-xs text-[#a6a6b0]">Diễn viên</div>
            <div class="text-white">{{ $movie->dien_vien }}</div>
          </div>

          @if($movie->trailer)
            <div>
              <div class="text-xs text-[#a6a6b0]">Trailer</div>
              <a href="{{ $movie->trailer }}" target="_blank" class="inline-flex items-center px-4 py-2 rounded-lg bg-red-600/20 text-red-300 text-sm hover:bg-red-600/30">
                <i class="fab fa-youtube mr-2"></i> Xem trailer
              </a>
            </div>
          @endif

          <div>
            <div class="text-xs text-[#a6a6b0] mb-1">Mô tả</div>
            <div class="border border-[#262833] rounded-lg p-3 bg-[#0f0f12] text-[#d7d7df]">{{ $movie->mo_ta }}</div>
          </div>
          @if(!empty($movie->mo_ta_ngan))
            <div>
              <div class="text-xs text-[#a6a6b0] mb-1">Mô tả ngắn</div>
              <div class="border border-[#262833] rounded-lg p-3 bg-[#0f0f12] text-[#d7d7df]">{{ $movie->mo_ta_ngan }}</div>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Showtimes -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
      <div class="mb-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-white">Lịch chiếu @if(isset($selectedDate))<span class="text-[#a6a6b0] text-sm">({{ $selectedDate->format('d/m/Y') }})</span>@endif</h2>
        </div>
        @if(isset($days) && count($days) > 0)
          <div class="flex flex-wrap gap-2 mt-3">
            @foreach($days as $day)
              @php
                $isActive = isset($selectedDate) && $day->isSameDay($selectedDate);
              @endphp
              <a href="{{ request()->fullUrlWithQuery(['date' => $day->toDateString()]) }}"
                 class="px-3 py-1.5 rounded-full text-xs border transition-colors {{ $isActive ? 'bg-blue-600/20 border-blue-500/40 text-blue-300' : 'bg-transparent border-[#2f3240] text-[#a6a6b0] hover:bg-[#1a1d24]' }}">
                {{ $day->format('d/m') }}
              </a>
            @endforeach
          </div>
        @endif
      </div>

      @php $listShowtimes = isset($suatChieu) ? $suatChieu : $movie->suatChieu; @endphp
      @if($listShowtimes->count() > 0)
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-[#1a1d24]">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Phòng chiếu</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Bắt đầu</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Kết thúc</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Trạng thái</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Sơ đồ</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#262833]">
              @foreach($listShowtimes as $showtime)
                <tr class="hover:bg-[#1a1d24]">
                  <td class="px-4 py-3 text-white">{{ $showtime->phongChieu->ten_phong ?? 'N/A' }}</td>
                  <td class="px-4 py-3 text-white">{{ $showtime->thoi_gian_bat_dau ? $showtime->thoi_gian_bat_dau->format('d/m/Y H:i') : 'N/A' }}</td>
                  <td class="px-4 py-3 text-white">{{ $showtime->thoi_gian_ket_thuc ? $showtime->thoi_gian_ket_thuc->format('d/m/Y H:i') : 'N/A' }}</td>
                  <td class="px-4 py-3">
                    @if($showtime->trang_thai)
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300">Hoạt động</span>
                    @else
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-300">Tạm dừng</span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    <button type="button" data-seat-url="{{ route('admin.showtimes.seats', $showtime->id) }}" class="view-seatmap-btn inline-flex items-center px-3 py-1.5 rounded bg-blue-600/20 text-blue-300 text-xs hover:bg-blue-600/30">
                      <i class="fas fa-th mr-2"></i> Xem sơ đồ
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-[#a6a6b0] flex items-center gap-2"><i class="fas fa-info-circle"></i> Không có lịch chiếu cho ngày đã chọn.</div>
      @endif
    </div>

  </div>
  <!-- Modal xem sơ đồ ghế -->
  <div id="seatmap-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 max-w-5xl mx-auto mt-10 bg-[#151822] border border-[#262833] rounded-xl shadow-xl">
      <div class="flex items-center justify-between px-4 py-3 border-b border-[#262833]">
        <h3 class="text-white font-semibold text-lg">Sơ đồ ghế</h3>
        <button type="button" id="seatmap-close" class="text-[#a6a6b0] hover:text-white"><i class="fas fa-times"></i></button>
      </div>
      <div class="p-4">
        <div id="seatmap-room" class="text-[#a6a6b0] mb-3 text-sm"></div>
        <div id="seatmap-grid" class="overflow-auto">
          <!-- Grid render here -->
        </div>
        <div class="mt-4 text-xs text-[#a6a6b0] flex items-center gap-4">
          <span><span class="inline-block w-4 h-4 align-middle rounded bg-green-500/40 mr-1"></span> Trống</span>
          <span><span class="inline-block w-4 h-4 align-middle rounded bg-gray-500/60 mr-1"></span> Đã đặt</span>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('seatmap-modal');
      const btnClose = document.getElementById('seatmap-close');
      const grid = document.getElementById('seatmap-grid');
      const roomInfo = document.getElementById('seatmap-room');

      function openModal() { modal.classList.remove('hidden'); }
      function closeModal() { modal.classList.add('hidden'); grid.innerHTML = ''; roomInfo.textContent=''; }
      if (btnClose) btnClose.addEventListener('click', closeModal);
      modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });

      function renderSeatMap(data) {
        roomInfo.textContent = 'Phòng: ' + (data.room?.ten_phong || data.room?.id || 'N/A');
        const seats = Array.isArray(data.seats) ? data.seats : [];

        // Group seats by row; index by explicit 'col' if provided, else parse from label
        const byRow = {};
        const rowMax = {}; // max col per row
        seats.forEach(s => {
          const r = parseInt(s.row, 10) || 0;
          let c = parseInt(s.col, 10);
          if (!Number.isInteger(c) || c <= 0) {
            const m = String(s.label || '').match(/(\d+)/);
            c = m ? parseInt(m[1], 10) : 0;
          }
          (byRow[r] ||= [])[c] = s;
          rowMax[r] = Math.max(rowMax[r] || 0, c);
        });

        const rows = Object.keys(byRow).map(n => parseInt(n, 10)).sort((a, b) => a - b);
        const container = document.createElement('div');
        container.className = 'inline-block p-3 bg-[#0f0f12] rounded-lg border border-[#262833]';

        rows.forEach(r => {
          const rowWrap = document.createElement('div');
          rowWrap.className = 'flex items-center mb-2';

          const label = document.createElement('div');
          label.className = 'w-8 text-right pr-2 text-[#a6a6b0] text-xs';
          label.textContent = String.fromCharCode(64 + Math.min(Math.max(r,1),26));
          rowWrap.appendChild(label);

          const seatLine = document.createElement('div');
          seatLine.className = 'flex gap-1';

          const maxCol = rowMax[r] || 0;
          for (let c = 1; c <= maxCol; c++) {
            const s = byRow[r][c];
            if (!s) {
              // Spacer for aisles/missing seats to preserve layout
              const spacer = document.createElement('div');
              spacer.className = 'w-6 h-6';
              seatLine.appendChild(spacer);
              continue;
            }
            const seat = document.createElement('div');
            seat.className = 'w-6 h-6 rounded flex items-center justify-center text-[10px] select-none ' + (s.booked ? 'bg-gray-600 text-white' : 'bg-green-600/50 text-white');
            seat.title = s.label;
            seat.textContent = String(s.label || '').replace(/^[A-Z]+/, '');
            seatLine.appendChild(seat);
          }

          rowWrap.appendChild(seatLine);
          container.appendChild(rowWrap);
        });

        grid.innerHTML = '';
        grid.appendChild(container);
      }

      document.querySelectorAll('.view-seatmap-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
          const url = this.getAttribute('data-seat-url');
          if (!url) return;
          try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status + ' ' + (res.statusText || ''));
            let data;
            try {
              data = await res.json();
            } catch (_) {
              const txt = await res.text();
              throw new Error('Invalid JSON: ' + (txt ? txt.slice(0, 200) + '...' : 'no body'));
            }
            renderSeatMap(data);
            openModal();
          } catch (e) {
            grid.innerHTML = '<div class="text-red-400">Không tải được sơ đồ ghế. Vui lòng thử lại.' + (e && e.message ? ' (' + e.message + ')' : '') + '</div>';
            openModal();
          }
        });
      });
    });

  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection
