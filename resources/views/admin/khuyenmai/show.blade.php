@extends('admin.layout')

@section('title', 'Chi tiết mã khuyến mãi')

@section('content')
<div class="mb-6 flex justify-between items-center">
	<h1 class="text-2xl font-bold">Chi tiết mã khuyến mãi</h1>
	<div class="flex gap-2">
		@if(auth()->user() && optional(auth()->user()->vaiTro)->ten === 'admin')
			<a href="{{ route('admin.khuyenmai.edit', $khuyenmai->id) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
				<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
				</svg>
				Chỉnh sửa
			</a>
		@endif
		<a href="{{ route('admin.khuyenmai.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">← Quay lại</a>
	</div>
</div>

<!-- Trạng thái thời gian -->
@php
	$now = \Carbon\Carbon::now();
	$start = \Carbon\Carbon::parse($khuyenmai->ngay_bat_dau);
	$end = \Carbon\Carbon::parse($khuyenmai->ngay_ket_thuc);
	
	if ($now < $start) {
		$statusClass = 'bg-yellow-600';
		$statusText = 'Sắp diễn ra';
		$statusIcon = 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
	} elseif ($now > $end) {
		$statusClass = 'bg-red-600';
		$statusText = 'Đã hết hạn';
		$statusIcon = 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';
	} else {
		$statusClass = 'bg-green-600';
		$statusText = 'Đang hoạt động';
		$statusIcon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
	}
@endphp

<div class="mb-6 {{ $statusClass }} bg-opacity-20 border {{ $statusClass }} rounded-xl p-4 flex items-center gap-3">
	<svg class="w-8 h-8 {{ str_replace('bg-', 'text-', $statusClass) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}"></path>
	</svg>
	<div>
		<p class="font-semibold text-lg {{ str_replace('bg-', 'text-', $statusClass) }}">{{ $statusText }}</p>
		<p class="text-sm text-gray-300">
			@if($now < $start)
				Chương trình bắt đầu từ {{ $start->format('d/m/Y H:i') }}
			@elseif($now > $end)
				Chương trình đã kết thúc vào {{ $end->format('d/m/Y H:i') }}
			@else
				Còn {{ $now->diffInDays($end) }} ngày (đến {{ $end->format('d/m/Y H:i') }})
			@endif
		</p>
	</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
	<!-- Card 1: Thông tin mã -->
	<div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
		<h3 class="text-lg font-semibold mb-4 text-blue-400 flex items-center gap-2">
			<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
			</svg>
			Thông tin mã
		</h3>
		<div class="space-y-4">
			<div>
				<label class="text-gray-400 text-sm">Tên khuyến mãi</label>
				<p class="text-white font-medium mt-1">{{ $khuyenmai->mo_ta ?? 'Không có tên' }}</p>
			</div>
			<div>
				<label class="text-gray-400 text-sm">Mã khuyến mãi</label>
				<p class="text-white font-mono bg-[#222533] px-3 py-2 rounded mt-1 text-lg">{{ $khuyenmai->ma_km }}</p>
			</div>
			<div>
				<label class="text-gray-400 text-sm">Trạng thái hiển thị</label>
				<p class="mt-1">
					@if($khuyenmai->trang_thai)
						<span class="bg-green-600 text-white px-3 py-1 rounded-full text-sm">✓ Kích hoạt</span>
					@else
						<span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm">✗ Ẩn</span>
					@endif
				</p>
			</div>
		</div>
	</div>

	<!-- Card 2: Giá trị giảm -->
	<div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
		<h3 class="text-lg font-semibold mb-4 text-green-400 flex items-center gap-2">
			<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
			</svg>
			Thông tin giảm giá
		</h3>
		<div class="space-y-4">
			<div>
				<label class="text-gray-400 text-sm">Loại giảm giá</label>
				<p class="mt-1">
					@if($khuyenmai->loai_giam === 'phantram')
						<span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm">% Phần trăm</span>
					@else
						<span class="bg-green-600 text-white px-3 py-1 rounded-full text-sm">₫ Cố định</span>
					@endif
				</p>
			</div>
			<div>
				<label class="text-gray-400 text-sm">Giá trị giảm</label>
				<p class="text-3xl font-bold mt-1">
					@if($khuyenmai->loai_giam === 'phantram')
						<span class="text-blue-400">{{ number_format($khuyenmai->gia_tri_giam, 0, ',', '.') }}%</span>
					@else
						<span class="text-green-400">{{ number_format($khuyenmai->gia_tri_giam, 0, ',', '.') }}₫</span>
					@endif
				</p>
			</div>
		</div>
	</div>

	<!-- Card 3: Thời gian -->
	<div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
		<h3 class="text-lg font-semibold mb-4 text-yellow-400 flex items-center gap-2">
			<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
			</svg>
			Thời gian hiệu lực
		</h3>
		<div class="space-y-4">
			<div>
				<label class="text-gray-400 text-sm">Ngày bắt đầu</label>
				<p class="text-white font-medium mt-1">{{ $start->format('d/m/Y H:i') }}</p>
			</div>
			<div>
				<label class="text-gray-400 text-sm">Ngày kết thúc</label>
				<p class="text-white font-medium mt-1">{{ $end->format('d/m/Y H:i') }}</p>
			</div>
			<div>
				<label class="text-gray-400 text-sm">Thời gian còn lại</label>
				<p class="text-white font-medium mt-1">
					@if($now < $start)
						Chưa bắt đầu
					@elseif($now > $end)
						Đã kết thúc
					@else
						{{ $now->diffInDays($end) }} ngày
					@endif
				</p>
			</div>
		</div>
	</div>
</div>

<!-- Điều kiện áp dụng - Highlighted Section -->
<div class="bg-gradient-to-r from-purple-600 to-blue-600 bg-opacity-20 border border-purple-500 rounded-xl p-6 mb-6">
	<h3 class="text-xl font-bold mb-3 text-purple-300 flex items-center gap-2">
		<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
		</svg>
		Điều kiện được áp dụng mã giảm giá
	</h3>
	<div class="bg-[#151822] bg-opacity-60 rounded-lg p-4 border border-purple-400 border-opacity-30">
		@if($khuyenmai->dieu_kien)
			<p class="text-white text-lg leading-relaxed">
				{{ $khuyenmai->dieu_kien }}
			</p>
		@else
			<p class="text-gray-400 italic">
				Không có điều kiện đặc biệt - Áp dụng cho tất cả đơn hàng
			</p>
		@endif
	</div>
	<div class="mt-4 flex items-start gap-2 text-sm text-purple-200">
		<svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
		</svg>
		<p>Khách hàng cần đáp ứng điều kiện trên để được áp dụng mã giảm giá này.</p>
	</div>
</div>
@endsection