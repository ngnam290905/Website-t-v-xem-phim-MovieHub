@extends('admin.layout')

@section('title', 'Chi Tiết Ghế')

@section('content')
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold">Chi Tiết Ghế #{{ $ghe->id }}</h1>
      <div class="flex space-x-3">
        <a href="{{ route('admin.ghe.edit', $ghe) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
          <i class="fas fa-edit mr-2"></i>Chỉnh Sửa
        </a>
        <a href="{{ route('admin.ghe.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
          <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Thông Tin Cơ Bản -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4 text-white">Thông Tin Cơ Bản</h3>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">ID:</span>
            <span class="text-white">{{ $ghe->id }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Số Ghế:</span>
            <span class="text-white">{{ $ghe->so_ghe }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Số Hàng:</span>
            <span class="text-white">{{ $ghe->so_hang }}</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="font-medium text-[#a6a6b0]">Trạng Thái:</span>
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $ghe->trang_thai ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
              {{ $ghe->trang_thai ? 'Hoạt động' : 'Tạm dừng' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Thông Tin Phòng Chiếu -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4 text-white">Thông Tin Phòng Chiếu</h3>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Tên Phòng:</span>
            <span class="text-white">{{ $ghe->phongChieu->ten_phong }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Số Hàng:</span>
            <span class="text-white">{{ $ghe->phongChieu->so_hang }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Số Cột:</span>
            <span class="text-white">{{ $ghe->phongChieu->so_cot }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Sức Chứa:</span>
            <span class="text-white">{{ $ghe->phongChieu->suc_chua }} chỗ</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="font-medium text-[#a6a6b0]">Mô Tả:</span>
            <span class="text-white">{{ $ghe->phongChieu->mo_ta ?? 'Không có' }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Thông Tin Loại Ghế -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4 text-white">Thông Tin Loại Ghế</h3>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Tên Loại:</span>
            <span class="text-white">{{ $ghe->loaiGhe->ten_loai ?? 'N/A' }}</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="font-medium text-[#a6a6b0]">Hệ Số Giá:</span>
            <span class="text-white">{{ $ghe->loaiGhe->he_so_gia ?? 0 }}x</span>
          </div>
        </div>
      </div>

      <!-- Thống Kê Sử Dụng -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4 text-white">Thống Kê Sử Dụng</h3>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Số Lần Được Đặt:</span>
            <span class="text-white">{{ $ghe->chiTietDatVe->count() }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-[#262833]">
            <span class="font-medium text-[#a6a6b0]">Tổng Doanh Thu:</span>
            <span class="text-white">{{ number_format($ghe->chiTietDatVe->sum('gia')) }} VNĐ</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="font-medium text-[#a6a6b0]">Lần Đặt Cuối:</span>
            <span class="text-white">
              @if($ghe->chiTietDatVe->count() > 0)
                {{ $ghe->chiTietDatVe->latest()->first()->created_at->format('d/m/Y H:i') }}
              @else
                Chưa có
              @endif
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Lịch Sử Đặt Vé -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <h3 class="text-lg font-semibold mb-4 text-white">Lịch Sử Đặt Vé</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[#262833]">
          <thead class="bg-[#1a1d24]">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">ID Đặt Vé</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Khách Hàng</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Ngày Đặt</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Giá Vé</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng Thái</th>
            </tr>
          </thead>
          <tbody class="bg-[#151822] divide-y divide-[#262833]">
            @forelse($ghe->chiTietDatVe as $chiTiet)
            <tr class="hover:bg-[#1a1d24] transition-colors duration-200">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $chiTiet->datVe->id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $chiTiet->datVe->ten_khach_hang ?? 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $chiTiet->created_at->format('d/m/Y H:i') }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ number_format($chiTiet->gia) }} VNĐ</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $chiTiet->datVe->trang_thai ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                  {{ $chiTiet->datVe->trang_thai ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                </span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="px-6 py-4 text-center text-sm text-[#a6a6b0]">Chưa có vé đặt nào cho ghế này</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
