@extends('layouts.admin')

@section('title', 'Chi tiết Ghế - Staff')
@section('page-title', 'Chi tiết Ghế')
@section('page-description', 'Xem thông tin chi tiết ghế')

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
      <li class="inline-flex items-center">
        <a href="{{ route('staff.ghe.index') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          Ghế
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
    <!-- Staff Notice -->
    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div>
          <h3 class="text-blue-400 font-semibold text-sm">Chế độ xem</h3>
          <p class="text-blue-200 text-sm mt-1">
            Bạn đang xem thông tin chi tiết ghế. Chỉ có thể xem thông tin, không thể chỉnh sửa.
          </p>
        </div>
      </div>
    </div>

    <!-- Back Button -->
    <div class="flex items-center gap-4">
      <a href="{{ route('staff.ghe.index') }}" class="inline-flex items-center px-4 py-2 bg-[#262833] hover:bg-[#3a3d4a] text-white rounded-lg transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>
        Quay lại
      </a>
      <h1 class="text-2xl font-bold text-white">Chi tiết Ghế</h1>
    </div>

    <!-- Ghe Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Info -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Basic Info -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h2 class="text-lg font-semibold text-white mb-4">Thông tin cơ bản</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Số ghế</label>
              <p class="text-white text-2xl font-bold">{{ $ghe->so_ghe }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Hàng</label>
              <p class="text-white text-xl">Hàng {{ $ghe->so_hang }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Phòng chiếu</label>
              <p class="text-white">{{ $ghe->phongChieu->ten_phong ?? 'N/A' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Sức chứa phòng</label>
              <p class="text-white">{{ $ghe->phongChieu->suc_chua ?? 'N/A' }} ghế</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Loại ghế</label>
              <p class="text-white">{{ $ghe->loaiGhe->ten_loai ?? 'N/A' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Hệ số giá</label>
              <p class="text-white">{{ $ghe->loaiGhe->he_so_gia ?? 'N/A' }}</p>
            </div>
          </div>
        </div>

        <!-- Status Info -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h2 class="text-lg font-semibold text-white mb-4">Trạng thái</h2>
          <div class="flex items-center gap-4">
            @if($ghe->trang_thai)
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                <i class="fas fa-check-circle mr-2"></i>
                Hoạt động
              </span>
            @else
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                <i class="fas fa-times-circle mr-2"></i>
                Tạm dừng
              </span>
            @endif
            <div class="text-sm text-[#a6a6b0]">
              Cập nhật lần cuối: {{ $ghe->updated_at->format('d/m/Y H:i') }}
            </div>
          </div>
        </div>

        <!-- Room Layout -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h2 class="text-lg font-semibold text-white mb-4">Sơ đồ phòng</h2>
          <div class="bg-[#1a1d24] rounded-lg p-4">
            <div class="text-center text-[#a6a6b0] mb-4">
              <i class="fas fa-chair text-2xl"></i>
              <p class="text-sm mt-2">Ghế {{ $ghe->so_ghe }} - Hàng {{ $ghe->so_hang }}</p>
            </div>
            <div class="grid grid-cols-8 gap-2 max-w-md mx-auto">
              @for($i = 1; $i <= 8; $i++)
                <div class="aspect-square bg-[#262833] rounded flex items-center justify-center text-xs {{ $i == 4 ? 'bg-blue-500 text-white' : 'text-[#a6a6b0]' }}">
                  {{ $i == 4 ? $ghe->so_ghe : $i }}
                </div>
              @endfor
            </div>
            <p class="text-xs text-[#a6a6b0] text-center mt-2">Ghế được đánh dấu màu xanh</p>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Seat Icon -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4">Biểu tượng ghế</h3>
          <div class="aspect-square bg-[#262833] rounded-lg flex items-center justify-center">
            <i class="fas fa-chair text-6xl text-[#a6a6b0]"></i>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4">Hành động</h3>
          <div class="space-y-3">
            <a href="{{ route('staff.ghe.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#262833] hover:bg-[#3a3d4a] text-white rounded-lg transition-colors">
              <i class="fas fa-list mr-2"></i>
              Danh sách ghế
            </a>
            <a href="{{ route('staff.suat-chieu.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
              <i class="fas fa-film mr-2"></i>
              Xem suất chiếu
            </a>
          </div>
        </div>

        <!-- Info -->
        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
              <h3 class="text-yellow-400 font-semibold text-sm">Thông báo</h3>
              <p class="text-yellow-200 text-sm mt-1">
                Bạn chỉ có thể xem thông tin ghế. Để chỉnh sửa, vui lòng liên hệ Admin.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
