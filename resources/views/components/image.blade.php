@props([
    'src' => null,
    'alt' => '',
    'class' => '',
    'lazy' => true,
    'aspectRatio' => null, // e.g., '16/9', '4/3', '1/1', '2/3'
    'placeholder' => true,
    'quality' => 'high', // 'low', 'medium', 'high'
    'fallback' => '/images/no-poster.svg'
])

@php
    use Illuminate\Support\Facades\Storage;
    
    // Xử lý fallback - đảm bảo là đường dẫn đầy đủ
    $finalFallback = $fallback;
    if ($fallback && !filter_var($fallback, FILTER_VALIDATE_URL) && !str_starts_with($fallback, '/')) {
        $finalFallback = asset($fallback);
    } elseif ($fallback && !filter_var($fallback, FILTER_VALIDATE_URL)) {
        $finalFallback = asset($fallback);
    }
    
    // Xử lý đường dẫn ảnh - đảm bảo hiển thị tất cả ảnh
    $finalSrc = $finalFallback;
    
    // Kiểm tra src có hợp lệ không (không null, không empty, không chỉ có khoảng trắng)
    $srcValid = $src && trim($src) !== '' && $src !== 'null' && $src !== 'undefined';
    
    if ($srcValid) {
        $src = trim($src);
        $foundValidPath = false;
        
        // Nếu là URL đầy đủ (http/https)
        if (filter_var($src, FILTER_VALIDATE_URL)) {
            $finalSrc = $src;
            $foundValidPath = true;
        }
        // Nếu bắt đầu bằng / hoặc storage/
        elseif (str_starts_with($src, '/') || str_starts_with($src, 'storage/')) {
            $finalSrc = asset($src);
            $foundValidPath = true;
        }
        // Nếu là đường dẫn tương đối trong storage
        elseif (str_contains($src, 'posters/') || str_contains($src, 'images/')) {
            $finalSrc = asset('storage/' . $src);
            $foundValidPath = true;
        }
        // Thử các đường dẫn khác
        else {
            try {
                // Thử storage path
                if (Storage::disk('public')->exists($src)) {
                    $finalSrc = Storage::disk('public')->url($src);
                    $foundValidPath = true;
                }
                // Thử public/images
                elseif (file_exists(public_path('images/' . $src))) {
                    $finalSrc = asset('images/' . $src);
                    $foundValidPath = true;
                }
                // Thử storage/posters
                elseif (Storage::disk('public')->exists('posters/' . $src)) {
                    $finalSrc = Storage::disk('public')->url('posters/' . $src);
                    $foundValidPath = true;
                }
                // Nếu không tìm thấy file, dùng fallback
                else {
                    $finalSrc = $finalFallback;
                }
            } catch (\Exception $e) {
                // Nếu có lỗi, luôn dùng fallback
                $finalSrc = $finalFallback;
            }
        }
    }
    
    // Generate aspect ratio style
    $aspectStyle = $aspectRatio ? "aspect-ratio: {$aspectRatio};" : '';
    
    // Generate quality-based classes
    $qualityClasses = [
        'low' => 'image-quality-low',
        'medium' => 'image-quality-medium',
        'high' => 'image-quality-high'
    ];
    $qualityClass = $qualityClasses[$quality] ?? 'image-quality-high';
    
    // Generate unique ID for this image
    $imageId = 'img-' . uniqid();
@endphp

<div class="image-wrapper {{ $class }}" style="{{ $aspectStyle }}" data-image-id="{{ $imageId }}">
    @if($placeholder)
        <!-- Skeleton Placeholder -->
        <div class="image-skeleton" data-skeleton="{{ $imageId }}">
            <div class="skeleton-shimmer"></div>
        </div>
    @endif
    
    <!-- Actual Image -->
    <img 
        id="{{ $imageId }}"
        src="{{ $finalSrc }}"
        alt="{{ $alt }}"
        class="image-content {{ $qualityClass }} {{ $placeholder ? 'opacity-0' : '' }}"
        @if($lazy) loading="lazy" @endif
        onload="this.classList.add('loaded'); this.parentElement.querySelector('[data-skeleton=\'{{ $imageId }}\']')?.classList.add('hidden');"
        onerror="this.onerror=null; this.src='{{ $finalFallback }}'; this.classList.add('loaded'); this.parentElement.querySelector('[data-skeleton=\'{{ $imageId }}\']')?.classList.add('hidden');"
        decoding="async"
    >
    
    <!-- Blur-up Effect -->
    @if($placeholder && $finalSrc && $finalSrc !== $finalFallback)
        <img 
            src="{{ $finalSrc }}" 
            alt=""
            class="image-blur"
            loading="eager"
            aria-hidden="true"
            onerror="this.style.display='none';"
        >
    @endif
</div>

<style>
    .image-wrapper {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #1a1d24 0%, #151822 100%);
    }
    
    .image-content {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: opacity 0.3s ease, transform 0.3s ease;
        display: block;
    }
    
    .image-content.loaded {
        opacity: 1 !important;
    }
    
    .image-quality-high {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
    
    .image-quality-medium {
        image-rendering: auto;
    }
    
    .image-quality-low {
        image-rendering: pixelated;
    }
    
    .image-skeleton {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #1a1d24 0%, #151822 100%);
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .image-skeleton.hidden {
        display: none;
    }
    
    .skeleton-shimmer {
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            rgba(255, 255, 255, 0) 0%,
            rgba(255, 255, 255, 0.05) 50%,
            rgba(255, 255, 255, 0) 100%
        );
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }
    
    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }
        100% {
            background-position: 200% 0;
        }
    }
    
    .image-blur {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: blur(20px);
        transform: scale(1.1);
        opacity: 0.3;
        z-index: 0;
        pointer-events: none;
    }
    
    .image-content.loaded ~ .image-blur {
        opacity: 0;
        transition: opacity 0.5s ease;
    }
    
    /* Hover effects */
    .image-wrapper:hover .image-content {
        transform: scale(1.05);
    }
    
    /* Responsive image optimization */
    @media (max-width: 768px) {
        .image-content {
            image-rendering: auto;
        }
    }
    
    /* Print styles */
    @media print {
        .image-skeleton,
        .image-blur {
            display: none;
        }
        
        .image-content {
            opacity: 1 !important;
        }
    }
</style>

