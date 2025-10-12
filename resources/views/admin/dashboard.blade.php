@extends('admin.layout')

@section('title', 'Bảng điều khiển - Admin')

@section('content')
  <div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
        <div class="text-sm text-[#a6a6b0]">Tổng số phim</div>
        <div class="mt-2 text-2xl font-semibold">—</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
        <div class="text-sm text-[#a6a6b0]">Suất chiếu hôm nay</div>
        <div class="mt-2 text-2xl font-semibold">—</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
        <div class="text-sm text-[#a6a6b0]">Vé đã bán</div>
        <div class="mt-2 text-2xl font-semibold">—</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
        <div class="text-sm text-[#a6a6b0]">Người dùng</div>
        <div class="mt-2 text-2xl font-semibold">—</div>
      </div>
    </div>

    <div class="bg-[#151822] border border-[#262833] rounded-xl">
      <div class="px-5 py-4 border-b border-[#262833] flex items-center justify-between">
        <h2 class="font-semibold">Hoạt động gần đây</h2>
        <a href="#" class="text-sm text-[#a6a6b0] hover:text-white">Xem tất cả</a>
      </div>
      <div class="p-5">
        <ul class="space-y-3 text-sm text-[#a6a6b0]">
          <li>— Chưa có dữ liệu</li>
        </ul>
      </div>
    </div>
  </div>
@endsection


