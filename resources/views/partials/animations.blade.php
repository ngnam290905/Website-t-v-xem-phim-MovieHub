<!-- Advanced Animations and Effects -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

<script>
  // Initialize GSAP
  gsap.registerPlugin(ScrollTrigger);
  
  class AnimationController {
    constructor() {
      this.init();
    }
    
    init() {
      this.setupScrollAnimations();
      this.setupHoverEffects();
      this.setupSkeletonLoading();
      this.setupPageTransitions();
      this.setupFloatingElements();
    }
    
    setupScrollAnimations() {
      // Hero section animations
      gsap.fromTo('.hero-slide h1', 
        { y: 100, opacity: 0 },
        { y: 0, opacity: 1, duration: 1, ease: 'power3.out' }
      );
      
      gsap.fromTo('.hero-slide p', 
        { y: 50, opacity: 0 },
        { y: 0, opacity: 1, duration: 1, delay: 0.3, ease: 'power3.out' }
      );
      
      gsap.fromTo('.hero-slide .flex', 
        { y: 30, opacity: 0 },
        { y: 0, opacity: 1, duration: 1, delay: 0.6, ease: 'power3.out' }
      );
      
      // Movie cards animation
      gsap.fromTo('.movie-card', 
        { y: 50, opacity: 0, scale: 0.9 },
        { 
          y: 0, 
          opacity: 1, 
          scale: 1, 
          duration: 0.8, 
          stagger: 0.1, 
          ease: 'power2.out',
          scrollTrigger: {
            trigger: '.movie-card',
            start: 'top 80%',
            end: 'bottom 20%',
            toggleActions: 'play none none reverse'
          }
        }
      );
      
      // Countdown timer animation
      gsap.fromTo('.countdown-timer div', 
        { scale: 0, rotation: 180 },
        { 
          scale: 1, 
          rotation: 0, 
          duration: 0.6, 
          stagger: 0.1, 
          ease: 'back.out(1.7)',
          delay: 1
        }
      );
      
      // Footer elements animation
      gsap.fromTo('footer .grid > div', 
        { y: 30, opacity: 0 },
        { 
          y: 0, 
          opacity: 1, 
          duration: 0.8, 
          stagger: 0.2, 
          ease: 'power2.out',
          scrollTrigger: {
            trigger: 'footer',
            start: 'top 80%',
            toggleActions: 'play none none reverse'
          }
        }
      );
    }
    
    setupHoverEffects() {
      // Movie card flip effect
      document.querySelectorAll('.movie-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
          gsap.to(card, { 
            y: -10, 
            scale: 1.05, 
            rotationY: 5, 
            duration: 0.3, 
            ease: 'power2.out' 
          });
        });
        
        card.addEventListener('mouseleave', () => {
          gsap.to(card, { 
            y: 0, 
            scale: 1, 
            rotationY: 0, 
            duration: 0.3, 
            ease: 'power2.out' 
          });
        });
      });
      
      // Button glow effects
      document.querySelectorAll('.glow-button').forEach(button => {
        button.addEventListener('mouseenter', () => {
          gsap.to(button, { 
            boxShadow: '0 0 30px rgba(245, 48, 3, 0.6)',
            duration: 0.3 
          });
        });
        
        button.addEventListener('mouseleave', () => {
          gsap.to(button, { 
            boxShadow: '0 0 0px rgba(245, 48, 3, 0)',
            duration: 0.3 
          });
        });
      });
      
      // Social icons bounce
      document.querySelectorAll('.social-link').forEach(link => {
        link.addEventListener('mouseenter', () => {
          gsap.to(link, { 
            scale: 1.2, 
            rotation: 10, 
            duration: 0.3, 
            ease: 'back.out(1.7)' 
          });
        });
        
        link.addEventListener('mouseleave', () => {
          gsap.to(link, { 
            scale: 1, 
            rotation: 0, 
            duration: 0.3, 
            ease: 'back.out(1.7)' 
          });
        });
      });
    }
    
    setupSkeletonLoading() {
      // Create skeleton loading for movie cards
      this.createSkeletonLoader();
      
      // Simulate loading
      setTimeout(() => {
        this.hideSkeletonLoader();
      }, 2000);
    }
    
    createSkeletonLoader() {
      const movieContainer = document.getElementById('movies-container');
      if (!movieContainer) return;
      
      const skeletonHTML = `
        <div class="skeleton-loader grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
          ${Array(6).fill(0).map(() => `
            <div class="skeleton-card bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden">
              <div class="skeleton h-80 bg-gradient-to-r from-[#2a2d3a] via-[#3a3d4a] to-[#2a2d3a] animate-pulse"></div>
              <div class="p-4 space-y-3">
                <div class="skeleton h-6 bg-gradient-to-r from-[#2a2d3a] via-[#3a3d4a] to-[#2a2d3a] animate-pulse rounded"></div>
                <div class="skeleton h-4 bg-gradient-to-r from-[#2a2d3a] via-[#3a3d4a] to-[#2a2d3a] animate-pulse rounded w-3/4"></div>
                <div class="flex gap-2">
                  <div class="skeleton h-8 bg-gradient-to-r from-[#2a2d3a] via-[#3a3d4a] to-[#2a2d3a] animate-pulse rounded w-20"></div>
                  <div class="skeleton h-8 bg-gradient-to-r from-[#2a2d3a] via-[#3a3d4a] to-[#2a2d3a] animate-pulse rounded w-20"></div>
                </div>
              </div>
            </div>
          `).join('')}
        </div>
      `;
      
      movieContainer.innerHTML = skeletonHTML;
    }
    
    hideSkeletonLoader() {
      const skeletonLoader = document.querySelector('.skeleton-loader');
      if (skeletonLoader) {
        gsap.to(skeletonLoader, { 
          opacity: 0, 
          duration: 0.5, 
          onComplete: () => {
            skeletonLoader.remove();
          }
        });
      }
    }
    
    setupPageTransitions() {
      // Page transition effects
      document.addEventListener('DOMContentLoaded', () => {
        gsap.fromTo('body', 
          { opacity: 0 },
          { opacity: 1, duration: 0.5, ease: 'power2.out' }
        );
      });
      
      // Link hover effects
      document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', (e) => {
          if (link.href && !link.href.includes('#')) {
            e.preventDefault();
            gsap.to('body', { 
              opacity: 0, 
              duration: 0.3, 
              onComplete: () => {
                window.location.href = link.href;
              }
            });
          }
        });
      });
    }
    
    setupFloatingElements() {
      // Floating particles effect
      this.createFloatingParticles();
      
      // Parallax scrolling
      gsap.to('.hero-slide .absolute', {
        yPercent: -50,
        ease: 'none',
        scrollTrigger: {
          trigger: '.hero-slide',
          start: 'top bottom',
          end: 'bottom top',
          scrub: true
        }
      });
    }
    
    createFloatingParticles() {
      const particleContainer = document.createElement('div');
      particleContainer.className = 'floating-particles fixed inset-0 pointer-events-none z-0';
      document.body.appendChild(particleContainer);
      
      for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle absolute w-2 h-2 bg-white/10 rounded-full';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 5 + 's';
        
        particleContainer.appendChild(particle);
        
        // Animate particle
        gsap.to(particle, {
          y: -100,
          x: Math.random() * 100 - 50,
          opacity: 0,
          duration: Math.random() * 10 + 10,
          repeat: -1,
          ease: 'none'
        });
      }
    }
  }
  
  // Initialize animations when DOM is loaded
  document.addEventListener('DOMContentLoaded', () => {
    new AnimationController();
  });
  
  // Skeleton loading for specific elements
  function showSkeletonLoading(element, duration = 2000) {
    const originalContent = element.innerHTML;
    element.innerHTML = `
      <div class="skeleton-loading">
        <div class="skeleton h-4 bg-gradient-to-r from-[#2a2d3a] via-[#3a3d4a] to-[#2a2d3a] animate-pulse rounded mb-2"></div>
        <div class="skeleton h-4 bg-gradient-to-r from-[#2a2d3a] via-[#3a3d4a] to-[#2a2d3a] animate-pulse rounded mb-2 w-3/4"></div>
        <div class="skeleton h-4 bg-gradient-to-r from-[#2a2d3a] via-[#3a3d4a] to-[#2a2d3a] animate-pulse rounded w-1/2"></div>
      </div>
    `;
    
    setTimeout(() => {
      gsap.fromTo(element, 
        { opacity: 0 },
        { 
          opacity: 1, 
          duration: 0.5, 
          onComplete: () => {
            element.innerHTML = originalContent;
          }
        }
      );
    }, duration);
  }
  
  // Loading overlay
  function showLoadingOverlay(message = 'Đang tải...') {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay fixed inset-0 bg-black/50 flex items-center justify-center z-50';
    overlay.innerHTML = `
      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-8 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#F53003] mx-auto mb-4"></div>
        <p class="text-white">${message}</p>
      </div>
    `;
    
    document.body.appendChild(overlay);
    
    return {
      hide: () => {
        gsap.to(overlay, { 
          opacity: 0, 
          duration: 0.3, 
          onComplete: () => {
            overlay.remove();
          }
        });
      }
    };
  }
  
  // Smooth scroll to element
  function smoothScrollTo(element, offset = 0) {
    const target = typeof element === 'string' ? document.querySelector(element) : element;
    if (target) {
      const targetPosition = target.offsetTop - offset;
      gsap.to(window, { 
        scrollTo: { y: targetPosition, autoKill: false },
        duration: 1,
        ease: 'power2.inOut'
      });
    }
  }
  
  // Animate counter
  function animateCounter(element, target, duration = 2000) {
    const start = 0;
    gsap.fromTo(element, 
      { textContent: start },
      { 
        textContent: target,
        duration: duration / 1000,
        ease: 'power2.out',
        snap: { textContent: 1 },
        onUpdate: function() {
          element.textContent = Math.round(this.targets()[0].textContent);
        }
      }
    );
  }
