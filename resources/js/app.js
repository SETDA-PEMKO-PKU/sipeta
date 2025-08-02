import './bootstrap';

// Modern OPD Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize animations
    initializeAnimations();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize statistics counter animation
    initializeCounters();
    
    // Initialize card hover effects
    initializeCardEffects();
    
});

/**
 * Initialize entrance animations
 */
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.opd-card, .stats-card, .search-section').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Initialize modern search functionality
 */
function initializeSearch() {
    const searchInput = document.getElementById('searchOPD');
    const opdCards = document.querySelectorAll('.opd-card');
    const noResultsMessage = document.getElementById('noResults');
    
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            const searchValue = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            opdCards.forEach(card => {
                const opdName = card.querySelector('.opd-info h3').textContent.toLowerCase();
                const opdId = card.querySelector('.opd-id').textContent.toLowerCase();
                
                const isMatch = opdName.includes(searchValue) || opdId.includes(searchValue);
                
                if (isMatch) {
                    card.style.display = '';
                    card.classList.add('slide-up');
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                    card.classList.remove('slide-up');
                }
            });
            
            // Show/hide no results message
            if (noResultsMessage) {
                if (visibleCount === 0 && searchValue !== '') {
                    noResultsMessage.style.display = 'block';
                } else {
                    noResultsMessage.style.display = 'none';
                }
            }
            
            // Update results counter
            updateResultsCounter(visibleCount, opdCards.length);
            
        }, 300);
    });
    
    // Clear search functionality
    const clearButton = document.getElementById('clearSearch');
    if (clearButton) {
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    }
}

/**
 * Update search results counter
 */
function updateResultsCounter(visible, total) {
    const counter = document.getElementById('resultsCounter');
    if (counter) {
        if (visible === total) {
            counter.textContent = `${total} OPD`;
        } else {
            counter.textContent = `${visible} dari ${total} OPD`;
        }
    }
}

/**
 * Initialize animated counters for statistics
 */
function initializeCounters() {
    const counters = document.querySelectorAll('.stat-number, .opd-stat-number');
    
    const animateCounter = (element) => {
        const target = parseInt(element.textContent);
        const duration = 1000;
        const step = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 16);
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
}

/**
 * Initialize card hover effects and interactions
 */
function initializeCardEffects() {
    const cards = document.querySelectorAll('.opd-card');
    
    cards.forEach(card => {
        // Add ripple effect on click
        card.addEventListener('click', function(e) {
            if (e.target.closest('.btn-modern')) return;
            
            const ripple = document.createElement('div');
            ripple.classList.add('ripple');
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
        
        // Enhanced hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

/**
 * Utility function to debounce function calls
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Initialize theme toggle (if needed in future)
 */
function initializeThemeToggle() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;
    
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
}

/**
 * Initialize tooltips for better UX
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            
            this._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
}

// Export functions for potential external use
window.OPDDashboard = {
    initializeAnimations,
    initializeSearch,
    initializeCounters,
    initializeCardEffects,
    debounce
};