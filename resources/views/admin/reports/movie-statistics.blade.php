@extends('admin.layout')

@section('title', 'Thống kê phim - ' . $movie->ten_phim)

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.reports.dashboard') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
                    <i class="fas fa-chart-line mr-2"></i>
                    Dashboard báo cáo
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
                    <span class="ml-1 text-sm font-medium text-white md:ml-2">Thống kê phim</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-20 bg-gradient-to-br from-gray-700 to-gray-800 rounded-lg overflow-hidden flex-shrink-0">
                    <img src="{{ $movie->poster_url ?? asset('images/no-poster.svg') }}" 
                         alt="{{ $movie->ten_phim }}" 
                         class="w-full h-full object-cover"
                         onerror="this.src='{{ asset('images/no-poster.svg') }}'">
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">{{ $movie->ten_phim }}</h1>
                    <p class="text-[#a6a6b0] text-sm">{{ $movie->the_loai ?? 'N/A' }}</p>
                </div>
            </div>
            <a href="{{ route('admin.reports.dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Thống kê - Báo cáo -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-chart-bar text-[#F53003]"></i>
                Thống kê - Báo cáo
            </h2>
            <div class="flex items-center gap-2">
                <select id="statistics-period" class="px-4 py-2 rounded-lg border border-[#2f3240] bg-[#0f0f12] text-white text-sm focus:outline-none focus:border-[#F53003]">
                    <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>Tất cả thời gian</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hôm nay</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Tuần này</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Tháng này</option>
                    <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Năm nay</option>
                    <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Tùy chọn</option>
                </select>
                <div id="custom-date-range" class="hidden flex items-center gap-2">
                    <input type="date" id="start-date" value="{{ request('start_date') }}" class="px-3 py-2 rounded-lg border border-[#2f3240] bg-[#0f0f12] text-white text-sm focus:outline-none focus:border-[#F53003]">
                    <span class="text-[#a6a6b0]">đến</span>
                    <input type="date" id="end-date" value="{{ request('end_date') }}" class="px-3 py-2 rounded-lg border border-[#2f3240] bg-[#0f0f12] text-white text-sm focus:outline-none focus:border-[#F53003]">
                    <button id="apply-date-range" class="px-4 py-2 rounded-lg bg-[#F53003] text-white text-sm hover:bg-[#e02a00]">
                        Áp dụng
                    </button>
                </div>
            </div>
        </div>

        @if(isset($statistics))
            <div id="statistics-content">
                <!-- Cards thống kê -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <!-- Số suất chiếu -->
                    <div class="bg-gradient-to-br from-blue-600/20 to-blue-800/20 border border-blue-500/30 rounded-xl p-5 hover:border-blue-500/50 transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xs text-[#a6a6b0] uppercase tracking-wide">Số suất chiếu</div>
                            <i class="fas fa-calendar-alt text-blue-400 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-white mb-1" id="stat-total-showtimes">
                            {{ number_format($statistics['statistics']['total_showtimes']) }}
                        </div>
                        <div class="text-xs text-[#a6a6b0]">suất chiếu đã tạo</div>
                    </div>

                    <!-- Tổng vé đã bán -->
                    <div class="bg-gradient-to-br from-green-600/20 to-green-800/20 border border-green-500/30 rounded-xl p-5 hover:border-green-500/50 transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xs text-[#a6a6b0] uppercase tracking-wide">Vé đã bán</div>
                            <i class="fas fa-ticket-alt text-green-400 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-white mb-1" id="stat-total-tickets">
                            {{ number_format($statistics['statistics']['total_tickets_sold']) }}
                        </div>
                        <div class="text-xs text-[#a6a6b0]">vé đã thanh toán</div>
                    </div>

                    <!-- Tổng doanh thu -->
                    <div class="bg-gradient-to-br from-yellow-600/20 to-orange-800/20 border border-yellow-500/30 rounded-xl p-5 hover:border-yellow-500/50 transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xs text-[#a6a6b0] uppercase tracking-wide">Doanh thu</div>
                            <i class="fas fa-money-bill-wave text-yellow-400 text-xl"></i>
                        </div>
                        <div class="text-2xl font-bold text-white mb-1" id="stat-total-revenue">
                            {{ number_format($statistics['statistics']['total_revenue'], 0, ',', '.') }} <span class="text-sm">VNĐ</span>
                        </div>
                        <div class="text-xs text-[#a6a6b0]">tổng doanh thu</div>
                    </div>

                    <!-- Tỷ lệ lấp đầy -->
                    <div class="bg-gradient-to-br from-purple-600/20 to-pink-800/20 border border-purple-500/30 rounded-xl p-5 hover:border-purple-500/50 transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-xs text-[#a6a6b0] uppercase tracking-wide">Tỷ lệ lấp đầy</div>
                            <i class="fas fa-percentage text-purple-400 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-white mb-1" id="stat-occupancy-rate">
                            {{ number_format($statistics['statistics']['occupancy_rate'], 2) }}%
                        </div>
                        <div class="text-xs text-[#a6a6b0]">ghế đã bán / tổng ghế</div>
                    </div>
                </div>

                <!-- Chi tiết doanh thu -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chair text-blue-400"></i>
                            </div>
                            <div>
                                <div class="text-xs text-[#a6a6b0] mb-1">Doanh thu từ vé</div>
                                <div class="text-xl font-semibold text-white" id="stat-seat-revenue">
                                    {{ number_format($statistics['statistics']['seat_revenue'], 0, ',', '.') }} VNĐ
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-orange-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cookie-bite text-orange-400"></i>
                            </div>
                            <div>
                                <div class="text-xs text-[#a6a6b0] mb-1">Doanh thu từ combo</div>
                                <div class="text-xl font-semibold text-white" id="stat-combo-revenue">
                                    {{ number_format($statistics['statistics']['combo_revenue'], 0, ',', '.') }} VNĐ
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biểu đồ vé bán theo thời gian -->
                @if(!empty($statistics['chart_data']['tickets_by_date']))
                    <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-6">
                        <div class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <i class="fas fa-chart-line text-[#F53003]"></i>
                            Biểu đồ vé bán theo thời gian
                        </div>
                        <canvas id="tickets-chart" height="100"></canvas>
                    </div>
                @endif
            </div>
        @else
            <div class="text-[#a6a6b0] text-center py-12">
                <i class="fas fa-chart-line text-5xl mb-4 opacity-50"></i>
                <p class="text-lg">Đang tải dữ liệu thống kê...</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let ticketsChart = null;

    // Period selector
    document.getElementById('statistics-period').addEventListener('change', function() {
        const period = this.value;
        if (period === 'custom') {
            document.getElementById('custom-date-range').classList.remove('hidden');
        } else {
            document.getElementById('custom-date-range').classList.add('hidden');
            loadStatistics(period);
        }
    });

    // Apply custom date range
    document.getElementById('apply-date-range')?.addEventListener('click', function() {
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;
        if (startDate && endDate) {
            loadStatistics('custom', startDate, endDate);
        }
    });

    function loadStatistics(period, startDate = null, endDate = null) {
        const url = new URL('{{ route("admin.reports.movie-statistics", $movie->id) }}', window.location.origin);
        url.searchParams.set('period', period);
        if (startDate) url.searchParams.set('start_date', startDate);
        if (endDate) url.searchParams.set('end_date', endDate);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update statistics
            document.getElementById('stat-total-showtimes').textContent = 
                new Intl.NumberFormat('vi-VN').format(data.statistics.total_showtimes);
            document.getElementById('stat-total-tickets').textContent = 
                new Intl.NumberFormat('vi-VN').format(data.statistics.total_tickets_sold);
            document.getElementById('stat-total-revenue').innerHTML = 
                new Intl.NumberFormat('vi-VN').format(data.statistics.total_revenue) + ' <span class="text-sm">VNĐ</span>';
            document.getElementById('stat-occupancy-rate').textContent = 
                data.statistics.occupancy_rate.toFixed(2) + '%';
            document.getElementById('stat-seat-revenue').textContent = 
                new Intl.NumberFormat('vi-VN').format(data.statistics.seat_revenue) + ' VNĐ';
            document.getElementById('stat-combo-revenue').textContent = 
                new Intl.NumberFormat('vi-VN').format(data.statistics.combo_revenue) + ' VNĐ';

            // Update chart if data exists
            if (data.chart_data.tickets_by_date && Object.keys(data.chart_data.tickets_by_date).length > 0) {
                updateChart(data.chart_data.tickets_by_date);
            }
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
    }

    function updateChart(ticketsByDate) {
        const ctx = document.getElementById('tickets-chart');
        if (!ctx) return;

        const labels = Object.keys(ticketsByDate).sort();
        const data = labels.map(date => ticketsByDate[date]);

        if (ticketsChart) {
            ticketsChart.destroy();
        }

        ticketsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [{
                    label: 'Số vé bán',
                    data: data,
                    borderColor: '#F53003',
                    backgroundColor: 'rgba(245, 48, 3, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#F53003',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#F53003',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#ffffff'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#ffffff'
                        }
                    }
                }
            }
        });
    }

    // Initialize chart if data exists
    @if(isset($statistics) && !empty($statistics['chart_data']['tickets_by_date']))
        updateChart(@json($statistics['chart_data']['tickets_by_date']));
    @endif
</script>
@endsection

