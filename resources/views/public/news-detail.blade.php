@extends('layouts.main')

@section('title', $article->tieu_de . ' - MovieHub')

@section('content')
<div class="min-h-screen bg-[#0F1117] py-8">
    <div class="max-w-4xl mx-auto px-4">
        <a href="{{ route('public.news') }}" class="inline-flex items-center text-[#a6a6b0] hover:text-white mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Quay lại tin tức
        </a>

        <!-- Article Header -->
        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 mb-6">
            <div class="flex items-center gap-3 text-sm text-[#a6a6b0] mb-4">
                <span class="px-3 py-1 bg-[#2A2F3A] rounded-full">{{ $article->the_loai ?? 'Tin tức' }}</span>
                <span>{{ $article->ngay_dang->format('d/m/Y') }}</span>
                <span>•</span>
                <span><i class="fas fa-eye"></i> {{ number_format($article->luot_xem) }} lượt xem</span>
                @if($article->tac_gia)
                    <span>•</span>
                    <span>{{ $article->tac_gia }}</span>
                @endif
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ $article->tieu_de }}</h1>
            @if($article->tom_tat)
                <p class="text-xl text-[#a6a6b0]">{{ $article->tom_tat }}</p>
            @endif
        </div>

        <!-- Article Image -->
        @if($article->hinh_anh)
            <div class="mb-6">
                <x-image 
                  src="{{ $article->image_url ?? $article->hinh_anh }}" 
                  alt="{{ $article->tieu_de }}"
                  aspectRatio="16/9"
                  class="w-full rounded-xl"
                  quality="high"
                />
            </div>
        @endif

        <!-- Article Content -->
        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-8 mb-8">
            <div class="prose prose-invert max-w-none">
                {!! $article->noi_dung !!}
            </div>
        </div>

        <!-- Related News -->
        @if($relatedNews->count() > 0)
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-4">Tin liên quan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($relatedNews as $related)
                        <a href="{{ route('public.news.detail', $related->slug) }}" 
                           class="group flex gap-4 bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-4 hover:border-[#F53003] transition-colors">
                            <img src="{{ $related->image_url }}" 
                                 alt="{{ $related->tieu_de }}" 
                                 class="w-24 h-24 object-cover rounded-lg">
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-white group-hover:text-[#F53003] transition-colors line-clamp-2 mb-1">
                                    {{ $related->tieu_de }}
                                </h3>
                                <p class="text-xs text-[#a6a6b0]">{{ $related->ngay_dang->format('d/m/Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.prose {
    color: #E6E7EB;
}
.prose p {
    margin-bottom: 1.5rem;
    line-height: 1.75;
}
.prose h2 {
    color: #FFFFFF;
    font-size: 1.5rem;
    font-weight: bold;
    margin-top: 2rem;
    margin-bottom: 1rem;
}
.prose h3 {
    color: #FFFFFF;
    font-size: 1.25rem;
    font-weight: bold;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}
.prose ul, .prose ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}
.prose li {
    margin-bottom: 0.5rem;
}
.prose a {
    color: #F53003;
    text-decoration: underline;
}
.prose a:hover {
    color: #FF784E;
}
.prose strong {
    color: #FFFFFF;
    font-weight: bold;
}
</style>
@endsection

