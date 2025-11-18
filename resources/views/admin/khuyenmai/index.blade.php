
@extends('admin.layout')

@section('title', 'Quản lý mã khuyến mãi')

@section('content')
<!-- Header -->
<div class="mb-6 flex justify-between items-center">
	<h1 class="text-2xl font-bold">Danh sách mã khuyến mãi</h1>
	@if(auth()->user() && in_array(optional(auth()->user()->vaiTro)->ten, ['admin','staff']))
		<a href="{{ route('admin.khuyenmai.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
			<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
			</svg>
			Thêm mới
		</a>
	@endif
</div>

<!-- Thông báo -->
@if(session('success'))
	<div class="mb-4 p-4 bg-green-600 bg-opacity-20 border border-green-600 rounded-lg text-green-400">
		{{ session('success') }}
	</div>
@endif

<!-- Thống kê -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
	<div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
		<div class="flex items-center justify-between">
			<div>
				<p class="text-gray-400 text-sm">Tổng số mã</p>
				<p class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
			</div>
			<div class="bg-blue-600 bg-opacity-20 p-3 rounded-lg">
				<svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
				</svg>
			</div>
		</div>
	</div>

	<div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
		<div class="flex items-center justify-between">
			<div>
				<p class="text-gray-400 text-sm">Còn hạn</p>
				<p class="text-2xl font-bold text-green-400 mt-1">{{ $stats['active'] }}</p>
			</div>
			<div class="bg-green-600 bg-opacity-20 p-3 rounded-lg">
				<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
			</div>
		</div>
	</div>

	<div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
		<div class="flex items-center justify-between">
			<div>
				<p class="text-gray-400 text-sm">Hết hạn</p>
				<p class="text-2xl font-bold text-red-400 mt-1">{{ $stats['expired'] }}</p>
			</div>
			<div class="bg-red-600 bg-opacity-20 p-3 rounded-lg">
				<svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
			</div>
		</div>
	</div>

	<div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
		<div class="flex items-center justify-between">
			<div>
				<p class="text-gray-400 text-sm">Sắp diễn ra</p>
				<p class="text-2xl font-bold text-yellow-400 mt-1">{{ $stats['upcoming'] }}</p>
			</div>
			<div class="bg-yellow-600 bg-opacity-20 p-3 rounded-lg">
				<svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
			</div>
		</div>
	</div>
</div>

<!-- Tìm kiếm và Lọc -->
<div class="bg-[#151822] border border-[#262833] rounded-xl p-4 mb-6">
	<form method="GET" action="{{ route('admin.khuyenmai.index') }}" class="flex flex-col md:flex-row gap-4">
		<div class="flex-1">
			<div class="relative">
				<input type="text" name="search" value="{{ request('search') }}" 
					   placeholder="Tìm kiếm theo mã, tên, điều kiện..." 
					   class="w-full pl-10 pr-4 py-2 bg-[#222533] border border-[#262833] rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
				<svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
				</svg>
			</div>
		</div>
		<div class="w-full md:w-48">
			<select name="status" class="w-full px-4 py-2 bg-[#222533] border border-[#262833] rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
				<option value="">Tất cả trạng thái</option>
				<option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Còn hạn</option>
				<option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Hết hạn</option>
				<option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Sắp diễn ra</option>
			</select>
		</div>
		<div class="flex gap-2">
			<button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2">
				<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
				</svg>
				Tìm
			</button>
			@if(request('search') || request('status'))
				<a href="{{ route('admin.khuyenmai.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center gap-2">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
					</svg>
					Xóa lọc
				</a>
			@endif
		</div>
	</form>
</div>

<div class="overflow-x-auto">
	<table class="min-w-full bg-[#151822] border border-[#262833] rounded-xl">
		<thead>
			<tr class="bg-[#222533]">
				<th class="px-4 py-2">Tên KM</th>
				<th class="px-4 py-2">Mã KM</th>
				<th class="px-4 py-2">Ngày bắt đầu</th>
				<th class="px-4 py-2">Ngày kết thúc</th>
				<th class="px-4 py-2">Giá trị giảm</th>
				<th class="px-4 py-2">Điều kiện áp dụng</th>
				<th class="px-4 py-2">Trạng thái</th>
				<th class="px-4 py-2">Hành động</th>
			</tr>
		</thead>
		<tbody>
		@forelse($khuyenmai as $km)
			@php
				$start = \Carbon\Carbon::parse($km->ngay_bat_dau);
				$end = \Carbon\Carbon::parse($km->ngay_ket_thuc);
			@endphp
			<tr class="border-t border-[#262833]">
				<td class="px-4 py-2">{{ $km->mo_ta }}</td>
				<td class="px-4 py-2">{{ $km->ma_km }}</td>
				<td class="px-4 py-2">
					<div>{{ $start->format('d/m/Y') }}</div>
					<div class="text-xs text-gray-400">{{ $start->format('H:i') }}</div>
				</td>
				<td class="px-4 py-2">
					<div>{{ $end->format('d/m/Y') }}</div>
					<div class="text-xs text-gray-400">{{ $end->format('H:i') }}</div>
				</td>
				<td class="px-4 py-2">{{ $km->gia_tri_giam }}</td>
				<td class="px-4 py-2">{{ $km->dieu_kien }}</td>
				<td class="px-4 py-2">{{ $km->trang_thai ? 'Kích hoạt' : 'Ẩn' }}</td>
				<td class="px-4 py-2">
					<div class="flex justify-center gap-1.5">
						<a href="{{ route('admin.khuyenmai.show', $km->id) }}" class="btn-table-action btn-table-view" title="Xem chi tiết">
							<i class="fas fa-eye text-xs"></i>
						</a>
						@if(auth()->user() && in_array(optional(auth()->user()->vaiTro)->ten, ['admin','staff']))
							<a href="{{ route('admin.khuyenmai.edit', $km->id) }}" class="btn-table-action btn-table-edit" title="Chỉnh sửa">
								<i class="fas fa-edit text-xs"></i>
							</a>
							<form action="{{ route('admin.khuyenmai.destroy', $km->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mã khuyến mãi này?');">
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
			<tr><td colspan="8" class="text-center py-4">Chưa có mã khuyến mãi nào.</td></tr>
		@endforelse
		</tbody>
	</table>
</div>

<div class="mt-4">
	{{ $khuyenmai->links() }}
</div>
@endsection
