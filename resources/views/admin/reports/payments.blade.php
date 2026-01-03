@extends('admin.layout')

@section('title', 'Báo cáo thanh toán')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Báo cáo thanh toán</h2>
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
                <div class="text-gray-400 text-sm mb-1">Tổng giao dịch</div>
                <div class="text-2xl font-bold text-white">{{ number_format($totalTransactions ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Tổng doanh thu</div>
                <div class="text-2xl font-bold text-yellow-400">{{ number_format($totalPaymentRevenue ?? 0, 0, ',', '.') }}đ</div>
            </div>
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Trung bình/giao dịch</div>
                <div class="text-2xl font-bold text-blue-400">
                    {{ $totalTransactions > 0 ? number_format($totalPaymentRevenue / $totalTransactions, 0, ',', '.') : 0 }}đ
                </div>
            </div>
        </div>

        <!-- Payment Methods Chart -->
        <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-white mb-4">Phân bổ theo phương thức thanh toán</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($paymentMethods ?? [] as $method)
                <div class="bg-[#262833] rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">{{ $method->method_name ?? $method->phuong_thuc }}</div>
                    <div class="text-xl font-bold text-white mb-2">{{ number_format($method->total_amount ?? 0, 0, ',', '.') }}đ</div>
                    <div class="text-gray-500 text-xs">{{ number_format($method->transaction_count ?? 0, 0, ',', '.') }} giao dịch</div>
                </div>
                @empty
                <div class="col-span-3 text-center text-gray-400 py-8">Không có dữ liệu</div>
                @endforelse
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-[#1a1d29] border border-[#262833] rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-[#262833]">
                <h3 class="text-lg font-bold text-white">Chi tiết giao dịch</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#262833]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Thời gian</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Khách hàng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Phim</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Số tiền</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Phương thức</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#262833]">
                        @forelse($transactions ?? [] as $transaction)
                        <tr class="hover:bg-[#222533]">
                            <td class="px-6 py-4 text-gray-300 text-sm">
                                {{ $transaction->thoi_gian ? \Carbon\Carbon::parse($transaction->thoi_gian)->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-white">
                                {{ optional($transaction->datVe)->nguoiDung->ho_ten ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-gray-300">
                                {{ optional(optional($transaction->datVe)->suatChieu)->phim->ten_phim ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right text-yellow-400 font-bold">
                                {{ number_format($transaction->so_tien ?? 0, 0, ',', '.') }}đ
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-xs">
                                    {{ $transaction->phuong_thuc ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($transaction->trang_thai == 1)
                                <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs">Thành công</span>
                                @else
                                <span class="bg-red-500/20 text-red-400 px-3 py-1 rounded-full text-xs">Thất bại</span>
                                @endif
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
</div>
@endsection
