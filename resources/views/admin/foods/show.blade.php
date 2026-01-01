@extends('admin.layout')

@section('title', 'Chi tiết Đồ ăn')

@section('content')
<div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">🍿 Chi tiết Đồ ăn</h2>
    <div class="flex gap-2">
      <a href="{{ route('admin.foods.edit', $food) }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white text-sm">Chỉnh sửa</a>
      <a href="{{ route('admin.foods.index') }}" class="px-3 py-2 border border-[#2f3240] rounded text-sm text-gray-300">Quay lại</a>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Thông tin cơ bản -->
    <div class="bg-[#1b1e28] p-4 rounded-xl border border-[#262833]">
      <h3 class="text-lg font-semibold mb-4">Thông tin cơ bản</h3>
      <div class="space-y-3">
        <div>
          <label class="text-sm text-gray-400">Tên đồ ăn</label>
          <p class="text-white font-medium">{{ $food->name }}</p>
        </div>
        <div>
          <label class="text-sm text-gray-400">Giá</label>
          <p class="text-white font-medium">{{ number_format($food->price, 0) }}đ</p>
        </div>
        <div>
          <label class="text-sm text-gray-400">Tồn kho</label>
          <p class="text-white font-medium">
            @if($food->stock > 10)
              <span class="text-green-400">{{ $food->stock }}</span>
            @elseif($food->stock > 0)
              <span class="text-yellow-400">{{ $food->stock }}</span>
            @else
              <span class="text-red-400">Hết hàng</span>
            @endif
          </p>
        </div>
        <div>
          <label class="text-sm text-gray-400">Trạng thái</label>
          <p>
            @if($food->is_active)
              <span class="px-2 py-1 text-green-400 bg-green-900/30 rounded-full text-xs">Đang bán</span>
            @else
              <span class="px-2 py-1 text-gray-400 bg-gray-800 rounded-full text-xs">Ngừng bán</span>
            @endif
          </p>
        </div>
      </div>
    </div>

    <!-- Ảnh -->
    <div class="bg-[#1b1e28] p-4 rounded-xl border border-[#262833]">
      <h3 class="text-lg font-semibold mb-4">Ảnh</h3>
      <img src="{{ $food->image_url }}" alt="{{ $food->name }}" class="w-full h-64 object-cover rounded">
    </div>
  </div>

  <!-- Thống kê -->
  <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-[#1b1e28] p-4 rounded-xl border border-[#262833]">
      <div class="text-sm text-gray-400">Tổng đã bán</div>
      <div class="text-2xl font-bold text-white mt-1">{{ $totalSold ?? 0 }}</div>
    </div>
    <div class="bg-[#1b1e28] p-4 rounded-xl border border-[#262833]">
      <div class="text-sm text-gray-400">Doanh thu</div>
      <div class="text-2xl font-bold text-green-400 mt-1">{{ number_format($totalRevenue ?? 0, 0) }}đ</div>
    </div>
    <div class="bg-[#1b1e28] p-4 rounded-xl border border-[#262833]">
      <div class="text-sm text-gray-400">Tồn kho</div>
      <div class="text-2xl font-bold {{ $food->stock > 0 ? 'text-blue-400' : 'text-red-400' }} mt-1">{{ $food->stock }}</div>
    </div>
  </div>

  <!-- Đơn hàng gần đây -->
  @if(isset($recentOrders) && $recentOrders->count() > 0)
  <div class="mt-6 bg-[#1b1e28] p-4 rounded-xl border border-[#262833]">
    <h3 class="text-lg font-semibold mb-4">Đơn hàng gần đây</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="text-gray-400 border-b border-[#262833]">
          <tr>
            <th class="px-4 py-2 text-left">Ngày</th>
            <th class="px-4 py-2 text-left">Khách hàng</th>
            <th class="px-4 py-2 text-right">Số lượng</th>
            <th class="px-4 py-2 text-right">Giá</th>
            <th class="px-4 py-2 text-right">Tổng</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#262833]">
          @foreach($recentOrders as $order)
          <tr>
            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') }}</td>
            <td class="px-4 py-2">{{ $order->ho_ten ?? 'N/A' }}</td>
            <td class="px-4 py-2 text-right">{{ $order->quantity }}</td>
            <td class="px-4 py-2 text-right">{{ number_format($order->price, 0) }}đ</td>
            <td class="px-4 py-2 text-right font-medium">{{ number_format($order->price * $order->quantity, 0) }}đ</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif
</div>
@endsection

