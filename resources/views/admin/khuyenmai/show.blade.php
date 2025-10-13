@extends('admin.layout')

@section('title', 'Chi tiết mã khuyến mãi')

@section('content')
<div class="mb-4 flex justify-between items-center">
	<h1 class="text-2xl font-bold">Chi tiết mã khuyến mãi</h1>
	<a href="{{ route('admin.khuyenmai.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Quay lại danh sách</a>
</div>

<div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
	<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
		<div>
			<h3 class="text-lg font-semibold mb-2 text-blue-400">Thông tin cơ bản</h3>
			<div class="space-y-3">
				<div>
					<label class="text-gray-300 font-medium">Mã khuyến mãi:</label>
					<p class="text-white mt-1">{{ $khuyenmai->ma_km }}</p>
				</div>
				<div>
					<label class="text-gray-300 font-medium">Mô tả:</label>
					<p class="text-white mt-1">{{ $khuyenmai->mo_ta ?? 'Không có mô tả' }}</p>
				</div>
				<div>
					<label class="text-gray-300 font-medium">Điều kiện áp dụng:</label>
					<p class="text-white mt-1">{{ $khuyenmai->dieu_kien ?? 'Không có điều kiện đặc biệt' }}</p>
				</div>
			</div>
		</div>
		
		<div>
			<h3 class="text-lg font-semibold mb-2 text-green-400">Thông tin giảm giá</h3>
			<div class="space-y-3">
				<div>
					<label class="text-gray-300 font-medium">Giá trị giảm:</label>
					<p class="text-white mt-1">
						{{ number_format($khuyenmai->gia_tri_giam, 0, ',', '.') }}
						@if($khuyenmai->loai_giam === 'phantram')
							%
						@else
							VNĐ
						@endif
					</p>
				</div>
				<div>
					<label class="text-gray-300 font-medium">Loại giảm:</label>
					<p class="text-white mt-1">
						@if($khuyenmai->loai_giam === 'phantram')
							<span class="bg-blue-600 text-white px-2 py-1 rounded-full text-sm">Phần trăm</span>
						@else
							<span class="bg-green-600 text-white px-2 py-1 rounded-full text-sm">Cố định</span>
						@endif
					</p>
				</div>
				<div>
					<label class="text-gray-300 font-medium">Trạng thái:</label>
					<p class="text-white mt-1">
						@if($khuyenmai->trang_thai)
							<span class="bg-green-600 text-white px-2 py-1 rounded-full text-sm">Kích hoạt</span>
						@else
							<span class="bg-red-600 text-white px-2 py-1 rounded-full text-sm">Ẩn</span>
						@endif
					</p>
				</div>
			</div>
		</div>
	</div>
	
	<div class="mt-6 pt-6 border-t border-[#262833]">
		<h3 class="text-lg font-semibold mb-2 text-yellow-400">Thời gian hiệu lực</h3>
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<div>
				<label class="text-gray-300 font-medium">Ngày bắt đầu:</label>
				<p class="text-white mt-1">{{ \Carbon\Carbon::parse($khuyenmai->ngay_bat_dau)->format('d/m/Y H:i') }}</p>
			</div>
			<div>
				<label class="text-gray-300 font-medium">Ngày kết thúc:</label>
				<p class="text-white mt-1">{{ \Carbon\Carbon::parse($khuyenmai->ngay_ket_thuc)->format('d/m/Y H:i') }}</p>
			</div>
		</div>
		
		<div class="mt-4">
			@php
				$now = \Carbon\Carbon::now();
				$start = \Carbon\Carbon::parse($khuyenmai->ngay_bat_dau);
				$end = \Carbon\Carbon::parse($khuyenmai->ngay_ket_thuc);
			@endphp
			
			@if($now < $start)
				<span class="bg-yellow-600 text-white px-3 py-1 rounded-full text-sm">Chưa bắt đầu</span>
			@elseif($now > $end)
				<span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm">Đã hết hạn</span>
			@else
				<span class="bg-green-600 text-white px-3 py-1 rounded-full text-sm">Đang diễn ra</span>
			@endif
		</div>
	</div>
</div>
@endsection