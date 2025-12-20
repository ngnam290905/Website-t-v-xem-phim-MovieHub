@extends('layouts.app')

@section('title', 'Giá vé - MovieHub')

@section('content')
<div class="min-h-screen bg-[#0d0f14] py-12">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                <span class="bg-gradient-to-r from-[#F53003] via-[#ff7a5f] to-[#ffa07a] bg-clip-text text-transparent">
                    Bảng giá vé
                </span>
            </h1>
            <p class="text-[#a6a6b0] text-lg">Thông tin giá vé và combo tại MovieHub</p>
        </div>

        <!-- Seat Type Pricing -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-8 mb-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                <i class="fas fa-chair text-[#F53003]"></i>
                Giá vé theo loại ghế
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($pricing as $item)
                <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-6 hover:border-[#F53003]/50 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-white">{{ $item['name'] }}</h3>
                        <div class="px-3 py-1 bg-[#F53003]/20 text-[#F53003] rounded-full text-xs font-semibold">
                            Hệ số: {{ number_format($item['coefficient'], 1) }}x
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-[#F53003] mb-2">
                        {{ number_format($item['price'], 0, ',', '.') }} đ
                    </div>
                    <p class="text-sm text-[#a6a6b0]">Giá cơ bản: {{ number_format($basePrice, 0, ',', '.') }} đ</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Time-based Pricing -->
        @if($timeRules->count() > 0)
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-8 mb-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                <i class="fas fa-clock text-[#F53003]"></i>
                Giá vé theo thời gian
            </h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#262833]">
                            <th class="text-left py-3 px-4 text-white font-semibold">Loại</th>
                            <th class="text-left py-3 px-4 text-white font-semibold">Thời gian</th>
                            <th class="text-right py-3 px-4 text-white font-semibold">Hệ số</th>
                            <th class="text-right py-3 px-4 text-white font-semibold">Giá vé (ghế thường)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeRules as $rule)
                        <tr class="border-b border-[#262833]/50 hover:bg-[#1a1d24] transition-colors">
                            <td class="py-3 px-4 text-[#a6a6b0]">
                                @if($rule->loai === 'ngay_tuan')
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-calendar-week text-[#F53003]"></i>
                                        Ngày trong tuần
                                    </span>
                                @else
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-clock text-[#F53003]"></i>
                                        Giờ chiếu
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-white">
                                {{ $rule->mo_ta ?? $rule->gia_tri }}
                            </td>
                            <td class="py-3 px-4 text-right text-[#F53003] font-semibold">
                                {{ number_format($rule->he_so, 1) }}x
                            </td>
                            <td class="py-3 px-4 text-right text-white font-semibold">
                                {{ number_format($basePrice * $rule->he_so, 0, ',', '.') }} đ
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Combo Pricing -->
        @if($combos->count() > 0)
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-8 mb-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                <i class="fas fa-shopping-bag text-[#F53003]"></i>
                Combo bắp nước
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($combos as $combo)
                <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-6 hover:border-[#F53003]/50 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">{{ $combo->ten_combo }}</h3>
                        @if($combo->combo_noi_bat)
                            <span class="px-2 py-1 bg-yellow-500 text-black text-xs font-bold rounded">HOT</span>
                        @endif
                    </div>
                    <p class="text-sm text-[#a6a6b0] mb-4 line-clamp-2">{{ $combo->mo_ta }}</p>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-[#F53003]">
                                {{ number_format($combo->gia, 0, ',', '.') }} đ
                            </div>
                            @if($combo->gia_goc)
                                <div class="text-sm text-[#a6a6b0] line-through">
                                    {{ number_format($combo->gia_goc, 0, ',', '.') }} đ
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Note -->
        <div class="bg-gradient-to-r from-[#F53003]/10 to-[#ff7a5f]/10 border border-[#F53003]/20 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <i class="fas fa-info-circle text-[#F53003] text-2xl mt-1"></i>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-2">Lưu ý về giá vé</h3>
                    <ul class="space-y-2 text-[#a6a6b0] text-sm">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-[#F53003] text-xs mt-1"></i>
                            <span>Giá vé có thể thay đổi tùy theo thời gian chiếu và loại ghế</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-[#F53003] text-xs mt-1"></i>
                            <span>Giá vé cuối cùng sẽ được hiển thị khi bạn chọn suất chiếu và ghế</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-[#F53003] text-xs mt-1"></i>
                            <span>Áp dụng các chương trình khuyến mãi (nếu có) khi thanh toán</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-[#F53003] text-xs mt-1"></i>
                            <span>Giá có thể thay đổi mà không báo trước</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-12">
            <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-[#F53003] to-[#ff7849] text-white rounded-lg font-semibold text-lg hover:shadow-lg hover:shadow-[#F53003]/50 transition-all">
                <i class="fas fa-ticket-alt"></i>
                <span>Đặt vé ngay</span>
            </a>
        </div>
    </div>
</div>
@endsection

