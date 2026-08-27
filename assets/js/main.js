// =============================================
// MAIN JAVASCRIPT
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    // ===== AUTO DISMISS ALERTS =====
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // ===== MOBILE NAV TOGGLE =====
    const mobileToggle = document.getElementById('mobileToggle');
    const navLinks = document.getElementById('navLinks');
    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', function() {
            navLinks.classList.toggle('open');
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 991) {
                if (!navLinks.contains(e.target) && !mobileToggle.contains(e.target)) {
                    navLinks.classList.remove('open');
                }
            }
        });
    }
    
    // ===== NAVBAR SCROLL EFFECT =====
    const nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    }
    
    // ===== QUANTITY INPUT VALIDATION =====
    const qtyInputs = document.querySelectorAll('input[type="number"][name^="quantity"]');
    qtyInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const min = parseInt(this.min) || 1;
            if (parseInt(this.value) < min) {
                this.value = min;
            }
        });
    });
    
    // ===== CONFIRM DELETE =====
    const deleteLinks = document.querySelectorAll('a[onclick*="confirm"], .delete-confirm');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });
    
    // ===== FORM SUBMISSION LOADING =====
    const forms = document.querySelectorAll('form[data-loading]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            }
        });
    });
    
    // ===== BACK TO TOP =====
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.style.display = 'flex';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    
    // ===== IMAGE ZOOM ON CLICK =====
    const zoomImages = document.querySelectorAll('.zoomable');
    zoomImages.forEach(function(img) {
        img.addEventListener('click', function() {
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            `;
            const cloned = this.cloneNode();
            cloned.style.maxWidth = '90%';
            cloned.style.maxHeight = '90%';
            cloned.style.objectFit = 'contain';
            overlay.appendChild(cloned);
            document.body.appendChild(overlay);
            overlay.addEventListener('click', function() {
                this.remove();
            });
        });
    });
    
    // ===== PRICE FORMATTER =====
    window.formatPrice = function(price) {
        return 'Rp ' + Number(price).toLocaleString('id-ID');
    };
});

// =============================================
// TOAST NOTIFICATION
// =============================================
function showToast(message, type = 'success') {
    const container = document.querySelector('.toast-container');
    if (!container) {
        const newContainer = document.createElement('div');
        newContainer.className = 'toast-container';
        document.body.appendChild(newContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 show`;
    toast.role = 'alert';
    toast.style.borderRadius = '12px';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    const containerEl = document.querySelector('.toast-container');
    containerEl.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// =============================================
// AJAX FUNCTIONS
// =============================================
function ajaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        if (data && method === 'POST') {
            xhr.setRequestHeader('Content-Type', 'application/json');
        }
        
        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    resolve(JSON.parse(this.responseText));
                } catch (e) {
                    resolve(this.responseText);
                }
            } else {
                reject(this.statusText);
            }
        };
        
        xhr.onerror = function() {
            reject('Network error');
        };
        
        xhr.send(data ? JSON.stringify(data) : null);
    });
}

// =============================================
// CART FUNCTIONS
// =============================================
function addToCart(productId, quantity = 1) {
    const btn = document.querySelector(`[data-add-cart="${productId}"]`);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    ajaxRequest('/ajax/cart.php', 'POST', { action: 'add', product_id: productId, quantity: quantity })
        .then(response => {
            if (response.success) {
                showToast('Produk berhasil ditambahkan ke keranjang!', 'success');
                updateCartCount(response.count);
            } else {
                showToast(response.error || 'Gagal menambahkan ke keranjang', 'error');
            }
        })
        .catch(() => {
            showToast('Terjadi kesalahan, silakan coba lagi', 'error');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-shopping-bag"></i>';
            }
        });
}

function updateCartCount(count) {
    const badge = document.querySelector('.cart-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

// =============================================
// WISHLIST FUNCTIONS
// =============================================
function toggleWishlist(productId) {
    const icon = document.querySelector(`[data-wishlist="${productId}"] i`);
    if (icon) {
        icon.className = 'fas fa-spinner fa-spin';
    }
    
    ajaxRequest('/pages/wishlist-toggle.php', 'POST', { product_id: productId })
        .then(response => {
            if (response.success) {
                if (icon) {
                    if (response.action === 'added') {
                        icon.className = 'fas fa-heart';
                        showToast('Ditambahkan ke wishlist', 'success');
                    } else {
                        icon.className = 'far fa-heart';
                        showToast('Dihapus dari wishlist', 'info');
                    }
                }
            }
        })
        .catch(() => {
            showToast('Terjadi kesalahan', 'error');
            if (icon) {
                icon.className = 'far fa-heart';
            }
        });
}

// =============================================
// PRODUCT FILTER
// =============================================
function applyFilters() {
    const form = document.getElementById('filterForm');
    if (form) {
        form.submit();
    }
}

// =============================================
// SMOOTH SCROLL
// =============================================
function smoothScroll(target, duration = 500) {
    const targetElement = document.querySelector(target);
    if (targetElement) {
        const targetPosition = targetElement.offsetTop;
        const startPosition = window.pageYOffset;
        const distance = targetPosition - startPosition;
        let startTime = null;
        
        function animation(currentTime) {
            if (startTime === null) startTime = currentTime;
            const timeElapsed = currentTime - startTime;
            const progress = Math.min(timeElapsed / duration, 1);
            const ease = progress < 0.5 ? 2 * progress * progress : -1 + (4 - 2 * progress) * progress;
            window.scrollTo(0, startPosition + distance * ease);
            if (timeElapsed < duration) requestAnimationFrame(animation);
        }
        
        requestAnimationFrame(animation);
    }
}
