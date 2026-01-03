@extends('admin.layout')

@section('title', 'Báo cáo suất chiếu')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Báo cáo suất chiếu</h2>
            <div class="flex items-center gap-3">
                <select id="periodFilter" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-4 py-2 text-sm text-white">
                    <option value="today">Hôm nay</option>
                    <option value="week">Tuần này</option>
                    <option value="month" selected>Tháng này</option>
                    <option value="year">Năm nay</option>
                </select>
                <button id="exportBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-download mr-1"></i>Xuất Excel
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Tổng số suất chiếu</div>
                <div class="text-2xl font-bold text-white">{{ number_format($totalShowtimes ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Suất hiệu quả (>50%)</div>
                <div class="text-2xl font-bold text-green-400">{{ number_format($effectiveShowtimes ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Suất kém (<20%)</div>
                <div class="text-2xl font-bold text-red-400">{{ number_format($poorShowtimes ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-[#1a1d29] border border-[#262833] rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-[#262833]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Phim</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Phòng</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Thời gian</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Vé đã bán</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Tổng ghế</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Tỷ lệ lấp đầy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($showtimesDetail ?? [] as $showtime)
                    <tr class="hover:bg-[#222533]">
                        <td class="px-6 py-4 text-white">{{ $showtime->ten_phim }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $showtime->ten_phong }}</td>
                        <td class="px-6 py-4 text-gray-300">
                            {{ \Carbon\Carbon::parse($showtime->thoi_gian_bat_dau)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right text-white">{{ number_format($showtime->tickets_sold ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-gray-400">{{ number_format($showtime->total_seats ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">
                            @php
                                $rate = $showtime->occupancy_rate ?? 0;
                                $color = $rate >= 50 ? 'text-green-400' : ($rate >= 20 ? 'text-yellow-400' : 'text-red-400');
                            @endphp
                            <span class="{{ $color }} font-bold">{{ number_format($rate, 2) }}%</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-400">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
