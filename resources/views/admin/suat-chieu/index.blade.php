@extends('admin.layout')

@section('title', 'Quản lý Suất Chiếu')
@section('page-title', 'Quản lý Suất Chiếu')
@section('page-description', 'Danh sách và quản lý các suất chiếu')

@section('content')
  

  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-white">Danh sách Suất Chiếu</h1>
        <p class="text-[#a6a6b0] mt-1">Quản lý tất cả các suất chiếu trong hệ thống</p>
      </div>
      <a href="{{ route('admin.suat-chieu.create') }}" class="bg-[#F53003] hover:bg-[#e02a00] text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center shadow-lg hover:shadow-xl">
        <i class="fas fa-plus mr-2"></i>Tạo Suất Chiếu Mới
      </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Tổng suất chiếu</div>
        <div class="text-2xl font-bold text-white mt-1">{{ $totalShowtimes ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Sắp chiếu</div>
        <div class="text-2xl font-bold text-blue-400 mt-1">{{ $comingCount ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Đang chiếu</div>
        <div class="text-2xl font-bold text-green-400 mt-1">{{ $ongoingCount ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Đã kết thúc</div>
        <div class="text-2xl font-bold text-gray-400 mt-1">{{ $finishedCount ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Trong hôm nay</div>
        <div class="text-2xl font-bold text-yellow-400 mt-1">{{ $todayCount ?? 0 }}</div>
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

    <!-- Sticky Filter Bar -->
    <div class="sticky top-0 z-30 bg-[#151822] border-b border-[#262833] p-4 mb-6">
      <form method="GET" action="{{ route('admin.suat-chieu.index') }}" class="space-y-4">
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
                      class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent appearance-none cursor-pointer"
                      style="color: white !important; background-color: #1a1d24 !important;">
                <option value="" style="background-color: #1a1d24 !important; color: white !important;">Tất cả phim</option>
                @foreach($phim as $movie)
                  <option value="{{ $movie->id }}" {{ request('phim_id') == $movie->id ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">
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
                      class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent appearance-none cursor-pointer"
                      style="color: white !important; background-color: #1a1d24 !important;">
                <option value="" style="background-color: #1a1d24 !important; color: white !important;">Tất cả phòng</option>
                @foreach($phongChieu as $room)
                  <option value="{{ $room->id }}" {{ request('phong_id') == $room->id ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">
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
                      class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent appearance-none cursor-pointer"
                      style="color: white !important; background-color: #1a1d24 !important;">
                <option value="" style="background-color: #1a1d24 !important; color: white !important;">Tất cả</option>
                <option value="coming" {{ request('status') === 'coming' ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">Sắp chiếu</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">Đang chiếu</option>
                <option value="finished" {{ request('status') === 'finished' ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">Đã kết thúc</option>
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
                    class="px-3 py-1 bg-[#1a1d24] border border-[#262833] rounded text-xs text-white focus:outline-none focus:ring-1 focus:ring-[#F53003]"
                    style="color: white !important; background-color: #1a1d24 !important;">
              <option value="start_time" {{ request('sort_by') == 'start_time' ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">Thời gian</option>
              <option value="room_id" {{ request('sort_by') == 'room_id' ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">Phòng</option>
              <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">Trạng thái</option>
            </select>
            <select name="sort_order" 
                    class="px-3 py-1 bg-[#1a1d24] border border-[#262833] rounded text-xs text-white focus:outline-none focus:ring-1 focus:ring-[#F53003]"
                    style="color: white !important; background-color: #1a1d24 !important;">
              <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">Tăng dần</option>
              <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }} style="background-color: #1a1d24 !important; color: white !important;">Giảm dần</option>
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
        <select onchange="changePageSize(this.value)" class="px-2 py-1 bg-[#1a1d24] border border-[#262833] rounded text-xs text-white">
          <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
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
              <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">
                <input type="checkbox" id="select-all" class="rounded border-[#262833] bg-[#1a1d24] text-[#F53003] focus:ring-[#F53003]">
              </th>
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
                <input type="checkbox" class="row-checkbox rounded border-[#262833] bg-[#1a1d24] text-[#F53003] focus:ring-[#F53003]" value="{{ $suat->id }}">
              </td>
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
                <div class="text-sm text-white">{{ $suat->room->name ?? 'N/A' }}</div>
                <div class="text-xs text-[#a6a6b0]">{{ $suat->room->type ?? 'N/A' }} • {{ $suat->room->capacity ?? 'N/A' }} ghế</div>
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
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    <i class="fas fa-clock mr-1"></i>
                    Sắp chiếu
                  </span>
                @elseif($suat->status === 'ongoing')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                    <i class="fas fa-play-circle mr-1"></i>
                    Đang chiếu
                  </span>
                @else
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                    <i class="fas fa-check-circle mr-1"></i>
                    Đã kết thúc
                  </span>
                @endif
              </td>
              <td class="px-4 py-3">
                @php
                  $totalSeats = $suat->room->capacity ?? 0;
                  $soldSeats = rand(0, $totalSeats); // Mock data - replace with actual booking count
                  $percentage = $totalSeats > 0 ? round(($soldSeats / $totalSeats) * 100) : 0;
                @endphp
                <div class="text-sm text-white">{{ $soldSeats }}/{{ $totalSeats }}</div>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                  <div class="bg-[#F53003] h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                </div>
                <div class="text-xs text-[#a6a6b0]">{{ $percentage }}%</div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <a href="{{ route('admin.suat-chieu.show', $suat) }}" 
                     class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors duration-200" 
                     title="Xem chi tiết">
                    <i class="fas fa-eye mr-1"></i>
                    Xem
                  </a>
                  @if(auth()->check() && request()->is('admin/*') && optional(auth()->user()->vaiTro)->ten === 'admin')
                  <a href="{{ route('admin.suat-chieu.edit', $suat) }}" 
                     class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-medium rounded-md transition-colors duration-200" 
                     title="Sửa lịch">
                    <i class="fas fa-edit mr-1"></i>
                    Sửa
                  </a>
                  <button onclick="confirmDelete({{ $suat->id }})" 
                          class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md transition-colors duration-200" 
                          title="Xóa">
                    <i class="fas fa-trash mr-1"></i>
                    Xóa
                  </button>
                  <div class="relative">
                    <button type="button" class="inline-flex items-center px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-md transition-colors duration-200" 
                            onclick="toggleDropdown({{ $suat->id }})" title="Thêm hành động">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div id="dropdown-{{ $suat->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                      <div class="py-1">
                        <button onclick="duplicateShowtime({{ $suat->id }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                          <i class="fas fa-copy mr-2"></i>Nhân bản
                        </button>
                        <button onclick="updateStatus({{ $suat->id }}, '{{ $suat->status === 'coming' ? 'ongoing' : ($suat->status === 'ongoing' ? 'finished' : 'coming') }}')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                          <i class="fas fa-arrow-right mr-2"></i>Cập nhật trạng thái
                        </button>
                      </div>
                    </div>
                  </div>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                  <i class="fas fa-calendar-times text-4xl text-[#a6a6b0] mb-4"></i>
                  <h3 class="text-lg font-medium text-white mb-2">Không có suất chiếu nào</h3>
                  <p class="text-sm text-[#a6a6b0] mb-4">Chưa có suất chiếu nào được tạo hoặc không khớp với bộ lọc</p>
                  <a href="{{ route('admin.suat-chieu.create') }}" class="bg-[#F53003] hover:bg-[#e02a00] text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i>Tạo Suất Chiếu Mới
                  </a>
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
// Dropdown toggle
function toggleDropdown(id) {
    const dropdown = document.getElementById(`dropdown-${id}`);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
    
    // Close all other dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== `dropdown-${id}`) {
            d.classList.add('hidden');
        }
    });
    
    // Toggle current dropdown
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Select all checkbox
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Page size change
function changePageSize(size) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', size);
    window.location.href = url.toString();
}

// Duplicate showtime
function duplicateShowtime(id) {
    if (confirm('Bạn có muốn nhân bản suất chiếu này?')) {
        fetch(`/admin/suat-chieu/${id}/duplicate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Có lỗi xảy ra: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi nhân bản suất chiếu');
        });
    }
}

// Confirm delete
function confirmDelete(id) {
    if (confirm('Bạn có chắc chắn muốn xóa suất chiếu này?\n\nHành động này không thể hoàn tác!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/suat-chieu/${id}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

// Update status
function updateStatus(id, status) {
    if (confirm('Bạn có chắc chắn muốn thay đổi trạng thái suất chiếu?')) {
        fetch(`/admin/suat-chieu/${id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Có lỗi xảy ra khi cập nhật trạng thái');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi cập nhật trạng thái');
        });
    }
}

// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('form[method="GET"]');
    const filterInputs = filterForm.querySelectorAll('select, input[type="date"]');
    
    // Auto-submit when filter values change
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
