@extends('layouts.app')

@section('title', 'Thông tin thành viên')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-4">
                <i class="fas fa-user-circle text-purple-400 mr-2"></i>
                Thông tin thành viên
            </h1>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500 text-green-300 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Card thông tin cá nhân -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700">
                <h2 class="text-xl font-bold text-white mb-6">
                    <i class="fas fa-id-card mr-2 text-blue-400"></i>
                    Thông tin cá nhân
                </h2>
                
                <div class="flex flex-col items-center mb-6">
                    @if($user->hinh_anh)
                        <img src="{{ asset('storage/' . $user->hinh_anh) }}" alt="Avatar" 
                            class="w-24 h-24 rounded-full border-4 border-purple-500 mb-4">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mb-4">
                            <span class="text-white text-3xl font-bold">{{ substr($user->ho_ten, 0, 1) }}</span>
                        </div>
                    @endif
                    
                    <h3 class="text-white font-bold text-lg">{{ $user->ho_ten }}</h3>
                    <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                </div>

                <div class="space-y-3">
                    @if($user->sdt)
                    <div class="flex items-center text-gray-300">
                        <i class="fas fa-phone w-6 text-green-400"></i>
                        <span>{{ $user->sdt }}</span>
                    </div>
                    @endif
                    
                    @if($user->ngay_sinh)
                    <div class="flex items-center text-gray-300">
                        <i class="fas fa-birthday-cake w-6 text-pink-400"></i>
                        <span>{{ date('d/m/Y', strtotime($user->ngay_sinh)) }}</span>
                    </div>
                    @endif
                    
                    <div class="flex items-center text-gray-300">
                        <i class="fas fa-calendar-check w-6 text-blue-400"></i>
                        <span>Thành viên từ {{ date('d/m/Y', strtotime($user->ngay_dang_ky_thanh_vien)) }}</span>
                    </div>
                </div>
            </div>

            <!-- Card hạng thành viên -->
            <div class="bg-gradient-to-br from-yellow-600/20 to-orange-600/20 backdrop-blur-sm rounded-2xl p-6 border border-yellow-500/30">
                <h2 class="text-xl font-bold text-white mb-6">
                    <i class="fas fa-crown mr-2 text-yellow-400"></i>
                    Hạng thành viên
                </h2>
                
                @php
                    $hangColor = [
                        'Đồng' => 'from-orange-700 to-orange-900 border-orange-600 text-orange-300',
                        'Bạc' => 'from-gray-600 to-gray-800 border-gray-500 text-gray-300',
                        'Vàng' => 'from-yellow-700 to-yellow-900 border-yellow-600 text-yellow-300',
                        'Bạch Kim' => 'from-blue-700 to-purple-900 border-purple-600 text-purple-300',
                    ];
                    $currentColor = $hangColor[$hangThanhVien->ten_hang] ?? $hangColor['Đồng'];
                @endphp
                
                <div class="bg-gradient-to-r {{ $currentColor }} border rounded-xl p-6 mb-6 text-center">
                    <i class="fas fa-medal text-5xl mb-3"></i>
                    <h3 class="text-2xl font-bold mb-2">{{ $hangThanhVien->ten_hang }}</h3>
                    <p class="text-sm opacity-80">{{ $hangThanhVien->uu_dai }}</p>
                </div>

                <!-- Điểm hiện tại -->
                <div class="bg-gray-800/50 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-300">Điểm hiện tại</span>
                        <span class="text-white font-bold text-2xl">{{ number_format($diemThanhVien->tong_diem) }}</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Hết hạn: {{ date('d/m/Y', strtotime($diemThanhVien->ngay_het_han)) }}
                    </p>
                </div>

                <!-- Progress bar đến hạng tiếp theo -->
                @php
                    $nextTier = [
                        'Đồng' => ['name' => 'Bạc', 'points' => 1000],
                        'Bạc' => ['name' => 'Vàng', 'points' => 5000],
                        'Vàng' => ['name' => 'Bạch Kim', 'points' => 10000],
                        'Bạch Kim' => null,
                    ];
                    $next = $nextTier[$hangThanhVien->ten_hang] ?? null;
                @endphp

                @if($next)
                    <div class="bg-gray-800/50 rounded-lg p-4">
                        <p class="text-gray-300 text-sm mb-2">
                            Còn <span class="text-yellow-400 font-bold">{{ number_format($next['points'] - $diemThanhVien->tong_diem) }}</span> 
                            điểm để lên hạng {{ $next['name'] }}
                        </p>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-gradient-to-r from-yellow-400 to-orange-500 h-2 rounded-full" 
                                style="width: {{ min(100, ($diemThanhVien->tong_diem / $next['points']) * 100) }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="bg-gray-800/50 rounded-lg p-4 text-center">
                        <i class="fas fa-trophy text-yellow-400 text-3xl mb-2"></i>
                        <p class="text-gray-300">Bạn đã đạt hạng cao nhất!</p>
                    </div>
                @endif
            </div>

            <!-- Card ưu đãi -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700">
                <h2 class="text-xl font-bold text-white mb-6">
                    <i class="fas fa-gift mr-2 text-pink-400"></i>
                    Ưu đãi của bạn
                </h2>
                
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-purple-600/20 to-pink-600/20 border border-purple-500/30 rounded-lg p-4">
                        <i class="fas fa-percentage text-purple-400 text-2xl mb-2"></i>
                        <h3 class="text-white font-semibold mb-1">Giảm giá vé</h3>
                        <p class="text-gray-300 text-sm">
                            @switch($hangThanhVien->ten_hang)
                                @case('Bạc') 5% @break
                                @case('Vàng') 10% @break
                                @case('Bạch Kim') 15% @break
                                @default 0%
                            @endswitch
                        </p>
                    </div>

                    <div class="bg-gradient-to-r from-blue-600/20 to-cyan-600/20 border border-blue-500/30 rounded-lg p-4">
                        <i class="fas fa-coins text-blue-400 text-2xl mb-2"></i>
                        <h3 class="text-white font-semibold mb-1">Tích điểm</h3>
                        <p class="text-gray-300 text-sm">1 điểm / 10.000 VNĐ</p>
                    </div>

                    @if(in_array($hangThanhVien->ten_hang, ['Vàng', 'Bạch Kim']))
                    <div class="bg-gradient-to-r from-green-600/20 to-emerald-600/20 border border-green-500/30 rounded-lg p-4">
                        <i class="fas fa-ticket-alt text-green-400 text-2xl mb-2"></i>
                        <h3 class="text-white font-semibold mb-1">Ưu tiên đặt vé</h3>
                        <p class="text-gray-300 text-sm">Đặt vé sớm 24h</p>
                    </div>
                    @endif

                    @if($hangThanhVien->ten_hang === 'Bạch Kim')
                    <div class="bg-gradient-to-r from-orange-600/20 to-red-600/20 border border-orange-500/30 rounded-lg p-4">
                        <i class="fas fa-vip text-orange-400 text-2xl mb-2"></i>
                        <h3 class="text-white font-semibold mb-1">VIP Lounge</h3>
                        <p class="text-gray-300 text-sm">Phòng chờ đặc biệt</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Lịch sử đặt vé -->
        <div class="mt-6 bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700">
            <h2 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-history mr-2 text-green-400"></i>
                Lịch sử đặt vé
            </h2>
            
            @if($lichSuDatVe->count() > 0)
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($lichSuDatVe as $datVe)
                        <div class="bg-gray-700/30 rounded-lg p-4 border border-gray-600 hover:border-purple-500 transition">
                            <div class="flex gap-4">
                                @if($datVe->poster)
                                    <img src="{{ $datVe->poster_url ?? asset('storage/' . $datVe->poster) ?? asset('images/no-poster.svg') }}" alt="{{ $datVe->ten_phim }}" 
                                        class="w-20 h-28 object-cover rounded"
                                        onerror="this.src='{{ asset('images/no-poster.svg') }}'">
                                @else
                                    <div class="w-20 h-28 bg-gray-600 rounded flex items-center justify-center">
                                        <i class="fas fa-film text-gray-400 text-2xl"></i>
                                    </div>
                                @endif
                                
                                <div class="flex-1">
                                    <h3 class="text-white font-semibold mb-2 line-clamp-2">{{ $datVe->ten_phim }}</h3>
                                    <p class="text-gray-400 text-sm mb-1">
                                        <i class="far fa-calendar mr-1"></i>
                                        {{ date('d/m/Y', strtotime($datVe->ngay_chieu)) }}
                                    </p>
                                    <p class="text-gray-400 text-sm mb-2">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ date('H:i', strtotime($datVe->gio_chieu)) }}
                                    </p>
                                    
                                    @if($datVe->trang_thai == 1)
                                        <span class="inline-block bg-green-500/20 text-green-300 text-xs px-2 py-1 rounded">
                                            <i class="fas fa-check-circle mr-1"></i>Đã thanh toán
                                        </span>
                                    @else
                                        <span class="inline-block bg-yellow-500/20 text-yellow-300 text-xs px-2 py-1 rounded">
                                            <i class="fas fa-clock mr-1"></i>Chờ thanh toán
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-gray-600 text-6xl mb-4"></i>
                    <p class="text-gray-400">Bạn chưa có lịch sử đặt vé nào</p>
                    <a href="{{ route('home') }}" 
                        class="inline-block mt-4 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-film mr-2"></i>Đặt vé ngay
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
