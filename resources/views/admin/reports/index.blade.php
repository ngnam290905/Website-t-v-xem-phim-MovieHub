@extends('admin.layout')

@section('title', 'Báo cáo & Thống kê')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center gap-3 mb-6">
            <i class="fas fa-chart-pie text-2xl text-[#F53003]"></i>
            <h2 class="text-2xl font-bold">Báo cáo & Thống kê</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-600 rounded-lg p-6 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xl font-bold text-white">Doanh thu</h4>
                        <p class="text-blue-100">Thống kê doanh thu theo thời gian</p>
                    </div>
                    <div class="text-4xl text-blue-200">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <a href="{{ route('admin.reports.dashboard') }}" class="inline-block mt-4 bg-white text-blue-600 px-4 py-2 rounded text-sm font-medium hover:bg-blue-50 transition">
                    Xem chi tiết
                </a>
            </div>
            
            <div class="bg-green-600 rounded-lg p-6 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xl font-bold text-white">Top phim</h4>
                        <p class="text-green-100">Phim bán chạy nhất</p>
                    </div>
                    <div class="text-4xl text-green-200">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                <a href="{{ route('admin.reports.dashboard') }}" class="inline-block mt-4 bg-white text-green-600 px-4 py-2 rounded text-sm font-medium hover:bg-green-50 transition">
                    Xem chi tiết
                </a>
            </div>
            
            <div class="bg-yellow-600 rounded-lg p-6 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xl font-bold text-white">Top khách hàng</h4>
                        <p class="text-yellow-100">Khách hàng chi tiêu nhiều nhất</p>
                    </div>
                    <div class="text-4xl text-yellow-200">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <a href="{{ route('admin.reports.dashboard') }}" class="inline-block mt-4 bg-white text-yellow-600 px-4 py-2 rounded text-sm font-medium hover:bg-yellow-50 transition">
                    Xem chi tiết
                </a>
            </div>
        </div>
        
        <div class="bg-[#1a1d29] border border-[#262833] rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Tính năng báo cáo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h5 class="text-white font-medium mb-3">Thống kê doanh thu</h5>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500"></i>
                            <span class="text-gray-300">Doanh thu theo ngày</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500"></i>
                            <span class="text-gray-300">Doanh thu theo tháng</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500"></i>
                            <span class="text-gray-300">Doanh thu theo năm</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500"></i>
                            <span class="text-gray-300">Biểu đồ trực quan</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-white font-medium mb-3">Thống kê phim & khách hàng</h5>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500"></i>
                            <span class="text-gray-300">Top phim bán chạy</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500"></i>
                            <span class="text-gray-300">Top khách hàng VIP</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500"></i>
                            <span class="text-gray-300">Số vé bán ra</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-check text-green-500"></i>
                            <span class="text-gray-300">Lịch sử đặt vé</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
