@extends('layouts.main')

@section('title', 'Combo - MovieHub')

@section('content')
<div class="min-h-screen bg-[#0F1117] py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Combo bắp nước</h1>
            <p class="text-[#a6a6b0]">Chọn combo phù hợp để trải nghiệm trọn vẹn</p>
        </div>

        <!-- Featured Combos -->
        @if($featuredCombos->count() > 0)
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-star text-yellow-400"></i>
                    <span>Combo nổi bật</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($featuredCombos as $combo)
                        <div class="bg-[#161A23] border-2 border-yellow-500/50 rounded-xl p-6 hover:border-yellow-400 transition-all group">
                            <div class="relative mb-4">
                                <x-image 
                                  src="{{ $combo->image_url ?? ($combo->anh ?? $combo->hinh_anh) }}" 
                                  alt="{{ $combo->ten }}"
                                  aspectRatio="16/9"
                                  class="w-full rounded-lg"
                                  quality="high"
                                  fallback="/images/default-combo.jpg"
                                />
                                <div class="absolute top-2 right-2 bg-yellow-500 text-black px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-star"></i> Nổi bật
                                </div>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">{{ $combo->ten }}</h3>
                            @if($combo->mo_ta)
                                <p class="text-[#a6a6b0] text-sm mb-4 line-clamp-2">{{ $combo->mo_ta }}</p>
                            @endif
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-2xl font-bold text-[#F53003]">{{ number_format($combo->gia) }}đ</p>
                                    @if($combo->gia_goc && $combo->gia_goc > $combo->gia)
                                        <p class="text-sm text-[#a6a6b0] line-through">{{ number_format($combo->gia_goc) }}đ</p>
                                        <span class="inline-block mt-1 px-2 py-0.5 bg-green-500/20 text-green-400 text-xs font-semibold rounded">
                                            -{{ round((1 - $combo->gia / $combo->gia_goc) * 100) }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($combo->yeu_cau_it_nhat_ve)
                                <p class="text-xs text-[#a6a6b0] mb-2">
                                    <i class="fas fa-info-circle"></i> Yêu cầu tối thiểu {{ $combo->yeu_cau_it_nhat_ve }} vé
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Regular Combos -->
        @if($regularCombos->count() > 0)
            <div>
                <h2 class="text-2xl font-bold text-white mb-4">Tất cả combo</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($regularCombos as $combo)
                        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 hover:border-[#F53003] transition-all group">
                            <div class="relative mb-4">
                                <x-image 
                                  src="{{ $combo->image_url ?? ($combo->anh ?? $combo->hinh_anh) }}" 
                                  alt="{{ $combo->ten }}"
                                  aspectRatio="16/9"
                                  class="w-full rounded-lg"
                                  quality="high"
                                  fallback="/images/default-combo.jpg"
                                />
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2">{{ $combo->ten }}</h3>
                            @if($combo->mo_ta)
                                <p class="text-[#a6a6b0] text-sm mb-4 line-clamp-2">{{ $combo->mo_ta }}</p>
                            @endif
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xl font-bold text-[#F53003]">{{ number_format($combo->gia) }}đ</p>
                                    @if($combo->gia_goc && $combo->gia_goc > $combo->gia)
                                        <p class="text-xs text-[#a6a6b0] line-through">{{ number_format($combo->gia_goc) }}đ</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($featuredCombos->count() === 0 && $regularCombos->count() === 0)
            <div class="text-center py-16">
                <i class="fas fa-box-open text-6xl text-[#a6a6b0] mb-4"></i>
                <p class="text-[#a6a6b0] text-lg">Hiện tại không có combo nào</p>
            </div>
        @endif
    </div>
</div>
@endsection

