@extends('admin.layout')

@section('title', 'Thống kê - Báo cáo theo phim')
@section('page-title', 'Thống kê - Báo cáo theo phim')
@section('page-description', 'Dashboard tổng hợp thống kê tất cả phim')

@section('content')
  <!-- Breadcrumb -->
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
      <li class="inline-flex items-center">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-home mr-2"></i>
          Dashboard
        </a>
      </li>
      <li aria-current="page">
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Thống kê phim</span>
        </div>
      </li>
    </ol>
  </nav>

  <div class="space-y-6">
    <!-- Filters -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
      <div class="flex flex-wrap items-center gap-4">
        <div>
          <label class="text-xs text-[#a6a6b0] mb-1 block">Khoảng thời gian</label>
          <select id="period-filter" class="px-3 py-2 rounded-lg border border-[#2f3240] bg-[#0f0f12] text-white text-sm focus:outline-none focus:border-[#FF784E]">
            <option value="all" {{ $data['period'] === 'all' ? 'selected' : '' }}>Tất cả thời gian</option>
            <option value="today" {{ $data['period'] === 'today' ? 'selected' : '' }}>Hôm nay</option>
            <option value="week" {{ $data['period'] === 'week' ? 'selected' : '' }}>Tuần này</option>
            <option value="month" {{ $data['period'] === 'month' ? 'selected' : '' }}>Tháng này</option>
            <option value="year" {{ $data['period'] === 'year' ? 'selected' : '' }}>Năm nay</option>
            <option value="custom">Tùy chọn</option>
          </select>
        </div>

        <div id="custom-date-range" class="hidden flex items-end gap-2">
          <div>
            <label class="text-xs text-[#a6a6b0] mb-1 block">Từ ngày</label>
            <input type="date" id="start-date" value="{{ $data['date_range']['start'] }}" class="px-3 py-2 rounded-lg border border-[#2f3240] bg-[#0f0f12] text-white text-sm focus:outline-none focus:border-[#FF784E]">
          </div>
          <div>
            <label class="text-xs text-[#a6a6b0] mb-1 block">Đến ngày</label>
            <input type="date" id="end-date" value="{{ $data['date_range']['end'] }}" class="px-3 py-2 rounded-lg border border-[#2f3240] bg-[#0f0f12] text-white text-sm focus:outline-none focus:border-[#FF784E]">
          </div>
          <button id="apply-date-range" class="px-4 py-2 rounded-lg bg-blue-600/20 text-blue-300 text-sm hover:bg-blue-600/30">
            Áp dụng
          </button>
        </div>

        <div>
          <label class="text-xs text-[#a6a6b0] mb-1 block">Sắp xếp theo</label>
          <select id="sort-by" class="px-3 py-2 rounded-lg border border-[#2f3240] bg-[#0f0f12] text-white text-sm focus:outline-none focus:border-[#FF784E]">
            <option value="tickets" {{ $data['sort_by'] === 'tickets' ? 'selected' : '' }}>Số vé bán</option>
            <option value="showtimes" {{ $data['sort_by'] === 'showtimes' ? 'selected' : '' }}>Số suất chiếu</option>
          </select>
        </div>

        <div>
          <label class="text-xs text-[#a6a6b0] mb-1 block">Thứ tự</label>
          <select id="sort-order" class="px-3 py-2 rounded-lg border border-[#2f3240] bg-[#0f0f12] text-white text-sm focus:outline-none focus:border-[#FF784E]">
            <option value="desc" {{ $data['sort_order'] === 'desc' ? 'selected' : '' }}>Giảm dần</option>
            <option value="asc" {{ $data['sort_order'] === 'asc' ? 'selected' : '' }}>Tăng dần</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
        <div class="flex items-center justify-between mb-2">
          <div class="text-xs text-[#a6a6b0]">Tổng số phim</div>
          <i class="fas fa-film text-[#FF784E]"></i>
        </div>
        <div class="text-2xl font-bold text-white">{{ number_format($data['summary']['total_movies']) }}</div>
      </div>

      <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
        <div class="flex items-center justify-between mb-2">
          <div class="text-xs text-[#a6a6b0]">Tổng suất chiếu</div>
          <i class="fas fa-calendar-alt text-blue-400"></i>
        </div>
        <div class="text-2xl font-bold text-white">{{ number_format($data['summary']['total_showtimes']) }}</div>
      </div>

      <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
        <div class="flex items-center justify-between mb-2">
          <div class="text-xs text-[#a6a6b0]">Tổng vé bán</div>
          <i class="fas fa-ticket-alt text-green-400"></i>
        </div>
        <div class="text-2xl font-bold text-white">{{ number_format($data['summary']['total_tickets_sold']) }}</div>
      </div>
    </div>

    <!-- Movies Table -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
      <h2 class="text-lg font-semibold text-white mb-4">Danh sách phim</h2>
      
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-[#1a1d24]">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Phim</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Suất chiếu</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Vé bán</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#262833]">
            @forelse($data['movies'] as $movie)
              <tr class="hover:bg-[#1a1d24]">
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    @if($movie['poster'])
                      <img src="{{ $movie['poster'] ?? asset('images/no-poster.svg') }}" alt="{{ $movie['ten_phim'] }}" class="w-12 h-16 object-cover rounded" onerror="this.src='{{ asset('images/no-poster.svg') }}'">
                    @else
                      <div class="w-12 h-16 bg-[#0f0f12] rounded flex items-center justify-center">
                        <i class="fas fa-image text-[#a6a6b0]"></i>
                      </div>
                    @endif
                    <div>
                      <div class="text-white font-medium">{{ $movie['ten_phim'] }}</div>
                      <div class="text-xs text-[#a6a6b0]">
                        @switch($movie['trang_thai'])
                          @case('dang_chieu') <span class="text-green-400">Đang chiếu</span> @break
                          @case('sap_chieu') <span class="text-yellow-400">Sắp chiếu</span> @break
                          @case('ngung_chieu') <span class="text-gray-400">Ngừng chiếu</span> @break
                        @endswitch
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-white">{{ number_format($movie['total_showtimes']) }}</td>
                <td class="px-4 py-3 text-white">{{ number_format($movie['total_tickets_sold']) }}</td>
                <td class="px-4 py-3">
                  <a href="{{ route('admin.movies.show', $movie['id']) }}" class="inline-flex items-center px-3 py-1.5 rounded bg-blue-600/20 text-blue-300 text-xs hover:bg-blue-600/30">
                    <i class="fas fa-eye mr-2"></i> Chi tiết
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-[#a6a6b0]">
                  <i class="fas fa-info-circle mb-2"></i>
                  <p>Không có dữ liệu thống kê</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('period-filter')?.addEventListener('change', function() {
      if (this.value === 'custom') {
        document.getElementById('custom-date-range').classList.remove('hidden');
      } else {
        document.getElementById('custom-date-range').classList.add('hidden');
        applyFilters();
      }
    });

    document.getElementById('apply-date-range')?.addEventListener('click', function() {
      applyFilters();
    });

    document.getElementById('sort-by')?.addEventListener('change', applyFilters);
    document.getElementById('sort-order')?.addEventListener('change', applyFilters);

    function applyFilters() {
      const period = document.getElementById('period-filter').value;
      const sortBy = document.getElementById('sort-by').value;
      const sortOrder = document.getElementById('sort-order').value;
      
      const url = new URL(window.location.href);
      url.searchParams.set('period', period);
      url.searchParams.set('sort_by', sortBy);
      url.searchParams.set('sort_order', sortOrder);

      if (period === 'custom') {
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;
        if (startDate) url.searchParams.set('start_date', startDate);
        if (endDate) url.searchParams.set('end_date', endDate);
      } else {
        url.searchParams.delete('start_date');
        url.searchParams.delete('end_date');
      }

      window.location.href = url.toString();
    }
  </script>
@endsection

