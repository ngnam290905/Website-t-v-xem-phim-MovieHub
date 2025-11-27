@extends('layouts.main')

@section('title', 'Debug Bookings - MovieHub')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <h1 class="text-2xl font-bold mb-6 text-white">Debug - Tất cả đặt vé trong hệ thống</h1>
        
        @php
            $allBookings = \App\Models\DatVe::with(['nguoiDung', 'suatChieu.phim'])
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get();
            $currentUserId = Auth::id();
        @endphp
        
        <div class="mb-4 p-4 bg-blue-900/20 rounded border border-blue-500/30">
            <p class="text-white"><strong>User hiện tại:</strong> ID = {{ $currentUserId }}, Email = {{ Auth::user()->email ?? 'N/A' }}</p>
            <p class="text-white"><strong>Tổng bookings trong DB:</strong> {{ \App\Models\DatVe::count() }}</p>
            <p class="text-white"><strong>Bookings của user:</strong> {{ \App\Models\DatVe::where('id_nguoi_dung', $currentUserId)->count() }}</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-white">
                <thead class="bg-[#2f3240]">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">User ID</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Phim</th>
                        <th class="px-4 py-2">Tổng tiền</th>
                        <th class="px-4 py-2">Trạng thái</th>
                        <th class="px-4 py-2">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allBookings as $booking)
                        <tr class="border-b border-[#2f3240] {{ $booking->id_nguoi_dung == $currentUserId ? 'bg-green-900/20' : '' }}">
                            <td class="px-4 py-2">{{ $booking->id }}</td>
                            <td class="px-4 py-2">
                                {{ $booking->id_nguoi_dung ?? 'NULL' }}
                                @if($booking->id_nguoi_dung == $currentUserId)
                                    <span class="text-green-400 text-xs">(YOU)</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ optional($booking->nguoiDung)->email ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ optional(optional($booking->suatChieu)->phim)->ten_phim ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ number_format($booking->tong_tien ?? 0, 0) }}đ</td>
                            <td class="px-4 py-2">
                                @switch($booking->trang_thai)
                                    @case(0) <span class="text-yellow-300">Chờ xác nhận</span> @break
                                    @case(1) <span class="text-green-300">Đã xác nhận</span> @break
                                    @case(2) <span class="text-red-300">Đã hủy</span> @break
                                    @default <span class="text-gray-300">{{ $booking->trang_thai }}</span>
                                @endswitch
                            </td>
                            <td class="px-4 py-2">{{ $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : 'NULL' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            <a href="{{ route('user.bookings') }}" class="inline-block px-6 py-3 bg-[#F53003] text-white rounded-lg hover:bg-[#ff4d4d]">
                Quay lại lịch sử đặt vé
            </a>
        </div>
    </div>
</div>
@endsection
