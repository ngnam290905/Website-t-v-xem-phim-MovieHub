@props([
    'src' => null,
    'alt' => '',
    'class' => '',
    'lazy' => true,
    'aspectRatio' => null, // e.g., '16/9', '4/3', '1/1', '2/3'
    'placeholder' => true,
    'quality' => 'high', // 'low', 'medium', 'high'
    'fallback' => '/images/no-poster.jpg'
])

@php
    // Determine if image is external URL
    $isExternal = $src && filter_var($src, FILTER_VALIDATE_URL);
    
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
        src="{{ $src ?: $fallback }}"
        alt="{{ $alt }}"
        class="image-content {{ $qualityClass }} {{ $placeholder ? 'opacity-0' : '' }}"
        @if($lazy) loading="lazy" @endif
        onload="this.classList.add('loaded'); this.parentElement.querySelector('[data-skeleton=\'{{ $imageId }}\']')?.classList.add('hidden');"
        onerror="this.onerror=null; this.src='{{ $fallback }}'; this.classList.add('loaded'); this.parentElement.querySelector('[data-skeleton=\'{{ $imageId }}\']')?.classList.add('hidden');"
        decoding="async"
    >
    
    <!-- Blur-up Effect -->
    @if($placeholder && $src)
        <img 
            src="{{ $src }}" 
            alt=""
            class="image-blur"
            loading="eager"
            aria-hidden="true"
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

