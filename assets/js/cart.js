// =============================================
// CART JAVASCRIPT
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    // ===== UPDATE CART QUANTITY =====
    const qtyInputs = document.querySelectorAll('.cart-qty-input');
    qtyInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        });
        
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    });
    
    // ===== REMOVE FROM CART =====
    document.querySelectorAll('.cart-remove-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Hapus item dari keranjang?')) {
                window.location.href = this.href;
            }
        });
    });
    
    // ===== CLEAR CART =====
    const clearCartBtn = document.getElementById('clearCartBtn');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Hapus semua item dari keranjang?')) {
                window.location.href = this.href;
            }
        });
    }
    
    // ===== APPLY COUPON =====
    const couponForm = document.getElementById('couponForm');
    if (couponForm) {
        couponForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = this.querySelector('input[name="coupon"]');
            const code = input ? input.value.trim() : '';
            
            if (code) {
                // Simulate coupon check
                showToast('Kupon tidak valid atau sudah kadaluarsa', 'error');
            }
        });
    }
});

// =============================================
// CART API FUNCTIONS
// =============================================

function updateCartQuantity(cartId, quantity) {
    const data = { cart_id: cartId, quantity: quantity };
    
    ajaxRequest('/ajax/cart.php', 'POST', data)
        .then(response => {
            if (response.success) {
                updateCartTotal(response.total);
                updateCartCount(response.count);
                showToast('Keranjang diperbarui', 'success');
            } else {
                showToast(response.error || 'Gagal memperbarui', 'error');
            }
        })
        .catch(() => {
            showToast('Terjadi kesalahan', 'error');
        });
}

function removeFromCart(cartId) {
    const data = { cart_id: cartId, action: 'remove' };
    
    ajaxRequest('/ajax/cart.php', 'POST', data)
        .then(response => {
            if (response.success) {
                const row = document.querySelector(`[data-cart-id="${cartId}"]`);
                if (row) {
                    row.remove();
                }
                updateCartTotal(response.total);
                updateCartCount(response.count);
                showToast('Item dihapus dari keranjang', 'info');
                
                // Reload if cart is empty
                if (response.count === 0) {
                    location.reload();
                }
            } else {
                showToast(response.error || 'Gagal menghapus', 'error');
            }
        })
        .catch(() => {
            showToast('Terjadi kesalahan', 'error');
        });
}

function clearCart() {
    ajaxRequest('/ajax/cart.php', 'POST', { action: 'clear' })
        .then(response => {
            if (response.success) {
                location.reload();
            } else {
                showToast(response.error || 'Gagal membersihkan keranjang', 'error');
            }
        })
        .catch(() => {
            showToast('Terjadi kesalahan', 'error');
        });
}

function updateCartTotal(total) {
    const totalElement = document.getElementById('cartTotal');
    if (totalElement) {
        totalElement.textContent = formatPrice(total);
    }
}
