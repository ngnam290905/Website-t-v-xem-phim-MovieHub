@extends('admin.layout')

@section('title', 'Chi tiết Combo')

@section('content')
<div class="bg-[#151822] p-6 rounded-xl border border-[#262833] max-w-3xl">
  <h2 class="text-xl font-semibold mb-4">🥤 Chi tiết Combo #{{ $combo->id }}</h2>

  <div class="space-y-2 text-sm text-gray-300">
    <p><strong>Tên:</strong> {{ $combo->ten }}</p>
    <p><strong>Mô tả:</strong> {{ $combo->mo_ta ?? '—' }}</p>
    <p><strong>Giá:</strong> {{ number_format($combo->gia, 0) }}đ</p>
    <p><strong>Giá gốc:</strong> {{ $combo->gia_goc ? number_format($combo->gia_goc,0).'đ' : '—' }}</p>
    <p><strong>Ảnh:</strong> {!! $combo->anh ? '<a class="text-blue-400 underline" href="'.e($combo->anh).'" target="_blank">Mở ảnh</a>' : '—' !!}</p>
    <p><strong>Nổi bật:</strong> {!! $combo->combo_noi_bat ? '<span class="text-amber-300">YES</span>' : '<span class="text-gray-400">NO</span>' !!}</p>
    <p><strong>Số lượng tối đa:</strong> {{ $combo->so_luong_toi_da ?? '—' }}</p>
    <p><strong>Yêu cầu ít nhất vé:</strong> {{ $combo->yeu_cau_it_nhat_ve ?? '—' }}</p>
    <p><strong>Thời gian áp dụng:</strong>
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
    </p>
    <p><strong>Trạng thái:</strong>
      @if($combo->trang_thai)
        <span class="text-green-400">Đang bán</span>
      @else
        <span class="text-gray-400">Ngừng bán</span>
      @endif
    </p>
  </div>

  <div class="mt-6 flex gap-2">
    @if(auth()->user() && optional(auth()->user()->vaiTro)->ten === 'admin')
      <a href="{{ route('admin.combos.edit', $combo) }}" class="px-3 py-2 bg-yellow-500/80 hover:bg-yellow-500 rounded text-black text-sm">Sửa</a>
      <form action="{{ route('admin.combos.destroy', $combo) }}" method="POST" onsubmit="return confirm('Xóa combo này?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-2 bg-red-600/80 hover:bg-red-600 rounded text-white text-sm">Xóa</button>
      </form>
    @endif
    <a href="{{ route('admin.combos.index') }}" class="px-3 py-2 border border-[#2f3240] rounded text-sm text-gray-300">Quay lại</a>
  </div>
</div>
@endsection
