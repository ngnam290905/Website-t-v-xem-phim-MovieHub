@extends('admin.layout')

@section('title', 'Chỉnh sửa vé')

@section('content')
<div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
  <h2 class="text-xl font-semibold mb-4">✏️ Chỉnh sửa Đặt Vé #{{ $booking->id }}</h2>

  <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="space-y-4">
      @csrf

      <div>
          <label class="block mb-1 text-sm text-gray-300">Chọn ghế mới</label>
          <select name="ghe_ids[]" multiple class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200">
              @foreach($gheTrong as $ghe)
                  <option value="{{ $ghe->id }}">{{ $ghe->id_loai }}</option>
              @endforeach
          </select>
      </div>

      <div>
          <label class="block mb-1 text-sm text-gray-300">Chọn combo (nếu có)</label>
          <select name="combo_ids[]" multiple class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200">
              @foreach($combos as $combo)
                  <option value="{{ $combo->id }}">{{ $combo->ten }}</option>
              @endforeach
          </select>
      </div>

      <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded text-white text-sm">Lưu thay đổi</button>
      <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded text-white text-sm">Hủy</a>
  </form>
</div>
@endsection
