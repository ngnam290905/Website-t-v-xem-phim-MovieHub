@extends('admin.layout')

@section('title', 'Báo cáo doanh thu')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Báo cáo doanh thu</h2>
            <div class="flex items-center gap-3">
                <select id="periodFilter" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-4 py-2 text-sm text-white">
                    <option value="today">Hôm nay</option>
                    <option value="week">Tuần này</option>
                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Tháng này</option>
                    <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Năm nay</option>
                </select>
                <select id="groupBy" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-4 py-2 text-sm text-white">
                    <option value="day" {{ ($groupBy ?? 'day') == 'day' ? 'selected' : '' }}>Theo ngày</option>
                    <option value="month" {{ ($groupBy ?? 'day') == 'month' ? 'selected' : '' }}>Theo tháng</option>
                </select>
                <select id="chartType" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-4 py-2 text-sm text-white">
                    <option value="bar">Biểu đồ cột</option>
                    <option value="line">Biểu đồ đường</option>
                    <option value="pie">Biểu đồ tròn</option>
                </select>
                <button id="exportBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-download mr-1"></i>Xuất Excel
                </button>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Tổng doanh thu</div>
                <div class="text-2xl font-bold text-white">{{ number_format($totalRevenue ?? 0, 0, ',', '.') }}đ</div>
            </div>
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Tổng đơn hàng</div>
                <div class="text-2xl font-bold text-white">{{ number_format($totalBookings ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-1">Trung bình/đơn</div>
                <div class="text-2xl font-bold text-white">
                    {{ $totalBookings > 0 ? number_format($totalRevenue / $totalBookings, 0, ',', '.') : 0 }}đ
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-6 mb-6">
            <div class="h-96 relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-[#1a1d29] border border-[#262833] rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-[#262833]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Ngày/Tháng</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Doanh thu</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Số đơn</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @forelse($revenueData ?? [] as $item)
                    <tr class="hover:bg-[#222533]">
                        <td class="px-6 py-4 text-white">{{ $item->date }}</td>
                        <td class="px-6 py-4 text-right text-white">{{ number_format($item->total_revenue ?? 0, 0, ',', '.') }}đ</td>
                        <td class="px-6 py-4 text-right text-gray-400">{{ $item->booking_count ?? 0 }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-400">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let revenueChart = null;
    const ctx = document.getElementById('revenueChart');
    
    function initChart(chartType = 'bar') {
        if (!ctx) return;
        
        if (revenueChart) {
            revenueChart.destroy();
        }

        const revenueData = @json($revenueData ?? []);
        
        // Kiểm tra dữ liệu rỗng
        if (!revenueData || revenueData.length === 0) {
            ctx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400">Không có dữ liệu để hiển thị</div>';
            return;
        }
        
        const groupBy = '{{ $groupBy ?? "day" }}';
        const labels = revenueData.map(item => {
            // Format label dựa trên groupBy
            const date = new Date(item.date);
            if (isNaN(date.getTime())) {
                return item.date || 'N/A';
            }
            if (groupBy === 'month') {
                const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                                  'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
                return monthNames[date.getMonth()] + '/' + date.getFullYear();
            } else {
                return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' });
            }
        });
        const data = revenueData.map(item => parseFloat(item.total_revenue) || 0);

        const config = {
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: data,
                    backgroundColor: chartType === 'pie' 
                        ? [
                            'rgba(245, 48, 3, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(199, 199, 199, 0.8)',
                            'rgba(83, 102, 255, 0.8)',
                            'rgba(255, 99, 255, 0.8)',
                            'rgba(99, 255, 132, 0.8)',
                            'rgba(255, 206, 86, 0.8)'
                          ]
                        : 'rgba(245, 48, 3, 0.6)',
                    borderColor: chartType === 'pie' ? '#ffffff' : '#F53003',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: chartType === 'pie',
                        labels: {
                            color: '#ffffff',
                            font: { size: 14 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#F53003',
                        borderWidth: 2,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y || context.parsed;
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                            }
                        }
                    }
                },
                scales: chartType === 'pie' ? {} : {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#9ca3af',
                            font: { size: 11 },
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#9ca3af',
                            font: { size: 11 },
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + 'M';
                                } else if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + 'K';
                                }
                                return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                            }
                        }
                    }
                }
            }
        };

        revenueChart = new Chart(ctx, config);
    }

    // Khởi tạo biểu đồ
    initChart('bar');

    // Thay đổi loại biểu đồ
    const chartTypeSelect = document.getElementById('chartType');
    if (chartTypeSelect) {
        chartTypeSelect.addEventListener('change', function() {
            initChart(this.value);
        });
    }

    // Thay đổi filter
    const periodFilter = document.getElementById('periodFilter');
    const groupBySelect = document.getElementById('groupBy');
    
    function updateFilters() {
        const period = periodFilter ? periodFilter.value : 'month';
        const groupBy = groupBySelect ? groupBySelect.value : 'day';
        window.location.href = '{{ route("admin.reports.revenue") }}?period=' + period + '&group_by=' + groupBy;
    }
    
    if (periodFilter) {
        periodFilter.addEventListener('change', updateFilters);
    }
    
    if (groupBySelect) {
        groupBySelect.addEventListener('change', updateFilters);
    }
});
</script>
@endsection