</script>

<style>
  /* Additional animation styles */
  .floating-particles {
    overflow: hidden;
  }
  
  .particle {
    animation: float 15s infinite linear;
  }
  
  @keyframes float {
    0% {
      transform: translateY(100vh) translateX(0);
      opacity: 0;
    }
    10% {
      opacity: 1;
    }
    90% {
      opacity: 1;
    }
    100% {
      transform: translateY(-100px) translateX(100px);
      opacity: 0;
    }
  }
  
  .skeleton {
    background: linear-gradient(90deg, #2a2d3a 25%, #3a3d4a 50%, #2a2d3a 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
  }
  
  @keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }
  
  .skeleton-card {
    animation: skeletonPulse 2s infinite;
  }
  
  @keyframes skeletonPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
  }
  
  /* Hover card flip effect */
  .flip-card {
    perspective: 1000px;
  }
  
  .flip-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    text-align: center;
    transition: transform 0.6s;
    transform-style: preserve-3d;
  }
  
  .flip-card:hover .flip-card-inner {
    transform: rotateY(180deg);
  }
  
  .flip-card-front, .flip-card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 12px;
  }
  
  .flip-card-back {
    transform: rotateY(180deg);
    background: linear-gradient(135deg, #1b1d24, #2a2d3a);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }
  
  /* Glowing border animation */
  .glow-border {
    position: relative;
    overflow: hidden;
  }
  
  .glow-border::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(245, 48, 3, 0.4), transparent);
    transition: left 0.5s;
  }
  
  .glow-border:hover::before {
    left: 100%;
  }
  
  /* Loading spinner */
  .loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #2a2d3a;
    border-top: 4px solid #F53003;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>
