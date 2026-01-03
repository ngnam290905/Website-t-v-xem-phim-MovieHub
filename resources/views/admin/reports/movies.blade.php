@extends('admin.layout')

@section('title', 'Báo cáo phim')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Báo cáo phim</h2>
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

        <!-- Table -->
        <div class="bg-[#1a1d29] border border-[#262833] rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-[#262833]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Hạng</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Phim</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Doanh thu</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Vé đã bán</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Số đơn</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($moviesData ?? [] as $index => $movie)
                    <tr class="hover:bg-[#222533]">
                        <td class="px-6 py-4">
                            <span class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                #{{ $loop->iteration }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $movie->poster_url ?? asset('images/no-poster.svg') }}" 
                                     alt="{{ $movie->ten_phim }}" 
                                     class="w-12 h-16 object-cover rounded"
                                     onerror="this.src='{{ asset('images/no-poster.svg') }}'">
                                <div>
                                    <div class="text-white font-semibold">{{ $movie->ten_phim }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-yellow-400 font-bold">
                            {{ number_format($movie->total_revenue ?? 0, 0, ',', '.') }}đ
                        </td>
                        <td class="px-6 py-4 text-right text-white">
                            {{ number_format($movie->total_tickets ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-gray-400">
                            {{ number_format($movie->total_bookings ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-400">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
