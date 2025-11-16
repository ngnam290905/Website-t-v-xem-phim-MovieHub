@extends('admin.layout')

@section('title', 'Báo cáo & Thống kê')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300">
                    <i class="fas fa-chart-line text-2xl text-white"></i>
                </div>
                <div>
                    <h2 class="text-4xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
                        Báo cáo & Thống kê
                    </h2>
                    <p class="text-gray-400 mt-2 text-lg">Dashboard quản lý rạp chiếu phim</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-sm text-gray-400">Cập nhật lần cuối</div>
                    <div class="text-white font-semibold text-lg" id="lastUpdate">Vừa xong</div>
                </div>
                <button id="refreshBtn" class="bg-gradient-to-r from-orange-500 to-red-500 hover:brightness-110 text-white px-6 py-3 rounded-xl text-sm font-medium transition-all duration-300 hover:scale-105 shadow-lg">
                    <i class="fas fa-sync-alt mr-2 animate-spin-slow"></i>Làm mới
                </button>
            </div>
        </div>
        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
            <!-- Doanh thu hôm nay - Card chính nổi bật -->
            <div class="lg:col-span-1 bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 hover:border-indigo-500/50 rounded-2xl p-6 relative overflow-hidden shadow-lg hover:shadow-indigo-900/20 transition-all duration-300 hover:scale-105 group">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-transparent"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-2xl">💵</span>
                            <h3 class="text-lg font-semibold text-gray-300">Doanh thu hôm nay</h3>
                        </div>
                        <h3 class="text-4xl font-bold text-white mb-3">
                            {{ number_format($todayRevenue, 0, ',', '.') }}đ
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="bg-emerald-500/10 text-emerald-400 rounded-full px-3 py-1 text-xs font-medium flex items-center gap-1">
                                <i class="fas fa-arrow-up text-xs"></i>
                                12.5% hôm nay
                            </span>
                        </div>
                    </div>
                    <div class="text-4xl text-indigo-400 group-hover:text-indigo-300 transition-colors">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
            
            <!-- Doanh thu tháng này -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 hover:border-emerald-500/50 rounded-2xl p-6 relative overflow-hidden shadow-lg hover:shadow-emerald-900/20 transition-all duration-300 hover:scale-105 group">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-transparent"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-2xl">📊</span>
                            <h3 class="text-lg font-semibold text-gray-300">Doanh thu tháng</h3>
                        </div>
                        <h3 class="text-3xl font-bold text-white mb-3">
                            {{ number_format($monthRevenue, 0, ',', '.') }}đ
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="bg-emerald-500/10 text-emerald-400 rounded-full px-3 py-1 text-xs font-medium flex items-center gap-1">
                                <i class="fas fa-arrow-up text-xs"></i>
                                8.2% tháng này
                            </span>
                        </div>
                    </div>
                    <div class="text-4xl text-emerald-400 group-hover:text-emerald-300 transition-colors">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
            </div>
            
            <!-- Tổng khách hàng -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 hover:border-amber-500/50 rounded-2xl p-6 relative overflow-hidden shadow-lg hover:shadow-amber-900/20 transition-all duration-300 hover:scale-105 group">
                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/10 to-transparent"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-2xl">👥</span>
                            <h3 class="text-lg font-semibold text-gray-300">Tổng khách hàng</h3>
                        </div>
                        <h3 class="text-3xl font-bold text-white mb-3">
                            {{ $totalCustomers }}
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="bg-blue-500/10 text-blue-400 rounded-full px-3 py-1 text-xs font-medium flex items-center gap-1">
                                <i class="fas fa-user-plus text-xs"></i>
                                +5 mới
                            </span>
                        </div>
                    </div>
                    <div class="text-4xl text-amber-400 group-hover:text-amber-300 transition-colors">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <!-- Tổng phim -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 hover:border-purple-500/50 rounded-2xl p-6 relative overflow-hidden shadow-lg hover:shadow-purple-900/20 transition-all duration-300 hover:scale-105 group">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-transparent"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-2xl">🎬</span>
                            <h3 class="text-lg font-semibold text-gray-300">Tổng phim</h3>
                        </div>
                        <h3 class="text-3xl font-bold text-white mb-3">
                            {{ $totalMovies }}
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="bg-purple-500/10 text-purple-400 rounded-full px-3 py-1 text-xs font-medium flex items-center gap-1">
                                <i class="fas fa-play text-xs"></i>
                                3 đang chiếu
                            </span>
                        </div>
                    </div>
                    <div class="text-4xl text-purple-400 group-hover:text-purple-300 transition-colors">
                        <i class="fas fa-film"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <div class="lg:col-span-2">
                <div class="bg-[#1a1d29] border border-[#262833] rounded-2xl p-6 shadow-lg">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full"></div>
                            <h3 class="text-xl font-bold text-white">Biểu đồ doanh thu</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input id="startDate" type="date" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm text-gray-200" />
                                <input id="endDate" type="date" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm text-gray-200" />
                                <button id="applyDateRange" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium">Áp dụng</button>
                            </div>
                            <select id="revenuePeriod" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="today">Hôm nay</option>
                                <option value="week">Tuần này</option>
                                <option value="month" selected>Tháng này</option>
                                <option value="year">Năm nay</option>
                            </select>
                            <button class="bg-[#F53003] hover:bg-[#e02d03] text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-download mr-1"></i>Xuất
                            </button>
                        </div>
                    </div>
                    <div class="h-96 relative">
                        <canvas id="revenueChart"></canvas>
                        <div id="chartLoading" class="absolute inset-0 flex items-center justify-center bg-[#1a1d29]/90 rounded-lg">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500 mx-auto mb-4"></div>
                                <div class="text-gray-400 text-lg font-medium">Đang tải dữ liệu biểu đồ...</div>
                                <div class="text-gray-500 text-sm mt-2">Vui lòng chờ trong giây lát</div>
                            </div>
                        </div>
                        <!-- Skeleton loading cho biểu đồ -->
                        <div id="chartSkeleton" class="absolute inset-0 bg-[#1a1d29] rounded-lg p-6 hidden">
                            <div class="space-y-4">
                                <div class="h-4 bg-gray-700 rounded w-1/3 animate-pulse"></div>
                                <div class="h-64 bg-gray-800 rounded-lg relative overflow-hidden">
                                    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-gray-700 to-transparent animate-pulse"></div>
                                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                        <div class="w-8 h-8 bg-gray-600 rounded-full animate-pulse"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <div class="h-3 bg-gray-700 rounded w-16 animate-pulse"></div>
                                    <div class="h-3 bg-gray-700 rounded w-16 animate-pulse"></div>
                                    <div class="h-3 bg-gray-700 rounded w-16 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="lg:col-span-1">
                <div class="bg-[#1a1d29] border border-[#262833] rounded-2xl p-6 shadow-lg">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full"></div>
                            <h3 class="text-xl font-bold text-white">Top phim bán chạy</h3>
                        </div>
                        <select id="topMoviesPeriod" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <option value="today">Hôm nay</option>
                            <option value="week">Tuần này</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="year">Năm nay</option>
                        </select>
                    </div>
                    <div id="topMoviesList" class="space-y-4">
                        <!-- Top movies will be loaded here -->
                    </div>
                    <div class="mt-6">
                        <h5 class="text-sm text-gray-400 mb-3">Top suất chiếu sử dụng nhiều</h5>
                        <div id="topShowtimesList" class="space-y-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top khách hàng -->
        <div class="mb-8">
            <div class="bg-[#1a1d29] border border-[#262833] rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full"></div>
                        <h3 class="text-xl font-bold text-white">Top khách hàng VIP</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <select id="topCustomersPeriod" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="today">Hôm nay</option>
                            <option value="week">Tuần này</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="year">Năm nay</option>
                        </select>
                        <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-sort mr-1"></i>Sắp xếp
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full" id="topCustomersTable">
                        <thead>
                            <tr class="border-b border-[#262833]">
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Hạng</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Khách hàng</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Liên hệ</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Tổng chi tiêu</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Số vé</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#262833]">
                            <!-- Top customers will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Thống kê doanh số theo hạng thành viên -->
        <div class="mb-8">
            <div class="bg-[#1a1d29] border border-[#262833] rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full"></div>
                        <h3 class="text-xl font-bold text-white">Thống kê doanh số theo hạng thành viên</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="memberStartDate" type="date" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm text-gray-200" />
                        <input id="memberEndDate" type="date" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm text-gray-200" />
                        <select id="memberRevenuePeriod" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="today">Hôm nay</option>
                            <option value="week">Tuần này</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="year">Năm nay</option>
                        </select>
                        <button id="loadMemberRevenue" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-sync-alt mr-1"></i>Tải dữ liệu
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div id="memberRevenueSummary" class="space-y-2">
                        <!-- Summary will be loaded here -->
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full" id="memberRevenueTable">
                        <thead>
                            <tr class="border-b border-[#262833]">
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Hạng thành viên</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Tổng doanh số</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Số vé</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Số thành viên</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#262833]">
                            <!-- Member revenue data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top phim và suất chiếu được sử dụng nhiều nhất -->
        <div class="mb-8">
            <div class="bg-[#1a1d29] border border-[#262833] rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-gradient-to-r from-pink-500 to-rose-500 rounded-full"></div>
                        <h3 class="text-xl font-bold text-white">Top phim & suất chiếu được sử dụng nhiều nhất</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="popularStartDate" type="date" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm text-gray-200" />
                        <input id="popularEndDate" type="date" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-3 py-2 text-sm text-gray-200" />
                        <select id="popularPeriod" class="bg-[#262833] border border-[#3a3d4a] rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            <option value="today">Hôm nay</option>
                            <option value="week">Tuần này</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="year">Năm nay</option>
                        </select>
                        <button id="loadPopular" class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-sync-alt mr-1"></i>Tải dữ liệu
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-semibold text-white mb-4">Top phim (sắp xếp theo số vé)</h4>
                        <div id="popularMoviesList" class="space-y-3">
                            <!-- Popular movies will be loaded here -->
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-white mb-4">Top suất chiếu (sắp xếp theo số vé)</h4>
                        <div id="popularShowtimesList" class="space-y-3">
                            <!-- Popular showtimes will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dữ liệu phim và suất chiếu -->
        <div class="mb-8">
            <div class="bg-[#1a1d29] border border-[#262833] rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full"></div>
                        <h3 class="text-xl font-bold text-white">🎬 Quản lý Phim & Suất chiếu</h3>
                    </div>
                </div>

                <!-- Filter bar -->
                <div class="bg-[#151822] border border-[#262833] rounded-xl p-4 flex flex-wrap items-end gap-3 mb-6">
                    <div>
                        <label class="block text-xs text-[#a6a6b0] mb-1">Thời gian</label>
                        <select id="dataPeriod" class="w-48 bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300">
                            <option value="all">Tất cả</option>
                            <option value="today">Hôm nay</option>
                            <option value="week">Tuần này</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="year">Năm nay</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-[#a6a6b0] mb-1">Từ ngày</label>
                        <input id="dataStartDate" type="date" class="w-48 bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300" />
                    </div>
                    <div>
                        <label class="block text-xs text-[#a6a6b0] mb-1">Đến ngày</label>
                        <input id="dataEndDate" type="date" class="w-48 bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300" />
                    </div>
                    <div>
                        <label class="block text-xs text-[#a6a6b0] mb-1">Trạng thái</label>
                        <select id="dataStatus" class="w-48 bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="dang_chieu">Đang chiếu</option>
                            <option value="sap_chieu">Sắp chiếu</option>
                            <option value="ngung_chieu">Ngừng chiếu</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-[#a6a6b0] mb-1">Tìm phim</label>
                        <input id="searchMovie" type="text" placeholder="Tên phim..." class="w-56 bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300 placeholder-gray-500" />
                    </div>
                    <button id="loadMoviesShowtimesData" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-search mr-1"></i>Tìm kiếm
                    </button>
                </div>

                <!-- Thống kê tổng quan -->
                <div id="dataStatistics" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                    <!-- Statistics will be loaded here -->
                </div>

                <!-- Tabs -->
                <div class="mb-4">
                    <div class="border-b border-[#262833]">
                        <nav class="flex space-x-8" aria-label="Tabs">
                            <button class="data-tab active border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-400" data-tab="movies">
                                📽️ Phim
                            </button>
                            <button class="data-tab border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-400 hover:text-gray-300" data-tab="showtimes">
                                🎫 Suất chiếu
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Movies Tab Content -->
                <div id="moviesTab" class="data-tab-content">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="moviesDataTable">
                            <thead>
                                <tr class="border-b border-slate-700">
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">ID</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Tên phim</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Trạng thái</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Số suất chiếu</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Số vé bán</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Doanh thu</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Đánh giá</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <!-- Movies data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Showtimes Tab Content -->
                <div id="showtimesTab" class="data-tab-content hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="showtimesDataTable">
                            <thead>
                                <tr class="border-b border-slate-700">
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">ID</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Phim</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Phòng</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Thời gian</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Trạng thái</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Số vé bán</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-300">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <!-- Showtimes data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đặt vé gần đây -->
        <div class="mb-8">
            <div class="bg-[#1a1d29] border border-[#262833] rounded-2xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full"></div>
                        <h3 class="text-xl font-bold text-white">Đặt vé gần đây</h3>
                    </div>
                    <button class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-eye mr-1"></i>Xem tất cả
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-700">
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">ID</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Khách hàng</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Phim</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Thời gian đặt</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-300">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($recentBookings as $booking)
                            <tr class="hover:bg-slate-800/60 transition-colors duration-200 group odd:bg-slate-800/30">
                                <td class="py-4 px-6">
                                    <span class="bg-slate-700 text-gray-300 px-3 py-1 rounded-full text-sm font-mono">#{{ $booking->id }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-slate-600 to-slate-800 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                            {{ substr($booking->nguoiDung->ho_ten, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-white font-medium group-hover:text-cyan-300 transition-colors">{{ $booking->nguoiDung->ho_ten }}</div>
                                            <div class="text-gray-500 text-sm">{{ $booking->nguoiDung->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-white font-medium">{{ $booking->suatChieu->phim->ten_phim }}</div>
                                    <div class="text-gray-500 text-sm">{{ $booking->suatChieu->phongChieu->ten_phong }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-gray-300">{{ $booking->created_at->format('d/m/Y') }}</div>
                                    <div class="text-gray-500 text-sm">{{ $booking->created_at->format('H:i') }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    @php
                                        $status = $booking->trang_thai == 1 ? 'confirmed' : ($booking->trang_thai == 0 ? 'pending' : 'cancelled');
                                        $statusConfig = [
                                            'confirmed' => ['bg-emerald-500/10', 'text-emerald-400', 'Đã xác nhận', '✅'],
                                            'pending' => ['bg-amber-500/10', 'text-amber-400', 'Đang chờ', '⏳'],
                                            'cancelled' => ['bg-red-500/10', 'text-red-400', 'Đã hủy', '❌']
                                        ];
                                        $config = $statusConfig[$status];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 {{ $config[0] }} {{ $config[1] }} rounded-full px-3 py-1 text-xs font-medium">
                                        <span>{{ $config[3] }}</span>
                                        {{ $config[2] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
    }
    
    .animate-spin-slow {
        animation: spin 3s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .hover\:brightness-110:hover {
        filter: brightness(1.1);
    }
    
    .bg-gradient-to-r {
        background-image: linear-gradient(to right, var(--tw-gradient-stops));
    }
    
    .text-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card-hover:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
<script>
$(document).ready(function() {
    let revenueChart;
    
    // Load initial data
    loadRevenueData();
    loadTopMovies();
    loadTopCustomers();
    loadTopShowtimes();
    
    // Update last update time
    function updateLastUpdateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('vi-VN', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        });
        $('#lastUpdate').text(timeString);
    }
    
    // Update time every second
    setInterval(updateLastUpdateTime, 1000);
    
    // Refresh button
    $('#refreshBtn').click(function() {
        const $btn = $(this);
        const $icon = $btn.find('i');
        
        // Disable button and show loading
        $btn.prop('disabled', true);
        $icon.addClass('animate-spin');
        $btn.addClass('opacity-75');
        
        // Show loading states
        $('#chartLoading').fadeIn();
        
        // Reload all data
        Promise.all([
            new Promise(resolve => {
                loadRevenueData();
                setTimeout(resolve, 1000);
            }),
            new Promise(resolve => {
                loadTopMovies();
                setTimeout(resolve, 800);
            }),
            new Promise(resolve => {
                loadTopCustomers();
                setTimeout(resolve, 600);
            }),
            new Promise(resolve => {
                loadTopShowtimes();
                setTimeout(resolve, 600);
            })
        ]).then(() => {
            // Re-enable button
            setTimeout(() => {
                $btn.prop('disabled', false);
                $icon.removeClass('animate-spin');
                $btn.removeClass('opacity-75');
                updateLastUpdateTime();
                
                // Show success message
                showNotification('Dữ liệu đã được cập nhật thành công!', 'success');
            }, 500);
        });
    });
    
    // Notification function
    function showNotification(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
            warning: 'bg-yellow-500'
        };
        
        const notification = $(`
            <div class="fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300">
                <div class="flex items-center gap-2">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        
        // Animate in
        setTimeout(() => {
            notification.removeClass('translate-x-full');
        }, 100);
        
        // Auto remove
        setTimeout(() => {
            notification.addClass('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Revenue chart
    function loadRevenueData() {
        const period = $('#revenuePeriod').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        $.ajax({
            url: '{{ route("admin.reports.revenue") }}',
            method: 'GET',
            data: { period: period, start_date: startDate, end_date: endDate },
            success: function(response) {
                updateRevenueChart(response.revenue_data);
            }
        });
    }
    
    function updateRevenueChart(data) {
        // Hide loading
        $('#chartLoading').fadeOut();
        
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        if (revenueChart) {
            revenueChart.destroy();
        }
        
        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: data.map(item => item.total_revenue),
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
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                            }
                        }
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
                            color: '#ffffff',
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
    
    // Top movies
    function loadTopMovies() {
        const period = $('#topMoviesPeriod').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        $.ajax({
            url: '{{ route("admin.reports.top-movies") }}',
            method: 'GET',
            data: { period: period, start_date: startDate, end_date: endDate },
            success: function(response) {
                updateTopMovies(response.top_movies);
            }
        });
    }
    
    function updateTopMovies(movies) {
        let html = '';
        const rankEmojis = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
        const rankColors = ['from-yellow-500 to-orange-500', 'from-gray-400 to-gray-600', 'from-amber-600 to-amber-800', 'from-blue-500 to-blue-700', 'from-purple-500 to-purple-700'];
        
        movies.forEach((movie, index) => {
            const rankEmoji = rankEmojis[index] || `${index + 1}️⃣`;
            const rankColor = rankColors[index] || 'from-gray-500 to-gray-700';
            
            html += `
                <div class="group flex items-center gap-4 p-4 bg-[#262833] rounded-xl hover:bg-[#2a2d3a] transition-all duration-300 hover:scale-105 hover:shadow-lg">
                    <div class="flex-shrink-0 relative">
                        <div class="w-12 h-12 bg-gradient-to-br ${rankColor} rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                            ${rankEmoji}
                        </div>
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-[#F53003] rounded-full flex items-center justify-center text-white text-xs font-bold">
                            ${index + 1}
                        </div>
                    </div>
                    <div class="flex-grow">
                        <div class="flex items-center gap-2 mb-2">
                            <h6 class="text-white font-bold text-lg group-hover:text-yellow-300 transition-colors">${movie.ten_phim}</h6>
                            <span class="bg-green-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                                HOT
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-1 text-green-400">
                                <i class="fas fa-money-bill-wave"></i>
                                <span class="font-semibold">${new Intl.NumberFormat('vi-VN').format(movie.total_revenue)}đ</span>
                            </div>
                            <div class="flex items-center gap-1 text-blue-400">
                                <i class="fas fa-ticket-alt"></i>
                                <span class="font-semibold">${movie.total_tickets} vé</span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 h-2 rounded-full transition-all duration-1000" 
                                     style="width: ${Math.min(100, (movie.total_revenue / (movies[0]?.total_revenue || 1)) * 100)}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="w-16 h-20 bg-gradient-to-br from-gray-700 to-gray-800 rounded-lg flex items-center justify-center text-gray-400">
                            <i class="fas fa-film text-2xl"></i>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#topMoviesList').html(html);
    }
    
    // Top customers
    function loadTopCustomers() {
        const period = $('#topCustomersPeriod').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        $.ajax({
            url: '{{ route("admin.reports.top-customers") }}',
            method: 'GET',
            data: { period: period, start_date: startDate, end_date: endDate, limit: 10 },
            success: function(response) {
                updateTopCustomers(response.top_customers);
            }
        });
    }

    // Top showtimes
    function loadTopShowtimes() {
        const period = $('#topMoviesPeriod').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        $.ajax({
            url: '{{ route("admin.reports.top-showtimes") }}',
            method: 'GET',
            data: { period: period, start_date: startDate, end_date: endDate, limit: 10 },
            success: function(response) {
                updateTopShowtimes(response.top_showtimes);
            }
        });
    }

    function updateTopShowtimes(showtimes) {
        let html = '';
        showtimes.forEach((s, idx) => {
            const time = new Date(s.thoi_gian);
            const dateLabel = time.toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            html += `
                <div class="p-3 bg-[#262833] rounded-lg flex items-center justify-between">
                    <div>
                        <div class="text-white font-semibold">${s.ten_phim}</div>
                        <div class="text-gray-400 text-sm">Suất: ${dateLabel}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-green-400 font-bold">${new Intl.NumberFormat('vi-VN').format(s.total_tickets)} vé</div>
                        <div class="text-gray-500 text-sm">${new Intl.NumberFormat('vi-VN').format(s.total_revenue)}đ</div>
                    </div>
                </div>
            `;
        });
        $('#topShowtimesList').html(html);
    }
    
    function updateTopCustomers(customers) {
        let html = '';
        const vipLevels = ['🥇', '🥈', '🥉', '💎', '⭐'];
        const vipColors = ['from-yellow-500 to-orange-500', 'from-gray-400 to-gray-600', 'from-amber-600 to-amber-800', 'from-blue-500 to-blue-700', 'from-purple-500 to-purple-700'];
        
        customers.forEach((customer, index) => {
            const vipEmoji = vipLevels[index] || '⭐';
            const vipColor = vipColors[index] || 'from-gray-500 to-gray-700';
            const isVip = index < 3;
            
            html += `
                <tr class="hover:bg-[#262833] transition-colors duration-200 group">
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-br ${vipColor} rounded-full flex items-center justify-center text-white font-bold text-sm">
                                ${vipEmoji}
                            </div>
                            <span class="text-gray-300 font-semibold">#${index + 1}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-gray-600 to-gray-800 rounded-full flex items-center justify-center text-white font-bold">
                                ${customer.ho_ten.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div class="text-white font-semibold group-hover:text-purple-300 transition-colors">${customer.ho_ten}</div>
                                ${isVip ? '<div class="text-xs text-yellow-400 font-medium">VIP Customer</div>' : ''}
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-gray-300 text-sm">${customer.email}</div>
                        <div class="text-gray-500 text-xs">${customer.sdt || 'Chưa cập nhật'}</div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-green-400 font-bold text-lg">${new Intl.NumberFormat('vi-VN').format(customer.total_spent)}đ</div>
                        <div class="text-gray-500 text-xs">Tổng chi tiêu</div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-2">
                            <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                ${customer.total_tickets} vé
                            </span>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center gap-1 ${isVip ? 'bg-yellow-600' : 'bg-green-600'} text-white px-3 py-1 rounded-full text-xs font-medium">
                            <i class="fas fa-${isVip ? 'crown' : 'check'}"></i>
                            ${isVip ? 'VIP' : 'Active'}
                        </span>
                    </td>
                </tr>
            `;
        });
        $('#topCustomersTable tbody').html(html);
    }
    
    // Event listeners
    $('#revenuePeriod').change(function() {
        loadRevenueData();
    });
    
    $('#topMoviesPeriod').change(function() {
        loadTopMovies();
        loadTopShowtimes();
    });
    
    $('#topCustomersPeriod').change(function() {
        loadTopCustomers();
    });

    // Apply date range
    $('#applyDateRange').click(function() {
        loadRevenueData();
        loadTopMovies();
        loadTopCustomers();
        loadTopShowtimes();
    });

    // Load member revenue
    function loadMemberRevenue() {
        const period = $('#memberRevenuePeriod').val();
        const startDate = $('#memberStartDate').val();
        const endDate = $('#memberEndDate').val();

        $.ajax({
            url: '{{ route("admin.reports.member-revenue") }}',
            method: 'GET',
            data: { period: period, start_date: startDate, end_date: endDate },
            success: function(response) {
                updateMemberRevenue(response);
            }
        });
    }

    function updateMemberRevenue(data) {
        // Update summary
        const summary = data.total_member_revenue;
        let summaryHtml = '';
        if (summary) {
            summaryHtml = `
                <div class="bg-gradient-to-br from-green-600 to-emerald-600 rounded-xl p-4">
                    <div class="text-sm text-green-100 mb-2">Tổng doanh số thành viên</div>
                    <div class="text-2xl font-bold text-white">${new Intl.NumberFormat('vi-VN').format(summary.total_revenue)}đ</div>
                    <div class="text-xs text-green-100 mt-1">${summary.total_tickets} vé • ${summary.total_members} thành viên</div>
                </div>
            `;
        }
        $('#memberRevenueSummary').html(summaryHtml);

        // Update table
        let tableHtml = '';
        const totalRevenue = data.revenue_by_tier.reduce((sum, item) => sum + parseFloat(item.total_revenue || 0), 0);
        
        data.revenue_by_tier.forEach((tier) => {
            const percentage = totalRevenue > 0 ? ((tier.total_revenue / totalRevenue) * 100).toFixed(1) : 0;
            const tierColors = {
                'Kim cương': 'from-purple-500 to-indigo-600',
                'Vàng': 'from-yellow-400 to-orange-500',
                'Bạc': 'from-gray-400 to-gray-600',
                'Đồng': 'from-amber-600 to-orange-600',
                'Chưa có hạng': 'from-gray-500 to-gray-700'
            };
            const tierColor = tierColors[tier.member_tier] || 'from-gray-500 to-gray-700';
            
            tableHtml += `
                <tr class="hover:bg-[#262833] transition-colors">
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br ${tierColor} rounded-lg flex items-center justify-center text-white font-bold">
                                ${tier.member_tier.charAt(0)}
                            </div>
                            <span class="text-white font-semibold">${tier.member_tier}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-green-400 font-bold">${new Intl.NumberFormat('vi-VN').format(tier.total_revenue)}đ</div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-blue-400 font-semibold">${tier.total_tickets}</div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-purple-400 font-semibold">${tier.total_members}</div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-700 rounded-full h-2">
                                <div class="bg-gradient-to-r ${tierColor} h-2 rounded-full" style="width: ${percentage}%"></div>
                            </div>
                            <span class="text-gray-400 text-sm">${percentage}%</span>
                        </div>
                    </td>
                </tr>
            `;
        });
        $('#memberRevenueTable tbody').html(tableHtml);
    }

    // Load popular movies and showtimes
    function loadPopularMoviesAndShowtimes() {
        const period = $('#popularPeriod').val();
        const startDate = $('#popularStartDate').val();
        const endDate = $('#popularEndDate').val();

        $.ajax({
            url: '{{ route("admin.reports.popular-movies-showtimes") }}',
            method: 'GET',
            data: { period: period, start_date: startDate, end_date: endDate, limit: 10 },
            success: function(response) {
                updatePopularMovies(response.top_movies);
                updatePopularShowtimes(response.top_showtimes);
            }
        });
    }

    function updatePopularMovies(movies) {
        let html = '';
        const rankEmojis = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
        
        movies.forEach((movie, index) => {
            const rankEmoji = rankEmojis[index] || `${index + 1}️⃣`;
            html += `
                <div class="group flex items-center gap-4 p-4 bg-[#262833] rounded-xl hover:bg-[#2a2d3a] transition-all">
                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        ${rankEmoji}
                    </div>
                    <div class="flex-grow">
                        <div class="flex items-center gap-2 mb-2">
                            <h6 class="text-white font-bold group-hover:text-pink-300 transition-colors">${movie.ten_phim}</h6>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="text-green-400">
                                <i class="fas fa-ticket-alt"></i> ${movie.total_tickets} vé
                            </div>
                            <div class="text-blue-400">
                                <i class="fas fa-money-bill-wave"></i> ${new Intl.NumberFormat('vi-VN').format(movie.total_revenue)}đ
                            </div>
                            <div class="text-purple-400">
                                <i class="fas fa-calendar"></i> ${movie.total_showtimes} suất
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#popularMoviesList').html(html || '<div class="text-gray-400 text-center py-8">Không có dữ liệu</div>');
    }

    function updatePopularShowtimes(showtimes) {
        let html = '';
        const rankEmojis = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
        
        showtimes.forEach((showtime, index) => {
            const rankEmoji = rankEmojis[index] || `${index + 1}️⃣`;
            const showtimeDate = new Date(showtime.ngay_chieu + 'T' + showtime.thoi_gian);
            const dateLabel = showtimeDate.toLocaleString('vi-VN', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            html += `
                <div class="group flex items-center gap-4 p-4 bg-[#262833] rounded-xl hover:bg-[#2a2d3a] transition-all">
                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        ${rankEmoji}
                    </div>
                    <div class="flex-grow">
                        <div class="flex items-center gap-2 mb-2">
                            <h6 class="text-white font-bold group-hover:text-rose-300 transition-colors">${showtime.ten_phim}</h6>
                        </div>
                        <div class="text-gray-400 text-xs mb-2">${dateLabel}</div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="text-green-400">
                                <i class="fas fa-ticket-alt"></i> ${showtime.total_tickets} vé
                            </div>
                            <div class="text-blue-400">
                                <i class="fas fa-money-bill-wave"></i> ${new Intl.NumberFormat('vi-VN').format(showtime.total_revenue)}đ
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#popularShowtimesList').html(html || '<div class="text-gray-400 text-center py-8">Không có dữ liệu</div>');
    }

    // Event listeners
    $('#loadMemberRevenue').click(function() {
        loadMemberRevenue();
    });

    $('#memberRevenuePeriod').change(function() {
        loadMemberRevenue();
    });

    $('#loadPopular').click(function() {
        loadPopularMoviesAndShowtimes();
    });

    $('#popularPeriod').change(function() {
        loadPopularMoviesAndShowtimes();
    });

    // Load initial data
    loadMemberRevenue();
    loadPopularMoviesAndShowtimes();
    loadMoviesShowtimesData();

    // Load movies and showtimes data
    function loadMoviesShowtimesData() {
        const period = $('#dataPeriod').val();
        const startDate = $('#dataStartDate').val();
        const endDate = $('#dataEndDate').val();
        const status = $('#dataStatus').val();
        const searchMovie = $('#searchMovie').val();

        $.ajax({
            url: '{{ route("admin.reports.movies-showtimes-data") }}',
            method: 'GET',
            data: { 
                period: period, 
                start_date: startDate, 
                end_date: endDate,
                status: status,
                phim: searchMovie
            },
            success: function(response) {
                updateMoviesShowtimesData(response);
            },
            error: function(xhr) {
                console.error('Error loading data:', xhr);
                showNotification('Có lỗi xảy ra khi tải dữ liệu', 'error');
            }
        });
    }

    function updateMoviesShowtimesData(data) {
        // Update statistics
        const stats = data.statistics;
        let statsHtml = `
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Tổng phim</div>
                <div class="text-2xl font-bold text-white mt-1">${stats.total_movies}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Đang chiếu</div>
                <div class="text-2xl font-bold text-green-400 mt-1">${stats.movies_by_status.dang_chieu || 0}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Sắp chiếu</div>
                <div class="text-2xl font-bold text-blue-400 mt-1">${stats.movies_by_status.sap_chieu || 0}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Ngừng chiếu</div>
                <div class="text-2xl font-bold text-gray-400 mt-1">${stats.movies_by_status.ngung_chieu || 0}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Tổng suất chiếu</div>
                <div class="text-2xl font-bold text-purple-400 mt-1">${stats.total_showtimes}</div>
            </div>
            <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                <div class="text-sm text-[#a6a6b0]">Tổng doanh thu</div>
                <div class="text-lg font-bold text-yellow-400 mt-1">${new Intl.NumberFormat('vi-VN').format(stats.total_revenue || 0)}đ</div>
            </div>
        `;
        $('#dataStatistics').html(statsHtml);

        // Update movies table
        let moviesHtml = '';
        if (data.movies && data.movies.length > 0) {
            data.movies.forEach((movie) => {
                const statusColors = {
                    'dang_chieu': ['text-green-400', 'bg-green-900/30'],
                    'sap_chieu': ['text-blue-400', 'bg-blue-900/30'],
                    'ngung_chieu': ['text-gray-400', 'bg-gray-800']
                };
                const statusTexts = {
                    'dang_chieu': 'Đang chiếu',
                    'sap_chieu': 'Sắp chiếu',
                    'ngung_chieu': 'Ngừng chiếu'
                };
                const statusConfig = statusColors[movie.trang_thai] || ['text-gray-400', 'bg-gray-800'];
                const statusText = statusTexts[movie.trang_thai] || movie.trang_thai;
                const rating = movie.diem_danh_gia > 0 ? movie.diem_danh_gia.toFixed(1) : 'N/A';
                
                moviesHtml += `
                    <tr class="hover:bg-slate-800/60 transition-colors duration-200 group odd:bg-slate-800/30">
                        <td class="py-4 px-6">
                            <span class="bg-slate-700 text-gray-300 px-3 py-1 rounded-full text-sm font-mono">#${movie.id}</span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                ${movie.poster ? `<img src="{{ asset('storage') }}/${movie.poster}" alt="${movie.ten_phim}" class="w-12 h-16 object-cover rounded">` : '<div class="w-12 h-16 bg-gray-700 rounded flex items-center justify-center"><i class="fas fa-film text-gray-500"></i></div>'}
                                <div>
                                    <div class="text-white font-medium group-hover:text-blue-300 transition-colors">${movie.ten_phim}</div>
                                    ${movie.ten_goc ? `<div class="text-gray-500 text-sm">${movie.ten_goc}</div>` : ''}
                                    ${movie.the_loai ? `<div class="text-gray-400 text-xs mt-1">${movie.the_loai}</div>` : ''}
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 ${statusConfig[0]} ${statusConfig[1]} rounded-full text-xs">${statusText}</span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-blue-400 font-semibold">${movie.so_suat_chieu}</div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-green-400 font-semibold">${movie.so_ve_ban}</div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-yellow-400 font-bold">${new Intl.NumberFormat('vi-VN').format(movie.tong_doanh_thu || 0)}đ</div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-2">
                                <span class="text-orange-400 font-semibold">${rating}</span>
                                ${movie.so_luot_danh_gia > 0 ? `<span class="text-gray-400 text-sm">(${movie.so_luot_danh_gia})</span>` : '<span class="text-gray-500 text-sm">(0)</span>'}
                            </div>
                        </td>
                    </tr>
                `;
            });
        } else {
            moviesHtml = '<tr><td colspan="7" class="py-8 text-center text-gray-400">Không có dữ liệu</td></tr>';
        }
        $('#moviesDataTable tbody').html(moviesHtml);

        // Update showtimes table
        let showtimesHtml = '';
        if (data.showtimes && data.showtimes.length > 0) {
            data.showtimes.forEach((showtime) => {
                const startTime = new Date(showtime.thoi_gian_bat_dau);
                const endTime = new Date(showtime.thoi_gian_ket_thuc);
                const timeLabel = startTime.toLocaleString('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const statusColors = {
                    'Sắp chiếu': ['text-blue-400', 'bg-blue-900/30'],
                    'Đang chiếu': ['text-green-400', 'bg-green-900/30'],
                    'Đã kết thúc': ['text-gray-400', 'bg-gray-800']
                };
                const statusConfig = statusColors[showtime.status_text] || ['text-gray-400', 'bg-gray-800'];
                
                showtimesHtml += `
                    <tr class="hover:bg-slate-800/60 transition-colors duration-200 group odd:bg-slate-800/30">
                        <td class="py-4 px-6">
                            <span class="bg-slate-700 text-gray-300 px-3 py-1 rounded-full text-sm font-mono">#${showtime.id}</span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-white font-medium group-hover:text-cyan-300 transition-colors">${showtime.ten_phim}</div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-gray-300">${showtime.ten_phong}</div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-gray-300">${timeLabel}</div>
                            <div class="text-gray-500 text-sm">${startTime.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })} - ${endTime.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}</div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 ${statusConfig[0]} ${statusConfig[1]} rounded-full text-xs">${showtime.status_text}</span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-green-400 font-semibold">${showtime.so_ve_ban}</div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-yellow-400 font-bold">${new Intl.NumberFormat('vi-VN').format(showtime.tong_doanh_thu || 0)}đ</div>
                        </td>
                    </tr>
                `;
            });
        } else {
            showtimesHtml = '<tr><td colspan="7" class="py-8 text-center text-gray-400">Không có dữ liệu</td></tr>';
        }
        $('#showtimesDataTable tbody').html(showtimesHtml);
    }

    // Tab switching
    $('.data-tab').click(function() {
        const targetTab = $(this).data('tab');
        
        // Update tab buttons
        $('.data-tab').removeClass('active border-blue-500 text-blue-400').addClass('border-transparent text-gray-400');
        $(this).addClass('active border-blue-500 text-blue-400').removeClass('border-transparent text-gray-400');
        
        // Show/hide tab content
        $('.data-tab-content').addClass('hidden');
        $('#' + targetTab + 'Tab').removeClass('hidden');
    });

    // Event listeners for movies/showtimes data
    $('#loadMoviesShowtimesData').click(function() {
        loadMoviesShowtimesData();
    });

    $('#dataPeriod').change(function() {
        loadMoviesShowtimesData();
    });

    $('#dataStatus').change(function() {
        loadMoviesShowtimesData();
    });

    // Search on Enter key
    $('#searchMovie').keypress(function(e) {
        if (e.which === 13) {
            loadMoviesShowtimesData();
        }
    });
});
</script>
@endsection
