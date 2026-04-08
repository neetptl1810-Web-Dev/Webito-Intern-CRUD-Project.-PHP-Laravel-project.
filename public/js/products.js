/**
 * =========================================
 * 🛍️ Products Page - Interactive Features
 * ✨ Standalone - No dependencies
 * ========================================= */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🛍️ Products page loaded');
    
    // =========================================
    // 1. Search Input Enhancements
    // =========================================
    const searchInput = document.getElementById('search');
    const searchForm = document.querySelector('.search-form');
    
    if (searchInput) {
        // Clear search on Escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                if (searchForm) searchForm.submit();
            }
        });
        
        // Add subtle animation on focus
        searchInput.addEventListener('focus', function() {
            this.parentElement?.classList.add('focused');
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement?.classList.remove('focused');
        });
    }
    
    // =========================================
    // 2. Company Card Hover Effects (Enhanced)
    // =========================================
    const companyCards = document.querySelectorAll('.company-card');
    
    companyCards.forEach(card => {
        // Add mousemove effect for parallax glow
        card.addEventListener('mousemove', function(e) {
            const glow = this.querySelector('.card-glow');
            if (!glow) return;
            
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const moveX = (x - centerX) / 20;
            const moveY = (y - centerY) / 20;
            
            glow.style.transform = `translate(${moveX}px, ${moveY}px)`;
        });
        
        // Reset glow on mouse leave
        card.addEventListener('mouseleave', function() {
            const glow = this.querySelector('.card-glow');
            if (glow) {
                glow.style.transform = 'translate(0, 0)';
            }
        });
    });
    
    // =========================================
    // 3. Smooth Scroll for Anchor Links
    // =========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // =========================================
    // 4. Lazy Load Animation for Cards
    // =========================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                cardObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Initialize cards with hidden state for lazy animation
    companyCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.5s ease, transform 0.5s ease ${index * 0.1}s`;
        cardObserver.observe(card);
    });
    
    // =========================================
    // 5. Search Form Submit Enhancement
    // =========================================
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchValue = searchInput?.value.trim();
            
            // Prevent empty search submission
            if (!searchValue) {
                e.preventDefault();
                searchInput?.focus();
                searchInput?.classList.add('invalid');
                
                // Remove invalid state after 2 seconds
                setTimeout(() => {
                    searchInput?.classList.remove('invalid');
                }, 2000);
                
                return false;
            }
            
            // Show loading state on button
            const searchBtn = this.querySelector('.btn-search');
            if (searchBtn) {
                const originalText = searchBtn.innerHTML;
                searchBtn.disabled = true;
                searchBtn.innerHTML = '<span class="ph-spinner"></span> Searching...';
                
                // Re-enable after 3 seconds (in case of slow network)
                setTimeout(() => {
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = originalText;
                }, 3000);
            }
        });
    }
    
    // =========================================
    // 6. Keyboard Navigation for Cards
    // =========================================
    companyCards.forEach(card => {
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'link');
        card.setAttribute('aria-label', `View products from ${card.querySelector('.card-title')?.textContent}`);
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // =========================================
    // 7. Performance: Debounce Search Input
    // =========================================
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Could add live search here if needed
                console.log('🔍 Search input:', this.value);
            }, 300);
        });
    }
    
    // =========================================
    // 8. Initialize Page Load Animation
    // =========================================
    const headerElements = document.querySelectorAll('.header-title, .header-subtitle, .search-form');
    headerElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = `opacity 0.6s ease, transform 0.6s ease ${index * 0.15}s`;
        
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 100 + (index * 150));
    });
    
    console.log('✅ Products page interactions initialized');
});

// =========================================
// 🎨 Utility: CSS Spinner for Loading
// =========================================
if (!document.getElementById('ph-spinner-styles')) {
    const style = document.createElement('style');
    style.id = 'ph-spinner-styles';
    style.textContent = `
        .ph-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: ph-spin 1s linear infinite;
            vertical-align: middle;
            margin-right: 6px;
        }
        @keyframes ph-spin {
            to { transform: rotate(360deg); }
        }
        .search-input.invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1) !important;
            animation: ph-shake 0.3s ease;
        }
        @keyframes ph-shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-3px); }
            40%, 80% { transform: translateX(3px); }
        }
    `;
    document.head.appendChild(style);
}