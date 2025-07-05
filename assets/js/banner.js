// banner.js - Clean Modern Slider Component
class BannerSlider {
    constructor(container, options = {}) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.options = {
            autoPlay: options.autoPlay || 5000,
            images: options.images || [],
            height: options.height || '300px',
            borderRadius: options.borderRadius || '12px',
            gap: options.gap || '16px',
            ...options
        };
        
        this.currentIndex = 0;
        this.isAutoPlaying = true;
        this.autoPlayInterval = null;
        
        this.init();
    }
    
    init() {
        this.createHTML();
        this.setupStyles();
        this.setupEvents();
        this.startAutoPlay();
    }
    
    createHTML() {
        this.container.innerHTML = `
            <div class="banner-slider-wrapper">
                <div class="banner-slider-track">
                    ${this.options.images.map((img, index) => `
                        <div class="banner-slide ${index === 0 ? 'active' : ''}">
                            <img src="${img}" alt="Slide ${index + 1}" loading="lazy">
                        </div>
                    `).join('')}
                </div>
                <div class="banner-dots">
                    ${this.options.images.map((_, index) => `
                        <div class="banner-dot ${index === 0 ? 'active' : ''}" data-index="${index}"></div>
                    `).join('')}
                </div>
            </div>
        `;
        
        this.track = this.container.querySelector('.banner-slider-track');
        this.slides = this.container.querySelectorAll('.banner-slide');
        this.dots = this.container.querySelectorAll('.banner-dot');
    }
    
    setupStyles() {
        if (!document.querySelector('#banner-slider-styles')) {
            const style = document.createElement('style');
            style.id = 'banner-slider-styles';
            style.textContent = `
                .banner-slider-wrapper {
                    position: relative;
                    width: 100%;
                    height: ${this.options.height};
                    overflow: hidden;
                    border-radius: ${this.options.borderRadius};
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                }
                
                .banner-slider-track {
                    position: relative;
                    width: 100%;
                    height: 100%;
                    touch-action: pan-y;
                }
                
                .banner-slide {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    opacity: 0;
                    transform: translateX(100%);
                    transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
                }
                
                .banner-slide.active {
                    opacity: 1;
                    transform: translateX(0);
                }
                
                .banner-slide.prev {
                    transform: translateX(-100%);
                }
                
                .banner-slide.next {
                    transform: translateX(100%);
                }
                
                .banner-slide img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    object-position: center;
                    border-radius: ${this.options.borderRadius};
                    transition: transform 0.3s ease;
                }
                
                .banner-dots {
                    position: absolute;
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    display: flex;
                    gap: 8px;
                    z-index: 10;
                }
                
                .banner-dot {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.5);
                    cursor: pointer;
                    transition: all 0.3s ease;
                    backdrop-filter: blur(10px);
                }
                
                .banner-dot.active {
                    background: rgba(255, 255, 255, 0.9);
                    transform: scale(1.2);
                }
                
                .banner-dot:hover {
                    background: rgba(255, 255, 255, 0.8);
                    transform: scale(1.1);
                }
                
                @media (max-width: 768px) {
                    .banner-slider-wrapper {
                        height: ${parseInt(this.options.height) * 0.8}px;
                    }
                    
                    .banner-dots {
                        bottom: 15px;
                    }
                    
                    .banner-dot {
                        width: 6px;
                        height: 6px;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    setupEvents() {
        // Touch events
        let startX = 0;
        let startY = 0;
        let moveX = 0;
        let isMoving = false;
        
        this.track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isMoving = false;
            this.pauseAutoPlay();
        }, { passive: true });
        
        this.track.addEventListener('touchmove', (e) => {
            if (!startX) return;
            
            moveX = e.touches[0].clientX;
            const moveY = e.touches[0].clientY;
            const diffX = startX - moveX;
            const diffY = startY - moveY;
            
            // Only handle horizontal swipes
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                isMoving = true;
                e.preventDefault();
            }
        }, { passive: false });
        
        this.track.addEventListener('touchend', (e) => {
            if (!startX || !isMoving) {
                this.resumeAutoPlay();
                return;
            }
            
            const diffX = startX - moveX;
            const threshold = 50;
            
            if (Math.abs(diffX) > threshold) {
                if (diffX > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
            
            startX = 0;
            moveX = 0;
            isMoving = false;
            this.resumeAutoPlay();
        }, { passive: true });
        
        // Dot navigation
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                this.goToSlide(index);
            });
        });
        
        // Pause on hover
        this.container.addEventListener('mouseenter', () => this.pauseAutoPlay());
        this.container.addEventListener('mouseleave', () => this.resumeAutoPlay());
        
        // Visibility API for performance
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAutoPlay();
            } else {
                this.resumeAutoPlay();
            }
        });
    }
    
    goToSlide(index) {
        if (index === this.currentIndex) return;
        
        // Remove all classes
        this.slides.forEach(slide => {
            slide.classList.remove('active', 'prev', 'next');
        });
        
        this.dots.forEach(dot => {
            dot.classList.remove('active');
        });
        
        // Set new active slide
        this.slides[index].classList.add('active');
        this.dots[index].classList.add('active');
        
        this.currentIndex = index;
    }
    
    next() {
        const nextIndex = (this.currentIndex + 1) % this.slides.length;
        this.goToSlide(nextIndex);
    }
    
    prev() {
        const prevIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
        this.goToSlide(prevIndex);
    }
    
    startAutoPlay() {
        if (!this.isAutoPlaying || this.options.autoPlay === false) return;
        
        this.autoPlayInterval = setInterval(() => {
            this.next();
        }, this.options.autoPlay);
    }
    
    pauseAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    }
    
    resumeAutoPlay() {
        if (this.isAutoPlaying && !this.autoPlayInterval) {
            this.startAutoPlay();
        }
    }
    
    destroy() {
        this.pauseAutoPlay();
        this.container.innerHTML = '';
    }
}

// Usage function
function createBannerSlider(containerId, images, options = {}) {
    return new BannerSlider(containerId, {
        images: images,
        ...options
    });
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { BannerSlider, createBannerSlider };
}

// Example usage:
/*
// Include this script in your HTML
<script src="banner.js"></script>

// Create a container in your HTML
<div id="hero-banner"></div>

// Initialize the slider
const bannerImages = [
    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=400&fit=crop',
    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200&h=400&fit=crop',
    'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&h=400&fit=crop'
];

const slider = createBannerSlider('#hero-banner', bannerImages, {
    height: '400px',
    autoPlay: 5000,
    borderRadius: '16px'
});
*/