@extends('admin.layout')

@section('title', 'Báo cáo đồ ăn')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Báo cáo đồ ăn</h2>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Tổng doanh thu đồ ăn</div>
                <div class="text-2xl font-bold text-yellow-400">{{ number_format($foodRevenue ?? 0, 0, ',', '.') }}đ</div>
            </div>
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Tổng combo đã bán</div>
                <div class="text-2xl font-bold text-green-400">{{ number_format($totalCombosSold ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Top Combos -->
        <div class="bg-[#1a1d29] border border-[#262833] rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-[#262833]">
                <h3 class="text-lg font-bold text-white">Top combo bán chạy</h3>
            </div>
            <table class="w-full">
                <thead class="bg-[#262833]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Hạng</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Combo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Số lượng</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Doanh thu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($topCombos ?? [] as $index => $combo)
                    <tr class="hover:bg-[#222533]">
                        <td class="px-6 py-4">
                            <span class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                #{{ $loop->iteration }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($combo->hinh_anh)
                                <img src="{{ asset('storage/' . $combo->hinh_anh) }}" 
                                     alt="{{ $combo->ten_combo }}" 
                                     class="w-12 h-12 object-cover rounded"
                                     onerror="this.style.display='none'">
                                @endif
                                <div class="text-white font-semibold">{{ $combo->ten_combo }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-white">
                            {{ number_format($combo->total_quantity ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-yellow-400 font-bold">
                            {{ number_format($combo->total_revenue ?? 0, 0, ',', '.') }}đ
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
