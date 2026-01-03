@extends('admin.layout')

@section('title', 'Báo cáo ghế')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Báo cáo ghế</h2>
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

        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Tổng số ghế</div>
                <div class="text-2xl font-bold text-white">{{ number_format($totalSeats ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Ghế đã sử dụng</div>
                <div class="text-2xl font-bold text-green-400">{{ number_format($totalUsedSeats ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Tỷ lệ sử dụng</div>
                <div class="text-2xl font-bold text-blue-400">{{ number_format($overallUsageRate ?? 0, 2) }}%</div>
            </div>
        </div>

        <!-- Table by Seat Type -->
        <div class="bg-[#1a1d29] border border-[#262833] rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-[#262833]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Loại ghế</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Tổng ghế</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Đã sử dụng</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Tỷ lệ sử dụng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($seatTypes ?? [] as $seatType)
                    <tr class="hover:bg-[#222533]">
                        <td class="px-6 py-4 text-white font-semibold">{{ $seatType['ten_loai'] }}</td>
                        <td class="px-6 py-4 text-right text-white">{{ number_format($seatType['total_seats'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-green-400">{{ number_format($seatType['used_seats'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-32 bg-gray-700 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-2 rounded-full" 
                                         style="width: {{ min(100, $seatType['usage_rate'] ?? 0) }}%"></div>
                                </div>
                                <span class="text-white font-bold">{{ number_format($seatType['usage_rate'] ?? 0, 2) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-400">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
