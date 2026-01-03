@extends('admin.layout')

@section('title', 'Báo cáo tổng quan')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-chart-line text-2xl text-white"></i>
                </div>
                <div>
                    <h2 class="text-4xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
                        Báo cáo tổng quan
                    </h2>
                    <p class="text-gray-400 mt-2 text-lg">Dashboard quản lý rạp chiếu phim</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <!-- Filter -->
                <select id="periodFilter" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-4 py-2 text-sm text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="today">Hôm nay</option>
                    <option value="week">Tuần này</option>
                    <option value="month" selected>Tháng này</option>
                    <option value="year">Năm nay</option>
                    <option value="custom">Tùy chọn</option>
                </select>
                <div id="customDateRange" class="hidden flex items-center gap-2">
                    <input type="date" id="startDate" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm text-white">
                    <span class="text-gray-400">đến</span>
                    <input type="date" id="endDate" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm text-white">
                    <button id="applyDateRange" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">Áp dụng</button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Tổng doanh thu -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-2xl p-6 hover:border-indigo-500/50 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-indigo-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-indigo-400 text-xl"></i>
                    </div>
                </div>
                <div class="text-sm text-gray-400 mb-1">Tổng doanh thu</div>
                <div class="text-3xl font-bold text-white" id="totalRevenue">
                    {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}đ
                </div>
            </div>

            <!-- Số vé bán -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-2xl p-6 hover:border-green-500/50 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-green-400 text-xl"></i>
                    </div>
                </div>
                <div class="text-sm text-gray-400 mb-1">Số vé bán</div>
                <div class="text-3xl font-bold text-white" id="totalTickets">
                    {{ number_format($totalTickets ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <!-- Tỷ lệ lấp đầy ghế -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-2xl p-6 hover:border-blue-500/50 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-blue-400 text-xl"></i>
                    </div>
                </div>
                <div class="text-sm text-gray-400 mb-1">Tỷ lệ lấp đầy ghế</div>
                <div class="text-3xl font-bold text-white" id="occupancyRate">
                    {{ number_format($occupancyRate ?? 0, 2) }}%
                </div>
            </div>

            <!-- Doanh thu đồ ăn -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-2xl p-6 hover:border-yellow-500/50 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-utensils text-yellow-400 text-xl"></i>
                    </div>
                </div>
                <div class="text-sm text-gray-400 mb-1">Doanh thu đồ ăn</div>
                <div class="text-3xl font-bold text-white" id="foodRevenue">
                    {{ number_format($foodRevenue ?? 0, 0, ',', '.') }}đ
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu theo tháng -->
        <div class="bg-[#1a1d29] border border-[#262833] rounded-xl p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Biểu đồ doanh thu theo tháng</h3>
                <div class="flex items-center gap-2">
                    <select id="chartType" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm text-white">
                        <option value="bar">Biểu đồ cột</option>
                        <option value="line">Biểu đồ đường</option>
                        <option value="pie">Biểu đồ tròn</option>
                    </select>
                </div>
            </div>
            <div class="h-96 relative">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.reports.revenue') }}" class="bg-[#1a1d29] border border-[#262833] rounded-xl p-4 hover:border-blue-500/50 transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-bar text-blue-400"></i>
                    </div>
                    <div>
                        <div class="text-white font-semibold">Doanh thu</div>
                        <div class="text-gray-400 text-sm">Biểu đồ doanh thu</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports.movies') }}" class="bg-[#1a1d29] border border-[#262833] rounded-xl p-4 hover:border-purple-500/50 transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-film text-purple-400"></i>
                    </div>
                    <div>
                        <div class="text-white font-semibold">Phim</div>
                        <div class="text-gray-400 text-sm">Thống kê phim</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports.showtimes') }}" class="bg-[#1a1d29] border border-[#262833] rounded-xl p-4 hover:border-green-500/50 transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-green-400"></i>
                    </div>
                    <div>
                        <div class="text-white font-semibold">Suất chiếu</div>
                        <div class="text-gray-400 text-sm">Hiệu quả suất chiếu</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports.payments') }}" class="bg-[#1a1d29] border border-[#262833] rounded-xl p-4 hover:border-yellow-500/50 transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-yellow-400"></i>
                    </div>
                    <div>
                        <div class="text-white font-semibold">Thanh toán</div>
                        <div class="text-gray-400 text-sm">Thống kê giao dịch</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dữ liệu doanh thu theo tháng
    const revenueByMonth = @json($revenueByMonth ?? []);
    let monthlyChart = null;

    // Khởi tạo biểu đồ
    function initMonthlyChart(chartType = 'bar') {
        const ctx = document.getElementById('monthlyRevenueChart');
        if (!ctx) return;

        if (monthlyChart) {
            monthlyChart.destroy();
        }

        // Kiểm tra dữ liệu rỗng
        if (!revenueByMonth || revenueByMonth.length === 0) {
            ctx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400">Không có dữ liệu để hiển thị</div>';
            return;
        }

        const labels = revenueByMonth.map(item => item.month);
        const data = revenueByMonth.map(item => parseFloat(item.revenue) || 0);

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
                            font: {
                                size: 14
                            }
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
                            font: {
                                size: 11
                            }
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
                            font: {
                                size: 11
                            },
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

        monthlyChart = new Chart(ctx, config);
    }

    // Khởi tạo biểu đồ mặc định
    initMonthlyChart('bar');

    // Thay đổi loại biểu đồ
    const chartTypeSelect = document.getElementById('chartType');
    if (chartTypeSelect) {
        chartTypeSelect.addEventListener('change', function() {
            initMonthlyChart(this.value);
        });
    }

    const periodFilter = document.getElementById('periodFilter');
    if (periodFilter) {
        periodFilter.addEventListener('change', function() {
            const customDateRange = document.getElementById('customDateRange');
            if (this.value === 'custom') {
                customDateRange.classList.remove('hidden');
            } else {
                customDateRange.classList.add('hidden');
                loadDashboard(this.value);
            }
        });
    }

    const applyDateRange = document.getElementById('applyDateRange');
    if (applyDateRange) {
        applyDateRange.addEventListener('click', function() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            if (startDate && endDate) {
                loadDashboard('custom', startDate, endDate);
            }
        });
    }

    function loadDashboard(period, startDate = null, endDate = null) {
        const params = { period: period };
        if (startDate) params.start_date = startDate;
        if (endDate) params.end_date = endDate;

        fetch('{{ route("admin.reports.dashboard") }}?' + new URLSearchParams(params), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            location.reload();
        })
        .catch(error => {
            console.error('Error loading dashboard:', error);
        });
    }
});
</script>
@endsection
