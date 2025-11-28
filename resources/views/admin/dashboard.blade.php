@extends('admin.layout')

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
      @php
        $userRole = optional(auth()->user()->vaiTro)->ten;
        $isAdmin = in_array(mb_strtolower(trim($userRole ?? '')), ['admin']);
      @endphp
      
      <!-- Doanh thu hôm nay -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 hover:shadow-lg transition-all duration-300 {{ $isAdmin ? 'cursor-pointer' : '' }}" @if($isAdmin) onclick="window.location='{{ route('admin.reports.dashboard') }}'" @endif>
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-[#a6a6b0] mb-1">Doanh thu hôm nay</div>
            <div class="text-3xl font-bold text-white">{{ number_format($todayRevenue, 0, ',', '.') }} đ</div>
          </div>
          <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
            <i class="fas fa-money-bill-wave text-green-400 text-xl"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-400">
          <i class="fas fa-calendar mr-1"></i>
          <span>{{ date('d/m/Y') }}</span>
        </div>
      </div>

      <!-- Doanh thu tháng này -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 hover:shadow-lg transition-all duration-300 {{ $isAdmin ? 'cursor-pointer' : '' }}" @if($isAdmin) onclick="window.location='{{ route('admin.reports.dashboard') }}'" @endif>
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-[#a6a6b0] mb-1">Doanh thu tháng này</div>
            <div class="text-3xl font-bold text-white">{{ number_format($monthRevenue, 0, ',', '.') }} đ</div>
          </div>
          <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
            <i class="fas fa-chart-line text-blue-400 text-xl"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-400">
          <i class="fas fa-calendar-alt mr-1"></i>
          <span>Tháng {{ date('m/Y') }}</span>
        </div>
      </div>

      <!-- Tổng số phim -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 hover:shadow-lg transition-all duration-300 cursor-pointer" onclick="window.location='{{ route('admin.movies.index') }}'">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-[#a6a6b0] mb-1">Tổng số phim</div>
            <div class="text-3xl font-bold text-white">{{ $totalMovies }}</div>
          </div>
          <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
            <i class="fas fa-film text-purple-400 text-xl"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-400">
          <i class="fas fa-eye mr-1"></i>
          <span>Đang chiếu</span>
        </div>
      </div>

      <!-- Người dùng -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 hover:shadow-lg transition-all duration-300 {{ $isAdmin ? 'cursor-pointer' : '' }}" @if($isAdmin) onclick="window.location='{{ route('admin.users.index') }}'" @endif>
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-[#a6a6b0] mb-1">Tổng người dùng</div>
            <div class="text-3xl font-bold text-white">{{ $totalCustomers }}</div>
          </div>
          <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
            <i class="fas fa-users text-orange-400 text-xl"></i>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-400">
          <i class="fas fa-user-check mr-1"></i>
          <span>Đã kích hoạt</span>
        </div>
      </div>
    </div>

    <!-- More Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs text-[#a6a6b0] mb-1">Suất chiếu hôm nay</div>
            <div class="text-2xl font-bold text-white">{{ number_format($todayShowtimesCount ?? (is_countable($todayShowtimes ?? []) ? count($todayShowtimes) : 0)) }}</div>
          </div>
          <i class="fas fa-calendar-alt text-green-400"></i>
        </div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs text-[#a6a6b0] mb-1">Đặt vé hôm nay</div>
            <div class="text-2xl font-bold text-white">{{ $todayBookings }}</div>
          </div>
          <i class="fas fa-ticket-alt text-blue-400"></i>
        </div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs text-[#a6a6b0] mb-1">Tổng đặt vé</div>
            <div class="text-2xl font-bold text-white">{{ $totalBookings }}</div>
          </div>
          <i class="fas fa-receipt text-purple-400"></i>
        </div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs text-[#a6a6b0] mb-1">Phòng chiếu</div>
            <div class="text-2xl font-bold text-white">{{ $totalRooms }}</div>
          </div>
          <i class="fas fa-video text-yellow-400"></i>
        </div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs text-[#a6a6b0] mb-1">Ghế</div>
            <div class="text-2xl font-bold text-white">{{ $totalSeats }}</div>
          </div>
          <i class="fas fa-chair text-pink-400"></i>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Quick Actions -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-bolt text-[#F53003] mr-2"></i>
          Thao tác nhanh
        </h3>
        <div class="space-y-2">
          @php
            $userRole = optional(auth()->user()->vaiTro)->ten;
            $isAdmin = in_array(mb_strtolower(trim($userRole ?? '')), ['admin']);
          @endphp
          
          @if($isAdmin)
            <a href="{{ route('admin.suat-chieu.create') }}" class="flex items-center justify-between p-3 bg-[#1a1d24] hover:bg-[#222533] rounded-lg transition-colors">
              <div class="flex items-center space-x-3">
                <i class="fas fa-plus-circle text-green-400"></i>
                <span class="text-white text-sm">Tạo suất chiếu</span>
              </div>
              <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </a>
          @else
            <a href="{{ route('admin.suat-chieu.index') }}" class="flex items-center justify-between p-3 bg-[#1a1d24] hover:bg-[#222533] rounded-lg transition-colors">
              <div class="flex items-center space-x-3">
                <i class="fas fa-calendar-alt text-green-400"></i>
                <span class="text-white text-sm">Xem suất chiếu</span>
              </div>
              <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </a>
          @endif
          
          <a href="{{ route('admin.phong-chieu.index') }}" class="flex items-center justify-between p-3 bg-[#1a1d24] hover:bg-[#222533] rounded-lg transition-colors">
            <div class="flex items-center space-x-3">
              <i class="fas fa-video text-blue-400"></i>
              <span class="text-white text-sm">{{ $isAdmin ? 'Quản lý' : 'Xem' }} phòng chiếu</span>
            </div>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
          </a>
          
          @if($isAdmin)
            <a href="{{ route('admin.users.create') }}" class="flex items-center justify-between p-3 bg-[#1a1d24] hover:bg-[#222533] rounded-lg transition-colors">
              <div class="flex items-center space-x-3">
                <i class="fas fa-user-plus text-purple-400"></i>
                <span class="text-white text-sm">Thêm người dùng</span>
              </div>
              <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </a>
          @else
            <a href="{{ route('admin.movies.index') }}" class="flex items-center justify-between p-3 bg-[#1a1d24] hover:bg-[#222533] rounded-lg transition-colors">
              <div class="flex items-center space-x-3">
                <i class="fas fa-film text-purple-400"></i>
                <span class="text-white text-sm">Quản lý phim</span>
              </div>
              <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </a>
          @endif
          
          <a href="{{ route('admin.bookings.index') }}" class="flex items-center justify-between p-3 bg-[#1a1d24] hover:bg-[#222533] rounded-lg transition-colors">
            <div class="flex items-center space-x-3">
              <i class="fas fa-ticket-alt text-orange-400"></i>
              <span class="text-white text-sm">Quản lý đặt vé</span>
            </div>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
          </a>
          
          @if($isAdmin)
            <a href="{{ route('admin.reports.dashboard') }}" class="flex items-center justify-between p-3 bg-[#1a1d24] hover:bg-[#222533] rounded-lg transition-colors">
              <div class="flex items-center space-x-3">
                <i class="fas fa-chart-bar text-yellow-400"></i>
                <span class="text-white text-sm">Xem báo cáo</span>
              </div>
              <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </a>
          @else
            <a href="{{ route('admin.ghe.index') }}" class="flex items-center justify-between p-3 bg-[#1a1d24] hover:bg-[#222533] rounded-lg transition-colors">
              <div class="flex items-center space-x-3">
                <i class="fas fa-chair text-yellow-400"></i>
                <span class="text-white text-sm">Xem ghế ngồi</span>
              </div>
              <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </a>
          @endif
          
          <a href="{{ route('admin.khuyenmai.index') }}" class="flex items-center justify-between p-3 bg-[#1a1d24] hover:bg-[#222533] rounded-lg transition-colors">
            <div class="flex items-center space-x-3">
              <i class="fas fa-gift text-pink-400"></i>
              <span class="text-white text-sm">Khuyến mãi</span>
            </div>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
          </a>
        </div>
      </div>

      <!-- Recent Bookings -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-clock text-[#F53003] mr-2"></i>
            Đặt vé gần đây
          </h3>
          <a href="{{ route('admin.bookings.index') }}" class="text-sm text-[#F53003] hover:underline">Xem tất cả</a>
        </div>
        <div class="space-y-3">
          @forelse($recentBookings as $booking)
            <div class="flex items-center justify-between p-3 bg-[#1a1d24] rounded-lg">
              <div class="flex items-center space-x-3 flex-1">
                <div class="w-10 h-10 bg-[#F53003]/20 rounded-lg flex items-center justify-center">
                  <i class="fas fa-ticket-alt text-[#F53003]"></i>
                </div>
                <div class="flex-1">
                  <div class="text-white font-medium text-sm">
                    {{ $booking->nguoiDung->ho_ten ?? 'Khách vãng lai' }}
                  </div>
                  <div class="text-gray-400 text-xs">
                    {{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }} - 
                    {{ $booking->chiTietDatVe->count() ?? 0 }} vé
                  </div>
                </div>
              </div>
              <div class="text-right">
                <div class="text-white text-sm font-medium">
                  {{ number_format($booking->tong_tien ?? 0, 0, ',', '.') }} đ
                </div>
                <div class="text-gray-400 text-xs">
                  {{ $booking->created_at->diffForHumans() }}
                </div>
              </div>
            </div>
          @empty
            <div class="text-center py-8 text-gray-400">
              <i class="fas fa-inbox text-4xl mb-2"></i>
              <p>Chưa có đặt vé nào</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Top Movies -->
    @if($topMovies->count() > 0)
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
        <i class="fas fa-trophy text-[#F53003] mr-2"></i>
        Phim phổ biến tháng này
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        @foreach($topMovies as $movie)
          <div class="bg-[#1a1d24] rounded-lg overflow-hidden hover:scale-105 transition-transform">
            @if($movie->poster)
              <img src="{{ asset('storage/' . $movie->poster) }}" alt="{{ $movie->ten_phim }}" class="w-full h-48 object-cover">
            @else
              <div class="w-full h-48 bg-gray-700 flex items-center justify-center">
                <i class="fas fa-film text-4xl text-gray-500"></i>
              </div>
            @endif
            <div class="p-3">
              <h4 class="text-white text-sm font-medium mb-1 truncate">{{ $movie->ten_phim }}</h4>
              <div class="flex items-center justify-between text-xs text-gray-400">
                <span><i class="fas fa-ticket-alt mr-1"></i>{{ $movie->total_tickets }} vé</span>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- System Status -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
        <i class="fas fa-server text-[#F53003] mr-2"></i>
        Trạng thái hệ thống
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="flex items-center justify-between p-4 bg-[#1a1d24] rounded-lg">
          <div class="flex items-center space-x-3">
            <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-white">Database</span>
          </div>
          <span class="text-green-400 text-sm font-medium">Hoạt động</span>
        </div>
        <div class="flex items-center justify-between p-4 bg-[#1a1d24] rounded-lg">
          <div class="flex items-center space-x-3">
            <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-white">API</span>
          </div>
          <span class="text-green-400 text-sm font-medium">Hoạt động</span>
        </div>
        <div class="flex items-center justify-between p-4 bg-[#1a1d24] rounded-lg">
          <div class="flex items-center space-x-3">
            <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-white">Cache</span>
          </div>
          <span class="text-green-400 text-sm font-medium">Hoạt động</span>
        </div>
        <div class="flex items-center justify-between p-4 bg-[#1a1d24] rounded-lg">
          <div class="flex items-center space-x-3">
            <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-white">Khuyến mãi</span>
          </div>
          <span class="text-green-400 text-sm font-medium">{{ $activePromotions }} đang áp dụng</span>
        </div>
      </div>
    </div>
  </div>
@endsection
