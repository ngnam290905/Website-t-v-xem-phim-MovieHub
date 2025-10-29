@extends('layouts.admin')

@section('title', 'Bảng điều khiển - Admin')
@section('page-title', 'Bảng điều khiển')
@section('page-description', 'Tổng quan hệ thống quản lý rạp chiếu phim')

@section('content')
  <div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-[#F53003] to-orange-400 rounded-xl p-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold mb-2">Chào mừng đến với MovieHub Admin!</h1>
          <p class="text-white/90">Quản lý hệ thống rạp chiếu phim một cách hiệu quả</p>
        </div>
        <div class="hidden md:block">
          <i class="fas fa-film text-6xl text-white/20"></i>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-[#a6a6b0] mb-1">Tổng số phim</div>
            <div class="text-3xl font-bold text-white">{{ \App\Models\Movie::count() }}</div>
          </div>
          <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
            <i class="fas fa-film text-blue-400 text-xl"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-green-400">
          <i class="fas fa-arrow-up mr-1"></i>
          <span>+12% so với tháng trước</span>
        </div>
      </div>

      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-[#a6a6b0] mb-1">Suất chiếu hôm nay</div>
            <div class="text-3xl font-bold text-white">{{ \App\Models\SuatChieu::whereDate('start_time', today())->count() }}</div>
          </div>
          <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
            <i class="fas fa-calendar-alt text-green-400 text-xl"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-green-400">
          <i class="fas fa-arrow-up mr-1"></i>
          <span>+8% so với hôm qua</span>
        </div>
      </div>

      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-[#a6a6b0] mb-1">Tổng số ghế</div>
            <div class="text-3xl font-bold text-white">{{ \App\Models\Ghe::count() }}</div>
          </div>
          <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
            <i class="fas fa-chair text-purple-400 text-xl"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-green-400">
          <i class="fas fa-arrow-up mr-1"></i>
          <span>+5% so với tuần trước</span>
        </div>
      </div>

      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-[#a6a6b0] mb-1">Người dùng</div>
            <div class="text-3xl font-bold text-white">{{ \App\Models\NguoiDung::count() }}</div>
          </div>
          <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
            <i class="fas fa-users text-orange-400 text-xl"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-green-400">
          <i class="fas fa-arrow-up mr-1"></i>
          <span>+15% so với tháng trước</span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Quick Actions -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-bolt text-[#F53003] mr-2"></i>
          Thao tác nhanh
        </h3>
        <div class="grid grid-cols-2 gap-4">
          <a href="{{ route('admin.phong-chieu.create') }}" class="bg-[#F53003] hover:bg-[#e02a00] text-white p-4 rounded-lg text-center transition-all duration-300 hover:scale-105">
            <i class="fas fa-plus text-2xl mb-2 block"></i>
            <div class="font-medium">Tạo phòng mới</div>
          </a>
          <a href="{{ route('admin.suat-chieu.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg text-center transition-all duration-300 hover:scale-105">
            <i class="fas fa-calendar-plus text-2xl mb-2 block"></i>
            <div class="font-medium">Tạo suất chiếu</div>
          </a>
          <a href="{{ route('admin.phong-chieu.index') }}" class="bg-green-600 hover:bg-green-700 text-white p-4 rounded-lg text-center transition-all duration-300 hover:scale-105">
            <i class="fas fa-video text-2xl mb-2 block"></i>
            <div class="font-medium">Quản lý phòng</div>
          </a>
          <a href="{{ route('admin.suat-chieu.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white p-4 rounded-lg text-center transition-all duration-300 hover:scale-105">
            <i class="fas fa-calendar-alt text-2xl mb-2 block"></i>
            <div class="font-medium">Quản lý suất chiếu</div>
          </a>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-history text-[#F53003] mr-2"></i>
          Hoạt động gần đây
        </h3>
        <div class="space-y-4">
          <div class="flex items-center space-x-3 p-3 bg-[#1a1d24] rounded-lg">
            <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
              <i class="fas fa-plus text-green-400 text-sm"></i>
            </div>
            <div class="flex-1">
              <div class="text-sm text-white">Ghế mới được tạo</div>
              <div class="text-xs text-[#a6a6b0]">2 phút trước</div>
            </div>
          </div>
          <div class="flex items-center space-x-3 p-3 bg-[#1a1d24] rounded-lg">
            <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center">
              <i class="fas fa-calendar-plus text-blue-400 text-sm"></i>
            </div>
            <div class="flex-1">
              <div class="text-sm text-white">Suất chiếu mới được thêm</div>
              <div class="text-xs text-[#a6a6b0]">15 phút trước</div>
            </div>
          </div>
          <div class="flex items-center space-x-3 p-3 bg-[#1a1d24] rounded-lg">
            <div class="w-8 h-8 bg-yellow-500/20 rounded-full flex items-center justify-center">
              <i class="fas fa-edit text-yellow-400 text-sm"></i>
            </div>
            <div class="flex-1">
              <div class="text-sm text-white">Thông tin phim được cập nhật</div>
              <div class="text-xs text-[#a6a6b0]">1 giờ trước</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- System Status -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
        <i class="fas fa-server text-[#F53003] mr-2"></i>
        Trạng thái hệ thống
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="flex items-center justify-between p-4 bg-[#1a1d24] rounded-lg">
          <div class="flex items-center space-x-3">
            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
            <span class="text-white">Database</span>
          </div>
          <span class="text-green-400 text-sm font-medium">Hoạt động</span>
        </div>
        <div class="flex items-center justify-between p-4 bg-[#1a1d24] rounded-lg">
          <div class="flex items-center space-x-3">
            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
            <span class="text-white">API</span>
          </div>
          <span class="text-green-400 text-sm font-medium">Hoạt động</span>
        </div>
        <div class="flex items-center justify-between p-4 bg-[#1a1d24] rounded-lg">
          <div class="flex items-center space-x-3">
            <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
            <span class="text-white">Cache</span>
          </div>
          <span class="text-yellow-400 text-sm font-medium">Đang tải</span>
        </div>
      </div>
    </div>
  </div>
@endsection


