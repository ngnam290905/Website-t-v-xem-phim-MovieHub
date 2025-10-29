@extends('layouts.admin')

@section('title', 'Chi tiết Suất Chiếu - Staff')
@section('page-title', 'Chi tiết Suất Chiếu')
@section('page-description', 'Xem thông tin chi tiết suất chiếu')

@section('content')
  <!-- Breadcrumb -->
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
      <li class="inline-flex items-center">
        <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-home mr-2"></i>
          Trang chủ
        </a>
      </li>
      <li>
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <a href="{{ route('staff.suat-chieu.index') }}" class="text-sm font-medium text-[#a6a6b0] hover:text-white">Suất chiếu</a>
        </div>
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
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-white">Chi tiết Suất Chiếu</h1>
        <p class="text-[#a6a6b0] mt-1">Thông tin chi tiết về suất chiếu</p>
      </div>
      <a href="{{ route('staff.suat-chieu.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center shadow-lg hover:shadow-xl">
        <i class="fas fa-arrow-left mr-2"></i>Quay lại
      </a>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Movie Information -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Movie Details -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <i class="fas fa-film mr-2 text-[#F53003]"></i>
            Thông tin phim
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Tên phim</label>
              <p class="text-white font-medium">{{ $suatChieu->phim->ten_phim ?? 'N/A' }}</p>
            </div>
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Đạo diễn</label>
              <p class="text-white">{{ $suatChieu->phim->dao_dien ?? 'N/A' }}</p>
            </div>
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời lượng</label>
              <p class="text-white">{{ $suatChieu->phim->thoi_luong ?? 'N/A' }} phút</p>
            </div>
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thể loại</label>
              <p class="text-white">{{ $suatChieu->phim->the_loai ?? 'N/A' }}</p>
            </div>
          </div>
        </div>

        <!-- Showtime Details -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <i class="fas fa-clock mr-2 text-[#F53003]"></i>
            Thông tin suất chiếu
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời gian bắt đầu</label>
              <p class="text-white font-medium">{{ \Carbon\Carbon::parse($suatChieu->start_time)->format('d/m/Y H:i') }}</p>
            </div>
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời gian kết thúc</label>
              <p class="text-white font-medium">{{ \Carbon\Carbon::parse($suatChieu->end_time)->format('d/m/Y H:i') }}</p>
            </div>
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</label>
              <div class="mt-1">
                @if($suatChieu->status === 'coming')
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                    <i class="fas fa-clock mr-1"></i>
                    Sắp chiếu
                  </span>
                @elseif($suatChieu->status === 'ongoing')
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-500/20 text-green-400 border border-green-500/30">
                    <i class="fas fa-play-circle mr-1"></i>
                    Đang chiếu
                  </span>
                @else
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-500/20 text-gray-400 border border-gray-500/30">
                    <i class="fas fa-check-circle mr-1"></i>
                    Đã kết thúc
                  </span>
                @endif
              </div>
            </div>
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời gian còn lại</label>
              <p class="text-white">
                @php
                  $now = now();
                  $start = \Carbon\Carbon::parse($suatChieu->start_time);
                  $end = \Carbon\Carbon::parse($suatChieu->end_time);
                  
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
              </p>
            </div>
          </div>
        </div>

        <!-- Room Information -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <i class="fas fa-door-open mr-2 text-[#F53003]"></i>
            Thông tin phòng chiếu
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Tên phòng</label>
              <p class="text-white font-medium">{{ $suatChieu->phongChieu->name ?? 'N/A' }}</p>
            </div>
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Loại phòng</label>
              <p class="text-white">{{ $suatChieu->phongChieu->type ?? 'N/A' }}</p>
            </div>
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Sức chứa</label>
              <p class="text-white">{{ $suatChieu->phongChieu->capacity ?? 'N/A' }} ghế</p>
            </div>
            <div>
              <label class="text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</label>
              <p class="text-white">{{ $suatChieu->phongChieu->status ?? 'N/A' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Booking Status -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <i class="fas fa-ticket-alt mr-2 text-[#F53003]"></i>
            Tình trạng bán vé
          </h3>
          @php
            $totalSeats = $suatChieu->phongChieu->capacity ?? 0;
            $soldSeats = rand(0, $totalSeats); // Mock data
            $availableSeats = $totalSeats - $soldSeats;
            $percentage = $totalSeats > 0 ? round(($soldSeats / $totalSeats) * 100) : 0;
          @endphp
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-sm text-[#a6a6b0]">Đã bán</span>
              <span class="text-white font-medium">{{ $soldSeats }}/{{ $totalSeats }}</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-3">
              <div class="bg-[#F53003] h-3 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-[#a6a6b0]">Còn trống</span>
              <span class="text-white font-medium">{{ $availableSeats }}</span>
            </div>
            <div class="text-center">
              <span class="text-2xl font-bold text-[#F53003]">{{ $percentage }}%</span>
              <p class="text-xs text-[#a6a6b0]">Tỷ lệ bán vé</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <i class="fas fa-tools mr-2 text-[#F53003]"></i>
            Thông tin bổ sung
          </h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-[#1a1d24] rounded-lg">
              <span class="text-sm text-[#a6a6b0]">ID Suất chiếu</span>
              <span class="text-white font-mono text-sm">#{{ $suatChieu->id }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-[#1a1d24] rounded-lg">
              <span class="text-sm text-[#a6a6b0]">Ngày tạo</span>
              <span class="text-white text-sm">{{ $suatChieu->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-[#1a1d24] rounded-lg">
              <span class="text-sm text-[#a6a6b0]">Cập nhật cuối</span>
              <span class="text-white text-sm">{{ $suatChieu->updated_at->format('d/m/Y') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
