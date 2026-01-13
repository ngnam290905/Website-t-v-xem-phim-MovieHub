@extends('admin.layout')

@section('title', 'Quản lý Combo')

@section('content')
<div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">🥤 Quản lý Combo</h2>
    @if(auth()->user() && in_array(optional(auth()->user()->vaiTro)->ten, ['admin','staff']))
      <a href="{{ route('admin.combos.create') }}" class="px-3 py-2 bg-[#F53003] rounded text-white text-sm">+ Thêm combo</a>
    @endif
  </div>

  <!-- Quick Stats -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
      <div class="text-sm text-[#a6a6b0]">Tổng combo</div>
      <div class="text-2xl font-bold text-white mt-1">{{ $totalCombos ?? 0 }}</div>
    </div>
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
      <div class="text-sm text-[#a6a6b0]">Đang bán</div>
      <div class="text-2xl font-bold text-green-400 mt-1">{{ $activeCombos ?? 0 }}</div>
    </div>
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
      <div class="text-sm text-[#a6a6b0]">Ngừng bán</div>
      <div class="text-2xl font-bold text-red-400 mt-1">{{ $pausedCombos ?? 0 }}</div>
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
          <th class="px-4 py-3">Giá gốc</th>
          <th class="px-4 py-3">Nổi bật</th>
          <th class="px-4 py-3">Thời gian</th>
          <th class="px-4 py-3">Trạng thái</th>
          <th class="px-4 py-3 text-center">Hành động</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#262833]">
        @forelse($combos as $combo)
        <tr class="hover:bg-[#1b1e28]/70">
          <td class="px-4 py-3">#{{ $combo->id }}</td>
          <td class="px-4 py-3">
            <div class="w-11 h-11 rounded-lg overflow-hidden border border-[#262833] bg-[#1d202a]">
              <img src="{{ $combo->image_url }}" alt="{{ $combo->ten }}" class="w-full h-full object-cover"/>
            </div>
          </td>
          <td class="px-4 py-3">{{ $combo->ten }}</td>
          <td class="px-4 py-3">{{ number_format($combo->gia, 0) }}đ</td>
          <td class="px-4 py-3">{{ $combo->gia_goc ? number_format($combo->gia_goc,0).'đ' : '—' }}</td>
          <td class="px-4 py-3">{!! $combo->combo_noi_bat ? '<span class="text-amber-300">YES</span>' : '<span class="text-gray-400">NO</span>' !!}</td>
          <td class="px-4 py-3 text-xs text-gray-300">
            @if($combo->ngay_bat_dau)
              {{ $combo->ngay_bat_dau->format('d/m/Y H:i') }}
            @else
              —
            @endif
            <span class="text-gray-500">→</span>
            @if($combo->ngay_ket_thuc)
              {{ $combo->ngay_ket_thuc->format('d/m/Y H:i') }}
            @else
              —
            @endif
          </td>
          <td class="px-4 py-3">
            @if($combo->trang_thai)
              <span class="px-2 py-1 text-green-400 bg-green-900/30 rounded-full text-xs">Đang bán</span>
            @else
              <span class="px-2 py-1 text-gray-400 bg-gray-800 rounded-full text-xs">Ngừng bán</span>
            @endif
          </td>
          <td class="px-4 py-3">
            <div class="flex justify-center gap-1.5">
              <a href="{{ route('admin.combos.show', $combo) }}" class="btn-table-action btn-table-view" title="Xem chi tiết">
                <i class="fas fa-eye text-xs"></i>
              </a>
              @if(auth()->user() && in_array(optional(auth()->user()->vaiTro)->ten, ['admin','staff']))
                <a href="{{ route('admin.combos.edit', $combo) }}" class="btn-table-action btn-table-edit" title="Chỉnh sửa">
                  <i class="fas fa-edit text-xs"></i>
                </a>
                <form action="{{ route('admin.combos.destroy', $combo) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa combo này?')">
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
          <tr><td colspan="9" class="px-4 py-6 text-center text-gray-400">Chưa có combo nào.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $combos->links('pagination.custom') }}</div>
</div>
@endsection
