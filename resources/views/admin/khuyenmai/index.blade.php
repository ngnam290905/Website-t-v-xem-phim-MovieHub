
@extends('admin.layout')

@section('title', 'Quản lý mã khuyến mãi')

@section('content')
<div class="mb-4 flex justify-between items-center">
	<h1 class="text-2xl font-bold">Danh sách mã khuyến mãi</h1>
	<a href="{{ route('admin.khuyenmai.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Thêm mới</a>
</div>

@if(session('success'))
	<div class="mb-4 text-green-500">{{ session('success') }}</div>
@endif

<div class="overflow-x-auto">
	<table class="min-w-full bg-[#151822] border border-[#262833] rounded-xl">
		<thead>
			<tr class="bg-[#222533]">
				<th class="px-4 py-2">Mã KM</th>
				<th class="px-4 py-2">Mô tả</th>
				<th class="px-4 py-2">Ngày bắt đầu</th>
				<th class="px-4 py-2">Ngày kết thúc</th>
				<th class="px-4 py-2">Giá trị giảm</th>
				<th class="px-4 py-2">Điều kiện</th>
				<th class="px-4 py-2">Trạng thái</th>
				<th class="px-4 py-2">Hành động</th>
			</tr>
		</thead>
		<tbody>
		@forelse($khuyenmai as $km)
			<tr class="border-t border-[#262833]">
				<td class="px-4 py-2">{{ $km->ma_km }}</td>
				<td class="px-4 py-2">{{ $km->mo_ta }}</td>
				<td class="px-4 py-2">{{ $km->ngay_bat_dau }}</td>
				<td class="px-4 py-2">{{ $km->ngay_ket_thuc }}</td>
				<td class="px-4 py-2">{{ $km->gia_tri_giam }}</td>
				<td class="px-4 py-2">{{ $km->dieu_kien }}</td>
				<td class="px-4 py-2">{{ $km->trang_thai ? 'Kích hoạt' : 'Ẩn' }}</td>
				<td class="px-4 py-2 flex gap-2">
					<a href="{{ route('admin.khuyenmai.edit', $km->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Sửa</a>
					<form action="{{ route('admin.khuyenmai.destroy', $km->id) }}" method="POST" onsubmit="return confirm('Xác nhận xóa?');">
						@csrf
						@method('DELETE')
						<button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Xóa</button>
					</form>
				</td>
			</tr>
		@empty
			<tr><td colspan="8" class="text-center py-4">Chưa có mã khuyến mãi nào.</td></tr>
		@endforelse
		</tbody>
	</table>
</div>

<div class="mt-4">
	{{ $khuyenmai->links() }}
</div>
@endsection
