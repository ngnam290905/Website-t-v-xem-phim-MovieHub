@extends('admin.layout')

@section('title', 'Thống kê Đồ ăn Bán chạy')

@section('content')
<div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">📊 Thống kê Đồ ăn Bán chạy</h2>
    <a href="{{ route('admin.foods.index') }}" class="px-3 py-2 border border-[#2f3240] rounded text-sm text-gray-300">Quay lại</a>
  </div>

  <!-- Overall Statistics -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-[#1b1e28] p-4 rounded-xl border border-[#262833]">
      <div class="text-sm text-gray-400">Tổng số lượng đã bán</div>
      <div class="text-3xl font-bold text-white mt-2">{{ number_format($totalFoodsSold ?? 0, 0) }}</div>
    </div>
    <div class="bg-[#1b1e28] p-4 rounded-xl border border-[#262833]">
      <div class="text-sm text-gray-400">Tổng doanh thu</div>
      <div class="text-3xl font-bold text-green-400 mt-2">{{ number_format($totalFoodRevenue ?? 0, 0) }}đ</div>
    </div>
  </div>

  <!-- Top Foods -->
  <div class="bg-[#1b1e28] p-4 rounded-xl border border-[#262833]">
    <h3 class="text-lg font-semibold mb-4">Top 20 Đồ ăn Bán chạy</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="text-gray-400 border-b border-[#262833]">
          <tr>
            <th class="px-4 py-2 text-left">#</th>
            <th class="px-4 py-2 text-left">Ảnh</th>
            <th class="px-4 py-2 text-left">Tên đồ ăn</th>
            <th class="px-4 py-2 text-right">Số lượng bán</th>
            <th class="px-4 py-2 text-right">Doanh thu</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#262833]">
          @forelse($topFoods as $index => $food)
          <tr class="hover:bg-[#151822]/50">
            <td class="px-4 py-3">{{ $index + 1 }}</td>
            <td class="px-4 py-3">
              <img src="{{ $food->image ? asset('storage/' . $food->image) : asset('images/no-poster.svg') }}" alt="{{ $food->name }}" class="w-12 h-12 object-cover rounded">
            </td>
            <td class="px-4 py-3 font-medium">{{ $food->name }}</td>
            <td class="px-4 py-3 text-right">
              <span class="text-blue-400 font-semibold">{{ number_format($food->total_sold, 0) }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <span class="text-green-400 font-semibold">{{ number_format($food->total_revenue, 0) }}đ</span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-400">Chưa có dữ liệu thống kê.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

