@extends('admin.layout')

@section('title', 'Chi Tiết Suất Chiếu')
@section('page-title', 'Chi Tiết Suất Chiếu')
@section('page-description', 'Xem thông tin chi tiết suất chiếu')

@section('content')
  <!-- Breadcrumb -->
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
      <li class="inline-flex items-center">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-home mr-2"></i>
          Trang chủ
        </a>
      </li>
      <li>
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <a href="{{ route('admin.suat-chieu.index') }}" class="ml-1 text-sm font-medium text-[#a6a6b0] hover:text-white md:ml-2">Suất chiếu</a>
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

  <!-- Action Buttons -->
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-2xl font-bold text-white">Suất Chiếu #{{ $suatChieu->id }}</h1>
      <p class="text-[#a6a6b0] mt-1">{{ $suatChieu->phim->ten_phim }} - {{ $suatChieu->phongChieu->ten_phong }}</p>
    </div>
    <div class="flex space-x-3">
      @if($suatChieu->thoi_gian_ket_thuc && $suatChieu->thoi_gian_ket_thuc->isPast())
        {{-- Suất chiếu đã kết thúc - ẩn nút sửa --}}
        <div class="px-4 py-2 bg-gray-500 text-white font-medium rounded-lg flex items-center cursor-not-allowed" title="Suất chiếu đã kết thúc, không thể chỉnh sửa">
          <i class="fas fa-lock mr-2"></i>Đã kết thúc
        </div>
      @else
      <a href="{{ route('admin.suat-chieu.edit', $suatChieu) }}" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
        <i class="fas fa-edit mr-2"></i>Chỉnh Sửa
      </a>
      @endif
      <a href="{{ route('admin.suat-chieu.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>Quay lại
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Thông Tin Cơ Bản -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <h3 class="text-lg font-semibold mb-4 text-white flex items-center">
        <i class="fas fa-info-circle mr-2 text-[#F53003]"></i>Thông Tin Cơ Bản
      </h3>
      <div class="space-y-4">
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">ID Suất Chiếu:</span>
          <span class="text-white font-semibold">#{{ $suatChieu->id }}</span>
        </div>
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Phim:</span>
          <span class="text-white">{{ $suatChieu->phim->ten_phim }}</span>
        </div>
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Phòng Chiếu:</span>
          <span class="text-white">{{ $suatChieu->phongChieu->ten_phong }}</span>
        </div>
        <div class="flex justify-between py-3">
          <span class="font-medium text-[#a6a6b0]">Trạng Thái:</span>
          <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
            @if($suatChieu->status === 'coming') bg-blue-100 text-blue-800
            @elseif($suatChieu->status === 'ongoing') bg-green-100 text-green-800
            @else bg-gray-100 text-gray-800
            @endif">
            <i class="fas fa-{{ $suatChieu->status === 'coming' ? 'clock' : ($suatChieu->status === 'ongoing' ? 'play-circle' : 'check-circle') }} mr-1"></i>
            @if($suatChieu->status === 'coming') Sắp chiếu
            @elseif($suatChieu->status === 'ongoing') Đang chiếu
            @else Đã kết thúc
            @endif
          </span>
        </div>
      </div>
    </div>

    <!-- Thông Tin Thời Gian -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <h3 class="text-lg font-semibold mb-4 text-white flex items-center">
        <i class="fas fa-clock mr-2 text-[#F53003]"></i>Thông Tin Thời Gian
      </h3>
      <div class="space-y-4">
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Bắt Đầu:</span>
          <span class="text-white">{{ $suatChieu->start_time->format('d/m/Y H:i') }}</span>
        </div>
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Kết Thúc:</span>
          <span class="text-white">{{ $suatChieu->end_time->format('d/m/Y H:i') }}</span>
        </div>
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Thời Lượng:</span>
          <span class="text-white">{{ $suatChieu->start_time->diffInMinutes($suatChieu->end_time) }} phút</span>
        </div>
        <div class="flex justify-between py-3">
          <span class="font-medium text-[#a6a6b0]">Trạng Thái:</span>
          <span class="text-white">
            @if($suatChieu->start_time > now())
              <span class="text-blue-400">Sắp chiếu</span>
            @elseif($suatChieu->end_time < now())
              <span class="text-gray-400">Đã kết thúc</span>
            @else
              <span class="text-green-400">Đang chiếu</span>
            @endif
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <!-- Thông Tin Phòng Chiếu -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <h3 class="text-lg font-semibold mb-4 text-white flex items-center">
        <i class="fas fa-building mr-2 text-[#F53003]"></i>Thông Tin Phòng Chiếu
      </h3>
      <div class="space-y-4">
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Tên Phòng:</span>
          <span class="text-white">{{ $suatChieu->phongChieu->ten_phong }}</span>
        </div>
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Số Hàng:</span>
          <span class="text-white">{{ $suatChieu->phongChieu->so_hang }}</span>
        </div>
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Số Cột:</span>
          <span class="text-white">{{ $suatChieu->phongChieu->so_cot }}</span>
        </div>
        <div class="flex justify-between py-3">
          <span class="font-medium text-[#a6a6b0]">Sức Chứa:</span>
          <span class="text-white">{{ $suatChieu->phongChieu->suc_chua }} chỗ</span>
        </div>
      </div>
    </div>

    <!-- Thống Kê Đặt Vé -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <h3 class="text-lg font-semibold mb-4 text-white flex items-center">
        <i class="fas fa-chart-bar mr-2 text-[#F53003]"></i>Thống Kê Đặt Vé
      </h3>
      <div class="space-y-4">
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Tổng Ghế:</span>
          <span class="text-white">{{ $suatChieu->phongChieu->suc_chua }}</span>
        </div>
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Đã Đặt:</span>
          <span class="text-white">{{ $suatChieu->datVe->count() }}</span>
        </div>
        <div class="flex justify-between py-3 border-b border-[#262833]">
          <span class="font-medium text-[#a6a6b0]">Còn Trống:</span>
          <span class="text-white">{{ $suatChieu->phongChieu->suc_chua - $suatChieu->datVe->count() }}</span>
        </div>
        <div class="flex justify-between py-3">
          <span class="font-medium text-[#a6a6b0]">Tỷ Lệ Lấp Đầy:</span>
          <span class="text-white">
            @php
              $fillRate = $suatChieu->phongChieu->suc_chua > 0 ? round(($suatChieu->datVe->count() / $suatChieu->phongChieu->suc_chua) * 100, 1) : 0;
            @endphp
            {{ $fillRate }}%
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Danh Sách Vé Đã Đặt -->
  <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 mt-6">
    <h3 class="text-lg font-semibold mb-4 text-white flex items-center">
      <i class="fas fa-ticket-alt mr-2 text-[#F53003]"></i>Danh Sách Vé Đã Đặt
    </h3>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-[#262833]">
        <thead class="bg-[#1a1d24]">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">ID Vé</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Khách Hàng</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số Điện Thoại</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Tổng Tiền</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Ngày Đặt</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng Thái</th>
          </tr>
        </thead>
        <tbody class="bg-[#151822] divide-y divide-[#262833]">
          @forelse($suatChieu->datVe as $datVe)
          <tr class="hover:bg-[#1a1d24] transition-colors duration-200">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">#{{ $datVe->id }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $datVe->ten_khach_hang ?? 'N/A' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $datVe->so_dien_thoai ?? 'N/A' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ number_format($datVe->tong_tien) }} VNĐ</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $datVe->created_at->format('d/m/Y H:i') }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $datVe->trang_thai ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                {{ $datVe->trang_thai ? 'Đã thanh toán' : 'Chưa thanh toán' }}
              </span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="px-6 py-4 text-center text-sm text-[#a6a6b0]">Chưa có vé đặt nào cho suất chiếu này</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection