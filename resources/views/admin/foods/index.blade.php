@extends('admin.layout')

@section('title', 'Quản lý Đồ ăn')

@section('content')
<div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">🍿 Quản lý Đồ ăn</h2>
    <div class="flex gap-2">
      <a href="{{ route('admin.foods.statistics') }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white text-sm">
        <i class="fas fa-chart-bar mr-1"></i> Thống kê
      </a>
      @if(auth()->user() && in_array(optional(auth()->user()->vaiTro)->ten, ['admin','staff']))
        <a href="{{ route('admin.foods.create') }}" class="px-3 py-2 bg-[#F53003] rounded text-white text-sm">+ Thêm đồ ăn</a>
      @endif
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
      <div class="text-sm text-[#a6a6b0]">Tổng đồ ăn</div>
      <div class="text-2xl font-bold text-white mt-1">{{ $totalFoods ?? 0 }}</div>
    </div>
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
      <div class="text-sm text-[#a6a6b0]">Đang bán</div>
      <div class="text-2xl font-bold text-green-400 mt-1">{{ $activeFoods ?? 0 }}</div>
    </div>
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
      <div class="text-sm text-[#a6a6b0]">Ngừng bán</div>
      <div class="text-2xl font-bold text-red-400 mt-1">{{ $inactiveFoods ?? 0 }}</div>
    </div>
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
      <div class="text-sm text-[#a6a6b0]">Bán hôm nay</div>
      <div class="text-2xl font-bold text-blue-400 mt-1">{{ $soldToday ?? 0 }}</div>
    </div>
  </div>

  @if(session('success'))
    <div class="text-green-400 text-sm bg-green-900/30 px-3 py-2 rounded mb-3">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="text-red-400 text-sm bg-red-900/30 px-3 py-2 rounded mb-3">{{ session('error') }}</div>
  @endif

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left border border-[#262833] rounded-xl">
      <thead class="bg-[#1b1e28] text-gray-300 uppercase text-xs">
        <tr>
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">Ảnh</th>
          <th class="px-4 py-3">Tên</th>
          <th class="px-4 py-3">Giá</th>
          <th class="px-4 py-3">Tồn kho</th>
          <th class="px-4 py-3">Trạng thái</th>
          <th class="px-4 py-3 text-center">Hành động</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#262833]">
        @forelse($foods as $food)
        <tr class="hover:bg-[#1b1e28]/70">
          <td class="px-4 py-3">#{{ $food->id }}</td>
          <td class="px-4 py-3">
            <img src="{{ $food->image_url }}" alt="{{ $food->name }}" class="w-12 h-12 object-cover rounded">
          </td>
          <td class="px-4 py-3 font-medium">{{ $food->name }}</td>
          <td class="px-4 py-3">{{ number_format($food->price, 0) }}đ</td>
          <td class="px-4 py-3">
            @if($food->stock > 10)
              <span class="text-green-400">{{ $food->stock }}</span>
            @elseif($food->stock > 0)
              <span class="text-yellow-400">{{ $food->stock }}</span>
            @else
              <span class="text-red-400">Hết</span>
            @endif
          </td>
          <td class="px-4 py-3">
            @if($food->is_active)
              <span class="px-2 py-1 text-green-400 bg-green-900/30 rounded-full text-xs">Đang bán</span>
            @else
              <span class="px-2 py-1 text-gray-400 bg-gray-800 rounded-full text-xs">Ngừng bán</span>
            @endif
          </td>
          <td class="px-4 py-3">
            <div class="flex justify-center gap-1.5">
              <a href="{{ route('admin.foods.show', $food) }}" class="btn-table-action btn-table-view" title="Xem chi tiết">
                <i class="fas fa-eye text-xs"></i>
              </a>
              @if(auth()->user() && in_array(optional(auth()->user()->vaiTro)->ten, ['admin','staff']))
                <a href="{{ route('admin.foods.edit', $food) }}" class="btn-table-action btn-table-edit" title="Chỉnh sửa">
                  <i class="fas fa-edit text-xs"></i>
                </a>
                <form action="{{ route('admin.foods.toggle-status', $food) }}" method="POST" class="inline">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn-table-action {{ $food->is_active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }}" title="{{ $food->is_active ? 'Tắt' : 'Bật' }}">
                    <i class="fas fa-{{ $food->is_active ? 'pause' : 'play' }} text-xs"></i>
                  </button>
                </form>
                <form action="{{ route('admin.foods.destroy', $food) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đồ ăn này?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-table-action btn-table-delete" title="Xóa">
                    <i class="fas fa-trash text-xs"></i>
                  </button>
                </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="px-4 py-8 text-center text-gray-400">Chưa có đồ ăn nào.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $foods->links() }}
  </div>
</div>
@endsection

