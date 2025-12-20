<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đặt lại mật khẩu - MovieHub</title>
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a1a2f;
            color: #ffffff;
            overflow: hidden;
            height: 100vh;
        }

        .reset-container {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* Form Section - 40% */
        .reset-form-section {
            width: 40%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: linear-gradient(135deg, #0a1a2f 0%, #0f1f3a 100%);
            position: relative;
            z-index: 10;
        }

        .reset-form-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .reset-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .reset-header p {
            color: #a0a6b1;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #e6e7eb;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0a6b1;
            font-size: 16px;
            z-index: 1;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #ffffff;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #0077c8;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(0, 119, 200, 0.1);
        }

        .input-wrapper input::placeholder {
            color: #6b7280;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0a6b1;
            cursor: pointer;
            z-index: 2;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #0077c8;
        }

        .error-message {
            color: #ff6b6b;
            font-size: 13px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .reset-btn {
            width: 100%;
            padding: 16px;
            background: #0077c8;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .reset-btn:hover {
            background: #0088e0;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 119, 200, 0.3);
        }

        .reset-btn:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            margin-top: 24px;
            color: #a0a6b1;
            font-size: 14px;
        }

        .back-link a {
            color: #ffcc00;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #ffd633;
        }

        /* Carousel Section - 60% */
        .carousel-section {
            width: 60%;
            position: relative;
            overflow: hidden;
        }

        .swiper {
            width: 100%;
            height: 100%;
        }

        .swiper-slide {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.1);
            transition: transform 8s ease-out;
            animation: zoomIn 8s ease-out infinite;
        }

        .swiper-slide-active img {
            transform: scale(1);
            animation: zoomOut 8s ease-out infinite;
        }

        @keyframes zoomIn {
            0% { transform: scale(1.1); }
            100% { transform: scale(1.15); }
        }

        @keyframes zoomOut {
            0% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        .slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom, 
                rgba(10, 26, 47, 0.3) 0%, 
                rgba(10, 26, 47, 0.5) 50%,
                rgba(10, 26, 47, 0.7) 100%
            );
            z-index: 1;
            animation: overlayPulse 4s ease-in-out infinite;
        }

        @keyframes overlayPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.9; }
        }

        .slide-content {
            position: absolute;
            bottom: 60px;
            left: 60px;
            right: 60px;
            z-index: 2;
            color: #ffffff;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .swiper-slide-active .slide-content {
            opacity: 1;
            transform: translateY(0);
            animation: slideUpFadeIn 0.8s ease-out;
        }

        @keyframes slideUpFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-content h2 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 16px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
            line-height: 1.2;
            animation: textGlow 3s ease-in-out infinite;
        }

        @keyframes textGlow {
            0%, 100% {
                text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 204, 0, 0.3);
            }
            50% {
                text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5), 0 0 30px rgba(255, 204, 0, 0.5);
            }
        }

        .slide-content p {
            font-size: 18px;
            color: #e6e7eb;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
            max-width: 600px;
            animation: fadeInDelay 1s ease-out 0.3s both;
        }

        @keyframes fadeInDelay {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .slide-badge {
            display: inline-block;
            background: rgba(255, 204, 0, 0.2);
            border: 1px solid #ffcc00;
            color: #ffcc00;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            animation: badgePulse 2s ease-in-out infinite;
            backdrop-filter: blur(10px);
        }

        @keyframes badgePulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 204, 0, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 8px rgba(255, 204, 0, 0);
            }
        }

        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 204, 0, 0.6);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% {
                transform: translateY(-100px) translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }

        .spotlight {
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 204, 0, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            top: 20%;
            right: 10%;
            z-index: 1;
            animation: spotlightMove 10s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes spotlightMove {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(-50px, 50px) scale(1.2); }
            50% { transform: translate(50px, -30px) scale(0.8); }
            75% { transform: translate(-30px, -50px) scale(1.1); }
        }

        .swiper-pagination {
            bottom: 30px !important;
            z-index: 10;
        }

        .swiper-pagination-bullet {
            background: rgba(255, 255, 255, 0.5);
            opacity: 1;
            width: 12px;
            height: 12px;
            margin: 0 6px;
        }

        .swiper-pagination-bullet-active {
            background: #ffcc00;
            width: 32px;
            border-radius: 6px;
            animation: bulletPulse 2s ease-in-out infinite;
        }

        @keyframes bulletPulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(255, 204, 0, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(255, 204, 0, 0);
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .reset-container {
                flex-direction: column;
            }

            .reset-form-section {
                width: 100%;
                height: auto;
                min-height: 50vh;
            }

            .carousel-section {
                width: 100%;
                height: 50vh;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Form Section - 40% -->
        <div class="reset-form-section">
            <div class="reset-form-wrapper">
                <div class="reset-header">
                    <h1>Đặt lại mật khẩu</h1>
                    <p>Nhập mật khẩu mới cho tài khoản của bạn</p>
                </div>

                @if ($errors->any())
                    <div class="error-message" style="margin-bottom: 20px; padding: 12px; background: rgba(255, 107, 107, 0.1); border: 1px solid rgba(255, 107, 107, 0.3); border-radius: 8px;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email', request('email')) }}" 
                                placeholder="Nhập email của bạn"
                                required
                                autofocus
                            >
                        </div>
                        @error('email')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu mới</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Tối thiểu 6 ký tự"
                                required
                                minlength="6"
                            >
                            <span class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye" id="password-eye"></i>
                            </span>
                        </div>
                        @error('password')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Xác nhận mật khẩu</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                placeholder="Nhập lại mật khẩu"
                                required
                                minlength="6"
                            >
                            <span class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye" id="password_confirmation-eye"></i>
                            </span>
                        </div>
                        @error('password_confirmation')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="reset-btn">
                        <span>Đặt lại mật khẩu</span>
                        <i class="fas fa-key"></i>
                    </button>
                </form>

                <div class="back-link">
                    <a href="{{ route('login.form') }}">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Quay lại đăng nhập
                    </a>
                </div>
            </div>
        </div>

        <!-- Carousel Section - 60% -->
        <div class="carousel-section">
            <div class="spotlight"></div>
            <div class="particles" id="particles"></div>
            
            <div class="swiper reset-carousel">
                <div class="swiper-wrapper">
                    @forelse($movies as $movie)
                        <div class="swiper-slide">
                            <img 
                                src="{{ $movie->poster_url ?? $movie->hinh_anh ?? asset('images/no-poster.svg') }}" 
                                alt="{{ $movie->ten_phim }}"
                                onerror="this.src='{{ asset('images/no-poster.svg') }}'"
                            >
                            <div class="slide-overlay"></div>
                            <div class="slide-content">
                                <div class="slide-badge">
                                    <i class="fas fa-star"></i> {{ number_format($movie->diem_danh_gia ?? 0, 1) }} / 10
                                </div>
                                <h2>{{ $movie->ten_phim }}</h2>
                                <p>{{ \Illuminate\Support\Str::limit($movie->mo_ta ?? 'Phim đang chiếu tại rạp', 150) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <img src="{{ asset('images/logo.png') }}" alt="MovieHub" onerror="this.src='{{ asset('images/no-poster.svg') }}'">
                            <div class="slide-overlay"></div>
                            <div class="slide-content">
                                <div class="slide-badge">
                                    <i class="fas fa-film"></i> Đang chiếu
                                </div>
                                <h2>Chào mừng đến MovieHub</h2>
                                <p>Trải nghiệm điện ảnh tuyệt vời nhất</p>
                            </div>
                        </div>
                    @endforelse
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Initialize Swiper
        const swiper = new Swiper('.reset-carousel', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            },
            speed: 1200,
        });

        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            if (!particlesContainer) return;

            const particleCount = 20;
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (10 + Math.random() * 10) + 's';
                const size = 2 + Math.random() * 3;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particlesContainer.appendChild(particle);
            }
        }

        window.addEventListener('load', createParticles);

        // Parallax effect
        const carouselSection = document.querySelector('.carousel-section');
        if (carouselSection) {
            carouselSection.addEventListener('mousemove', (e) => {
                const rect = carouselSection.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width - 0.5) * 20;
                const y = ((e.clientY - rect.top) / rect.height - 0.5) * 20;
                const spotlight = carouselSection.querySelector('.spotlight');
                if (spotlight) {
                    spotlight.style.transform = `translate(${x}px, ${y}px) scale(1)`;
                }
            });
        }

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + '-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>

