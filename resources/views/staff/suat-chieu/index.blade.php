@extends('layouts.admin')

@section('title', 'Xem Suất Chiếu - Staff')
@section('page-title', 'Xem Suất Chiếu')
@section('page-description', 'Xem danh sách các suất chiếu trong hệ thống')

@section('content')
  <!-- Additional CSS for better styling -->
  <style>
    /* Enhanced dropdown styling */
    select:focus {
      box-shadow: 0 0 0 2px rgba(245, 48, 3, 0.2) !important;
      border-color: #F53003 !important;
    }
    
    select option:checked {
      background-color: #F53003 !important;
      color: white !important;
    }
    
    /* Better hover effects */
    .hover\:border-\[\#F53003\]:hover {
      border-color: #F53003 !important;
    }
    
    /* Chỉ giới hạn z-index cho các dropdown trong filter bar */
    .filter-bar .relative {
      z-index: 10;
      overflow: visible;
    }
  </style>

  <!-- Breadcrumb -->
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
      <li class="inline-flex items-center">
        <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-home mr-2"></i>
          Trang chủ
        </a>
      </li>
      <li aria-current="page">
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Suất chiếu</span>
        </div>
      </li>
    </ol>
  </nav>

  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-white">Danh sách Suất Chiếu</h1>
        <p class="text-[#a6a6b0] mt-1">Xem tất cả các suất chiếu trong hệ thống</p>
      </div>
    </div>

    @if(session('success'))
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
          <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <title>Close</title>
            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
          </svg>
        </span>
      </div>
    @endif

    @if(session('error'))
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
          <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <title>Close</title>
            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
          </svg>
        </span>
      </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4 mb-6 filter-bar">
      <form method="GET" action="{{ route('staff.suat-chieu.index') }}" class="space-y-4">
        <!-- Main Filters Row -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Date Range -->
          <div class="md:col-span-2 grid grid-cols-2 gap-2">
            <div>
              <label for="tu_ngay" class="block text-xs font-medium text-[#a6a6b0] mb-1">Từ ngày</label>
              <input type="date" 
                     id="tu_ngay" 
                     name="tu_ngay" 
                     value="{{ request('tu_ngay') }}"
                     class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
            </div>
            <div>
              <label for="den_ngay" class="block text-xs font-medium text-[#a6a6b0] mb-1">Đến ngày</label>
              <input type="date" 
                     id="den_ngay" 
                     name="den_ngay" 
                     value="{{ request('den_ngay') }}"
                     class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
            </div>
          </div>
          
          <!-- Movie Filter -->
          <div>
            <label for="phim_id" class="block text-xs font-medium text-[#a6a6b0] mb-1">Phim</label>
            <div class="relative">
              <select id="phim_id" 
                      name="phim_id" 
                      class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent appearance-none cursor-pointer hover:border-[#F53003] transition-colors">
                <option value="" class="bg-[#1a1d24] text-white">Tất cả phim</option>
                @foreach($phim as $movie)
                  <option value="{{ $movie->id }}" {{ request('phim_id') == $movie->id ? 'selected' : '' }} class="bg-[#1a1d24] text-white">
                    {{ $movie->ten_phim }}
                  </option>
                @endforeach
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fas fa-chevron-down text-[#a6a6b0] text-xs"></i>
              </div>
            </div>
          </div>
          
          <!-- Room Filter -->
          <div>
            <label for="phong_id" class="block text-xs font-medium text-[#a6a6b0] mb-1">Phòng</label>
            <div class="relative">
              <select id="phong_id" 
                      name="phong_id" 
                      class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent appearance-none cursor-pointer hover:border-[#F53003] transition-colors">
                <option value="" class="bg-[#1a1d24] text-white">Tất cả phòng</option>
                  @foreach($phongChieu as $room)
                    <option value="{{ $room->id }}" {{ request('phong_id') == $room->id ? 'selected' : '' }} class="bg-[#1a1d24] text-white">
                      {{ $room->name }} ({{ $room->type }})
                    </option>
                  @endforeach
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fas fa-chevron-down text-[#a6a6b0] text-xs"></i>
              </div>
            </div>
          </div>
          
          <!-- Status Filter -->
          <div>
            <label for="status" class="block text-xs font-medium text-[#a6a6b0] mb-1">Trạng thái</label>
            <div class="relative">
              <select id="status" 
                      name="status" 
                      class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent appearance-none cursor-pointer hover:border-[#F53003] transition-colors">
                <option value="" class="bg-[#1a1d24] text-white">Tất cả</option>
                <option value="coming" {{ request('status') === 'coming' ? 'selected' : '' }} class="bg-[#1a1d24] text-white">Sắp chiếu</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }} class="bg-[#1a1d24] text-white">Đang chiếu</option>
                <option value="finished" {{ request('status') === 'finished' ? 'selected' : '' }} class="bg-[#1a1d24] text-white">Đã kết thúc</option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fas fa-chevron-down text-[#a6a6b0] text-xs"></i>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Quick Filter Chips -->
        <div class="flex flex-wrap gap-2">
          <span class="text-xs text-[#a6a6b0] font-medium">Nhanh:</span>
          <a href="{{ request()->fullUrlWithQuery(['tu_ngay' => now()->format('Y-m-d'), 'den_ngay' => now()->format('Y-m-d')]) }}" 
             class="px-3 py-1 bg-[#1a1d24] border border-[#262833] rounded-full text-xs text-white hover:bg-[#222533] transition-colors">
            Hôm nay
          </a>
          <a href="{{ request()->fullUrlWithQuery(['tu_ngay' => now()->startOfWeek()->format('Y-m-d'), 'den_ngay' => now()->endOfWeek()->format('Y-m-d')]) }}" 
             class="px-3 py-1 bg-[#1a1d24] border border-[#262833] rounded-full text-xs text-white hover:bg-[#222533] transition-colors">
            Tuần này
          </a>
          <a href="{{ request()->fullUrlWithQuery(['tu_ngay' => now()->startOfWeek()->addDays(5)->format('Y-m-d'), 'den_ngay' => now()->endOfWeek()->format('Y-m-d')]) }}" 
             class="px-3 py-1 bg-[#1a1d24] border border-[#262833] rounded-full text-xs text-white hover:bg-[#222533] transition-colors">
            Cuối tuần
          </a>
          <a href="{{ request()->fullUrlWithQuery(['tu_ngay' => '', 'den_ngay' => '', 'phim_id' => '', 'phong_id' => '', 'status' => '']) }}" 
             class="px-3 py-1 bg-[#1a1d24] border border-[#262833] rounded-full text-xs text-white hover:bg-[#222533] transition-colors">
            <i class="fas fa-times mr-1"></i>Xóa lọc
          </a>
        </div>
        
        <!-- Sort Options -->
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <label for="sort_by" class="text-xs font-medium text-[#a6a6b0]">Sắp xếp:</label>
            <select id="sort_by" 
                    name="sort_by" 
                    class="px-3 py-1 bg-[#1a1d24] border border-[#262833] rounded text-xs text-white focus:outline-none focus:ring-1 focus:ring-[#F53003] hover:border-[#F53003] transition-colors">
              <option value="start_time" {{ request('sort_by') == 'start_time' ? 'selected' : '' }} class="bg-[#1a1d24] text-white">Thời gian</option>
              <option value="room_id" {{ request('sort_by') == 'room_id' ? 'selected' : '' }} class="bg-[#1a1d24] text-white">Phòng</option>
              <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }} class="bg-[#1a1d24] text-white">Trạng thái</option>
            </select>
            <select name="sort_order" 
                    class="px-3 py-1 bg-[#1a1d24] border border-[#262833] rounded text-xs text-white focus:outline-none focus:ring-1 focus:ring-[#F53003] hover:border-[#F53003] transition-colors">
              <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }} class="bg-[#1a1d24] text-white">Tăng dần</option>
              <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }} class="bg-[#1a1d24] text-white">Giảm dần</option>
            </select>
          </div>
          
        @if(request()->hasAny(['phim_id', 'phong_id', 'status', 'tu_ngay', 'den_ngay']))
            <span class="text-xs text-[#a6a6b0] flex items-center">
              <i class="fas fa-filter mr-1"></i>
              {{ collect(request()->only(['phim_id', 'phong_id', 'status', 'tu_ngay', 'den_ngay']))->filter()->count() }} bộ lọc
            </span>
          @endif
          </div>
      </form>
    </div>

    <!-- Results Summary -->
    <div class="flex items-center justify-between mb-4">
      <div class="text-sm text-[#a6a6b0]">
        Hiển thị {{ $suatChieu->firstItem() ?? 0 }} đến {{ $suatChieu->lastItem() ?? 0 }} trong {{ $suatChieu->total() }} kết quả
      </div>
      <div class="flex items-center gap-2">
        <label class="text-xs text-[#a6a6b0]">Hiển thị:</label>
        <select onchange="changePageSize(this.value)" class="px-2 py-1 bg-[#1a1d24] border border-[#262833] rounded text-xs text-white hover:border-[#F53003] transition-colors">
          <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }} class="bg-[#1a1d24] text-white">10</option>
          <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }} class="bg-[#1a1d24] text-white">25</option>
          <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }} class="bg-[#1a1d24] text-white">50</option>
        </select>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <!-- Sticky Header -->
          <thead class="bg-[#1a1d24] sticky top-0 z-20">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phim</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phòng</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời gian</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Bán vé</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Hành động</th>
            </tr>
          </thead>
          <tbody class="bg-[#151822] divide-y divide-[#262833]">
            @forelse($suatChieu as $suat)
            <tr class="hover:bg-[#1a1d24] transition-colors duration-150 border-l-2 border-transparent hover:border-[#F53003]">
              <td class="px-4 py-3">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-8 bg-[#262833] rounded flex items-center justify-center">
                    <i class="fas fa-film text-[#a6a6b0] text-sm"></i>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-white group cursor-pointer" title="{{ $suat->phim->dao_dien ?? 'N/A' }} • {{ $suat->phim->thoi_luong ?? 'N/A' }} phút">
                      {{ $suat->phim->ten_phim ?? 'N/A' }}
                    </div>
                    <div class="text-xs text-[#a6a6b0]">{{ $suat->phim->dao_dien ?? 'N/A' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm text-white">{{ $suat->phongChieu->name ?? 'N/A' }}</div>
                <div class="text-xs text-[#a6a6b0]">{{ $suat->phongChieu->type ?? 'N/A' }} • {{ $suat->phongChieu->capacity ?? 'N/A' }} ghế</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm text-white">{{ \Carbon\Carbon::parse($suat->start_time)->format('d/m/Y • H:i') }}</div>
                <div class="text-xs text-[#a6a6b0]">{{ \Carbon\Carbon::parse($suat->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($suat->end_time)->format('H:i') }}</div>
                <div class="text-xs text-[#a6a6b0]">
                  @php
                    $now = now();
                    $start = \Carbon\Carbon::parse($suat->start_time);
                    $end = \Carbon\Carbon::parse($suat->end_time);
                    
                    if ($now->lt($start)) {
                      $diff = $now->diffForHumans($start, true);
                      echo "Còn {$diff}";
                    } elseif ($now->between($start, $end)) {
                      echo "Đang chiếu";
                    } else {
                      $diff = $now->diffForHumans($end, true);
                      echo "Đã kết thúc {$diff}";
                    }
                  @endphp
                </div>
              </td>
              <td class="px-4 py-3">
                @if($suat->status === 'coming')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                    <i class="fas fa-clock mr-1"></i>
                    Sắp chiếu
                  </span>
                @elseif($suat->status === 'ongoing')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400 border border-green-500/30">
                    <i class="fas fa-play-circle mr-1"></i>
                    Đang chiếu
                  </span>
                @else
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-400 border border-gray-500/30">
                    <i class="fas fa-check-circle mr-1"></i>
                    Đã kết thúc
                  </span>
                @endif
              </td>
              <td class="px-4 py-3">
                @php
                  $totalSeats = $suat->phongChieu->capacity ?? 0;
                  $soldSeats = rand(0, $totalSeats); // Mock data - replace with actual booking count
                  $percentage = $totalSeats > 0 ? round(($soldSeats / $totalSeats) * 100) : 0;
                @endphp
                <div class="text-sm text-white">{{ $soldSeats }}/{{ $totalSeats }}</div>
                <div class="w-full bg-gray-700 rounded-full h-1.5 mt-1">
                  <div class="bg-[#F53003] h-1.5 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                </div>
                <div class="text-xs text-[#a6a6b0]">{{ $percentage }}%</div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <a href="{{ route('staff.suat-chieu.show', $suat) }}" 
                     class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors duration-200" 
                     title="Xem chi tiết">
                    <i class="fas fa-eye mr-1"></i>
                    Xem chi tiết
                  </a>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                  <i class="fas fa-calendar-times text-4xl text-[#a6a6b0] mb-4"></i>
                  <h3 class="text-lg font-medium text-white mb-2">Không có suất chiếu nào</h3>
                  <p class="text-sm text-[#a6a6b0] mb-4">Chưa có suất chiếu nào được tạo hoặc không khớp với bộ lọc</p>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    @if($suatChieu->hasPages())
    <div class="mt-6 flex items-center justify-between">
      <div class="text-sm text-[#a6a6b0]">
        Trang {{ $suatChieu->currentPage() }}/{{ $suatChieu->lastPage() }} • {{ $suatChieu->total() }} kết quả
      </div>
      <div class="flex items-center gap-2">
        {{ $suatChieu->appends(request()->query())->links() }}
      </div>
    </div>
    @endif
  </div>

<script>
// Page size change
function changePageSize(size) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', size);
    window.location.href = url.toString();
}

// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('form[method="GET"]');
    const filterInputs = filterForm.querySelectorAll('select, input[type="date"]');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            setTimeout(() => {
                filterForm.submit();
            }, 300);
        });
    });
    
    // Show active filters count
    const activeFilters = Array.from(filterInputs).filter(input => input.value && input.value !== '');
    const filterIndicator = document.querySelector('.text-xs.text-\\[\\#a6a6b0\\]');
    if (activeFilters.length > 0 && filterIndicator) {
        filterIndicator.innerHTML = `<i class="fas fa-filter mr-1"></i>Đang áp dụng ${activeFilters.length} bộ lọc`;
    }
});
</script>
@endsection
