
@extends('layouts.app')
@section('title', 'Lịch sử đặt vé')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-blue-700">Lịch sử đặt vé của bạn</h2>
    <div class="overflow-x-auto rounded-lg shadow-lg bg-[#1b1d24]">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-blue-900 text-white">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Mã đơn</th>
                    <th class="px-4 py-3 text-left font-semibold">Phim</th>
                    <th class="px-4 py-3 text-left font-semibold">Suất chiếu</th>
                    <th class="px-4 py-3 text-left font-semibold">Ghế</th>
                    <th class="px-4 py-3 text-left font-semibold">Combo</th>
                    <th class="px-4 py-3 text-left font-semibold">Tổng tiền</th>
                    <th class="px-4 py-3 text-left font-semibold">Thời gian đặt</th>
                    <th class="px-4 py-3 text-left font-semibold">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="bg-[#1b1d24] divide-y divide-gray-800">
                @forelse($datVes as $datVe)
                <tr class="hover:bg-blue-900 transition">
                    <td class="px-4 py-3 font-bold text-blue-300">#{{ $datVe->id }}</td>
                    <td class="px-4 py-3 text-white">{{ $datVe->suatChieu->phim->ten_phim ?? '-' }}</td>
                    <td class="px-4 py-3 text-white">{{ $datVe->suatChieu->start_time ? date('d/m/Y H:i', strtotime($datVe->suatChieu->start_time)) : '-' }}</td>
                    <td class="px-4 py-3 text-white">
                        <div class="flex flex-wrap gap-2">
                        @foreach($datVe->chiTietDatVe as $ct)
                            <span class="inline-block bg-blue-500 text-white rounded px-2 py-1 text-xs font-semibold">{{ $ct->ghe->so_ghe ?? '-' }}</span>
                        @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 text-white">
                        <div class="flex flex-wrap gap-2">
                        @foreach($datVe->chiTietCombo as $combo)
                            <span class="inline-block bg-purple-400 text-white rounded px-2 py-1 text-xs font-semibold">{{ $combo->combo->ten ?? '-' }} (x{{ $combo->so_luong }})</span>
                        @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 font-bold text-orange-400">{{ number_format($datVe->tong_tien, 0, ',', '.') }} VNĐ</td>
                    <td class="px-4 py-3 text-gray-300">{{ $datVe->created_at ? date('d/m/Y H:i', strtotime($datVe->created_at)) : '-' }}</td>
                    <td class="px-4 py-3">
                        @if($datVe->trang_thai == 1)
                            <span class="inline-block bg-green-600 text-white rounded px-2 py-1 text-xs font-semibold">Đã thanh toán</span>
                        @else
                            <span class="inline-block bg-red-400 text-white rounded px-2 py-1 text-xs font-semibold">Chưa thanh toán</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-gray-400">Bạn chưa có đơn đặt vé nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6 flex justify-center">
        {{ $datVes->links('pagination::tailwind') }}
    </div>
</div>
@endsection
