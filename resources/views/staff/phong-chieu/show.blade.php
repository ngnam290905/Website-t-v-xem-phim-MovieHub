@extends('layouts.admin')

@section('title', 'Chi tiết phòng chiếu - Staff')
@section('page-title', 'Chi tiết phòng chiếu')
@section('page-description', 'Xem thông tin chi tiết phòng chiếu')

@section('content')
  <div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
      <div>
        <h1 class="text-2xl font-bold text-white">{{ $phongChieu->name }}</h1>
        <p class="text-[#a6a6b0] mt-1">{{ $phongChieu->description ?? 'Không có mô tả' }}</p>
      </div>
      <a href="{{ route('staff.phong-chieu.index') }}" 
         class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>Quay lại
      </a>
    </div>

    <!-- Room Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Basic Info -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-info-circle text-[#F53003] mr-2"></i>
          Thông tin cơ bản
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Tên phòng:</span>
            <span class="text-white">{{ $phongChieu->name }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Loại phòng:</span>
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
              @if($phongChieu->type === '2D') bg-blue-100 text-blue-800
              @elseif($phongChieu->type === '3D') bg-green-100 text-green-800
              @elseif($phongChieu->type === 'IMAX') bg-purple-100 text-purple-800
              @else bg-orange-100 text-orange-800
              @endif">
              {{ $phongChieu->type }}
            </span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Số ghế:</span>
            <span class="text-white">{{ $phongChieu->seats_count }} ghế</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Sơ đồ:</span>
            <span class="text-white">{{ $phongChieu->rows }} hàng × {{ $phongChieu->cols }} ghế</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="font-medium text-[#a6a6b0]">Trạng thái:</span>
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $phongChieu->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
              {{ $phongChieu->status === 'active' ? 'Hoạt động' : 'Tạm dừng' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Technical Specs -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-cogs text-[#F53003] mr-2"></i>
          Thông số kỹ thuật
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Âm thanh:</span>
            <span class="text-white">{{ $phongChieu->audio_system ?? 'Chưa cập nhật' }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Màn hình:</span>
            <span class="text-white">{{ $phongChieu->screen_type ?? 'Chưa cập nhật' }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Sức chứa:</span>
            <span class="text-white">{{ $phongChieu->capacity }} người</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="font-medium text-[#a6a6b0]">Ngày tạo:</span>
            <span class="text-white">{{ $phongChieu->created_at->format('d/m/Y') }}</span>
          </div>
        </div>
      </div>

      <!-- Statistics -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-chart-bar text-[#F53003] mr-2"></i>
          Thống kê
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Suất chiếu:</span>
            <span class="text-white">{{ $phongChieu->showtimes_count }} suất</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Ghế trống:</span>
            <span class="text-green-400">{{ $phongChieu->seats->where('status', 'available')->count() }} ghế</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Ghế bảo trì:</span>
            <span class="text-yellow-400">{{ $phongChieu->seats->where('status', 'maintenance')->count() }} ghế</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="font-medium text-[#a6a6b0]">Tỷ lệ sử dụng:</span>
            <span class="text-blue-400">{{ $phongChieu->showtimes_count > 0 ? round(($phongChieu->seats->where('status', 'unavailable')->count() / $phongChieu->seats_count) * 100, 1) : 0 }}%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Seat Map View -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
          <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-chair text-[#F53003] mr-2"></i>
            Sơ đồ ghế
          </h3>
          <p class="text-[#a6a6b0] mt-1">Xem sơ đồ ghế hiện tại</p>
        </div>
      </div>

      <!-- Seat Map -->
      <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-6 overflow-x-auto">
        <div class="flex justify-center mb-4">
          <div class="bg-[#262833] text-white px-4 py-2 rounded-lg text-sm font-medium">
            Màn hình
          </div>
        </div>
        
        <div id="seat-map" class="flex flex-col items-center space-y-1">
          @foreach($phongChieu->seats->groupBy('row_label') as $rowLabel => $seats)
            <div class="flex space-x-1 items-center">
              <span class="text-sm text-[#a6a6b0] w-6 text-center font-medium">{{ $rowLabel }}</span>
              @foreach($seats as $seat)
                <div class="w-8 h-8 rounded text-xs font-medium flex items-center justify-center
                         @if($seat->status === 'available') bg-green-600 text-white
                         @elseif($seat->status === 'unavailable') bg-red-600 text-white
                         @else bg-yellow-600 text-white
                         @endif">
                  {{ $seat->so_ghe }}
                </div>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>

      <!-- Seat Legend -->
      <div class="mt-4 flex flex-wrap gap-4 justify-center">
        <div class="flex items-center space-x-2">
          <div class="w-4 h-4 bg-green-600 rounded"></div>
          <span class="text-sm text-[#a6a6b0]">Có sẵn</span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-4 h-4 bg-red-600 rounded"></div>
          <span class="text-sm text-[#a6a6b0]">Đã đặt</span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-4 h-4 bg-yellow-600 rounded"></div>
          <span class="text-sm text-[#a6a6b0]">Bảo trì</span>
        </div>
      </div>
    </div>

    <!-- Upcoming Showtimes -->
    @if($phongChieu->showtimes->count() > 0)
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-calendar-alt text-[#F53003] mr-2"></i>
          Suất chiếu sắp tới
        </h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-[#262833]">
            <thead class="bg-[#1a1d24]">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Phim</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Thời gian</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Trạng thái</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#262833]">
              @foreach($phongChieu->showtimes->take(5) as $showtime)
                <tr>
                  <td class="px-4 py-3 text-sm text-white">{{ $showtime->movie->ten_phim }}</td>
                  <td class="px-4 py-3 text-sm text-white">
                    {{ $showtime->start_time->format('d/m/Y H:i') }} - {{ $showtime->end_time->format('H:i') }}
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                      @if($showtime->status === 'coming') bg-blue-100 text-blue-800
                      @elseif($showtime->status === 'ongoing') bg-green-100 text-green-800
                      @else bg-gray-100 text-gray-800
                      @endif">
                      @if($showtime->status === 'coming') Sắp chiếu
                      @elseif($showtime->status === 'ongoing') Đang chiếu
                      @else Đã kết thúc
                      @endif
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>
@endsection

