/**
 * =========================================
 * 🛍️ E-commerce Product Page - Interactive Features
 * ✨ Standalone - No dependencies
 * ========================================= */

// Global state
let postAuthCallback = null;

// =========================================
// 🎨 Utility Functions
// =========================================

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.form-notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = `form-notification form-notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        font-weight: 500;
        animation: slideIn 0.3s ease;
        max-width: 350px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
    
    // Add keyframes if needed
    if (!document.getElementById('notification-keyframes')) {
        const style = document.createElement('style');
        style.id = 'notification-keyframes';
        style.textContent = `
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
            @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        `;
        document.head.appendChild(style);
    }
}

function updateCartBadge(count) {
    const badge = document.getElementById('cart-count-badge');
    if (badge) {
        badge.textContent = count > 0 ? count : '';
        badge.style.display = count > 0 ? 'inline-flex' : 'none';
    }
}

// =========================================
// 👤 User Profile Menu Toggle
// =========================================

window.toggleProfileMenu = function() {
    const menu = document.getElementById('profileMenu');
    if (menu) {
        menu.classList.toggle('show');
        
        // Close menu when clicking outside
        const closeMenu = (e) => {
            if (!e.target.closest('.user-profile')) {
                menu.classList.remove('show');
                document.removeEventListener('click', closeMenu);
            }
        };
        document.addEventListener('click', closeMenu);
    }
};

// =========================================
// ℹ️ Product About Modal
// =========================================

window.openProductAbout = function(productId, name, description, type, price) {
    const modal = document.getElementById('productAboutModal');
    const content = document.getElementById('productAboutContent');
    
    if (!modal || !content) return;
    
    content.innerHTML = `
        <div class="product-about-details">
            <h4>${escapeHtml(name)}</h4>
            <p><strong>Type:</strong> ${escapeHtml(type)}</p>
            ${price ? `<p><strong>Price:</strong> Rs ${parseFloat(price).toFixed(2)}</p>` : ''}
            <p><strong>Description:</strong></p>
            <p>${escapeHtml(description || 'No description available.')}</p>
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeProductAboutModal()">Close</button>
        </div>
    `;
    
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.classList.add('active');
};

window.closeProductAboutModal = function() {
    const modal = document.getElementById('productAboutModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => { modal.style.display = 'none'; }, 300);
    }
};

// =========================================
// 💰 Buy Product Modal (Fixed)
// =========================================

window.openBuyModal = function(productId, name, price) {
    // Check if user is logged in
    const isLoggedIn = document.querySelector('.user-profile .profile-avatar') !== null;
    
    if (!isLoggedIn) {
        // ✅ Store product data in sessionStorage to reopen after login
        sessionStorage.setItem('pendingBuyModal', JSON.stringify({
            productId: productId,
            name: name,
            price: price
        }));
        
        // Open login modal
        openAuthModal('login');
        return;
    }
    
    const modal = document.getElementById('buyProductModal');
    const content = document.getElementById('buyProductContent');
    
    if (!modal || !content) return;
    
    content.innerHTML = `
        <form id="buyProductForm" data-product-id="${productId}">
            <div class="form-group">
                <label>Product</label>
                <input type="text" value="${escapeHtml(name)}" disabled>
            </div>
            ${price ? `
            <div class="form-group">
                <label>Price</label>
                <input type="text" value="Rs ${parseFloat(price).toFixed(2)}" disabled>
            </div>
            ` : ''}
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" value="1" min="1" max="100" required>
            </div>
            <div class="form-group">
                <label>Delivery Address</label>
                <textarea name="address" rows="3" placeholder="Enter your delivery address..." required></textarea>
                <button type="button" class="btn-location" onclick="getLocationForAddress(this)">📍 Use Current Location</button>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeBuyProductModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add to Cart</button>
            </div>
        </form>
    `;
    
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.classList.add('active');
    
    // Handle form submission
    const form = document.getElementById('buyProductForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            addToCart(productId, this, price);
        });
    }
};

window.closeBuyProductModal = function() {
    const modal = document.getElementById('buyProductModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => { modal.style.display = 'none'; }, 300);
    }
};

// =========================================
// 🛒 Add to Cart Functionality
// =========================================

async function addToCart(productId, form, price) {
    const formData = new FormData(form);
    const quantity = formData.get('quantity');
    const address = formData.get('address');
    
    if (!address.trim()) {
        alert('Please enter a delivery address.');
        return;
    }
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="ec-spinner"></span> Adding to Cart...';
    
    try {
        const response = await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: parseInt(quantity),
                address: address
            })
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showNotification('✅ Added to cart successfully!', 'success');
            closeBuyProductModal();
            updateCartBadge(result.cart_count);
        } else {
            // Handle 401 (not logged in)
            if (response.status === 401) {
                closeBuyProductModal();
                const productName = form.querySelector('input[type="text"]')?.value || '';
                openAuthModal('login', () => {
                    openBuyModal(productId, productName, price);
                });
                return;
            }
            
            // Handle 422 validation errors
            if (response.status === 422) {
                const errors = result.errors ? Object.values(result.errors).flat().join(', ') : 'Validation failed';
                throw new Error(errors);
            }
            
            throw new Error(result.message || 'Failed to add to cart');
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        showNotification('❌ ' + error.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// =========================================
// 🔐 Customer Authentication Modal (With Validation)
// =========================================

window.openAuthModal = function(mode = 'login', callback) {
    postAuthCallback = callback;
    
    const modal = document.getElementById('authModal');
    const title = document.getElementById('authModalTitle');
    const form = document.getElementById('authForm');
    
    if (!modal || !title || !form) {
        console.error('❌ Auth modal elements not found');
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    if (mode === 'register') {
        title.textContent = '👤 Create Customer Account';
        form.action = '/customer/register';
        
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            
            <!-- Full Name -->
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" id="registerName" required 
                       placeholder="Enter your name"
                       pattern="[A-Za-z]+( [A-Za-z]+)*" 
                       title="Only letters and spaces allowed">
                <span class="form-error" id="nameError"></span>
            </div>
            
            <!-- Email -->
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" id="registerEmail" required 
                       placeholder="you@example.com"
                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                       title="Enter a valid email address">
                <span class="form-error" id="emailError"></span>
            </div>
            
            <!-- Phone -->
            <div class="form-group">
                <label>Phone *</label>
                <input type="tel" name="phone" id="registerPhone" required 
                       placeholder="Enter 10-digit phone number"
                       pattern="[0-9]{10}" 
                       title="Only 10 digits allowed"
                       maxlength="10">
                <small class="form-hint">Only digits allowed (10 digits max)</small>
                <span class="form-error" id="phoneError"></span>
            </div>
            
            <!-- Address -->
            <div class="form-group">
                <label>Address *</label>
                <textarea name="address" id="registerAddress" rows="2" required 
                          placeholder="Enter your address"></textarea>
                <button type="button" class="btn-location" onclick="getLocationForRegistration()">
                    📍 Use Current Location
                </button>
                <span class="form-error" id="addressError"></span>
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" id="registerPassword" required 
                       minlength="6" 
                       placeholder="Min 6 characters"
                       pattern="(?=.*[a-zA-Z])(?=.*[0-9]).{6,}"
                       title="Must contain letters and numbers, min 6 characters">
                <small class="form-hint">Must contain letters & numbers, min 6 characters</small>
                <span class="form-error" id="passwordError"></span>
            </div>
            
            <!-- Confirm Password -->
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="password_confirmation" id="registerPasswordConfirm" required 
                       placeholder="Re-enter your password">
                <span class="form-error" id="passwordConfirmError"></span>
            </div>
            
            <button type="submit" class="btn-primary" id="registerSubmitBtn">Create Account</button>
            <p class="auth-switch">
                Already have an account? 
                <a href="#" onclick="switchAuthMode('login'); return false;">Login here</a>
            </p>
        `;
        
        // ✅ Attach validation event listeners
        attachRegisterValidations();
        
    } else {
        // Login mode (unchanged)
        title.textContent = '👤 Customer Login';
        form.action = '/customer/login';
        
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required placeholder="you@example.com">
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn-primary">Login</button>
            <p class="auth-switch">
                Don't have an account? 
                <a href="#" onclick="switchAuthMode('register'); return false;">Register here</a>
            </p>
        `;
    }
    
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.classList.add('active');
    
    const firstInput = form.querySelector('input');
    if (firstInput) firstInput.focus();
    
    form.onsubmit = function(e) {
        e.preventDefault();
        
        // ✅ Validate all fields before submission
        if (mode === 'register' && !validateRegisterForm()) {
            return;
        }
        
        handleCustomerAuthSubmit(this, mode);
    };
    
    console.log(`✅ Auth modal opened: ${mode}`);
};

/**
 * Handle customer auth form submission (AJAX) - FIXED
 */
async function handleCustomerAuthSubmit(form, mode) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="ec-spinner"></span> ' + (mode === 'register' ? 'Creating...' : 'Logging in...');
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showNotification('✅ ' + result.message, 'success');
            closeAuthModal();
            
            // ✅ Check if there's a pending buy modal to reopen
            const pendingBuy = sessionStorage.getItem('pendingBuyModal');
            
            if (pendingBuy) {
                // Clear the session storage
                sessionStorage.removeItem('pendingBuyModal');
                
                // ✅ Reload page to update UI with logged-in state, then reopen modal
                setTimeout(() => {
                    const buyData = JSON.parse(pendingBuy);
                    // Reopen buy modal after page reload
                    if (window.openBuyModal) {
                        window.openBuyModal(buyData.productId, buyData.name, buyData.price);
                    }
                }, 1000);
                
                // Reload the page
                location.reload();
            } else if (postAuthCallback) {
                // Execute other callbacks if no pending buy
                postAuthCallback();
                postAuthCallback = null;
            } else {
                // Default: reload to update UI
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            throw new Error(result.message || (mode === 'register' ? 'Registration failed' : 'Login failed'));
        }
    } catch (error) {
        console.error('Auth error:', error);
        showNotification('❌ ' + error.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

window.closeAuthModal = function() {
    const modal = document.getElementById('authModal');
    if (!modal) return;
    
    modal.classList.remove('active');
    setTimeout(() => { modal.style.display = 'none'; }, 300);
    
    const form = document.getElementById('authForm');
    if (form) form.reset();
    
    console.log('🔐 Auth modal closed');
};

window.switchAuthMode = function(mode) {
    openAuthModal(mode);
    return false;
};

// =========================================
// 👤 Profile Modal
// =========================================

window.openProfileModal = function() {
    const modal = document.getElementById('profileModal');
    if (modal) {
        modal.style.display = 'flex';
        void modal.offsetWidth;
        modal.classList.add('active');
    }
};

window.closeProfileModal = function() {
    const modal = document.getElementById('profileModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => { modal.style.display = 'none'; }, 300);
    }
};

window.previewProfilePhoto = function(input) {
    const preview = document.getElementById('profilePhotoPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
};

// Handle profile form submission
const profileForm = document.getElementById('profileForm');
if (profileForm) {
    profileForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="ec-spinner"></span> Updating...';
        
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                showNotification('✅ Profile updated successfully!', 'success');
                closeProfileModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(result.message || 'Failed to update profile');
            }
        } catch (error) {
            console.error('Profile update error:', error);
            showNotification('❌ ' + error.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// =========================================
// 📍 Geolocation Functions
// =========================================

window.getLocation = function() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }
    
    const addressField = document.querySelector('textarea[name="address"]');
    if (!addressField) return;
    
    addressField.placeholder = 'Getting your location...';
    addressField.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                const data = await response.json();
                
                if (data.address) {
                    const address = [
                        data.address.house_number,
                        data.address.road,
                        data.address.city,
                        data.address.state,
                        data.address.postcode,
                        data.address.country
                    ].filter(Boolean).join(', ');
                    
                    addressField.value = address;
                    addressField.placeholder = 'Your address will be auto-filled from location...';
                }
            } catch (error) {
                console.error('Geocoding error:', error);
                addressField.placeholder = 'Failed to get address. Please enter manually.';
            }
            addressField.disabled = false;
        },
        (error) => {
            console.error('Geolocation error:', error);
            addressField.placeholder = 'Location access denied. Please enter address manually.';
            addressField.disabled = false;
        }
    );
};

window.getLocationForAddress = function(button) {
    const addressField = button.previousElementSibling;
    if (addressField && addressField.tagName === 'TEXTAREA') {
        addressField.placeholder = 'Getting your location...';
        addressField.disabled = true;
        button.disabled = true;
        
        if (!navigator.geolocation) {
            addressField.placeholder = 'Geolocation not supported. Enter manually.';
            addressField.disabled = false;
            button.disabled = false;
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const { latitude, longitude } = position.coords;
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                    const data = await response.json();
                    
                    if (data.address) {
                        const address = [
                            data.address.house_number,
                            data.address.road,
                            data.address.city,
                            data.address.state,
                            data.address.postcode,
                            data.address.country
                        ].filter(Boolean).join(', ');
                        
                        addressField.value = address;
                    }
                } catch (error) {
                    console.error('Geocoding error:', error);
                    addressField.placeholder = 'Failed to get address.';
                }
                addressField.disabled = false;
                button.disabled = false;
            },
            (error) => {
                console.error('Geolocation error:', error);
                addressField.placeholder = 'Location access denied.';
                addressField.disabled = false;
                button.disabled = false;
            }
        );
    }
};

// =========================================
// 🛒 Cart Modal Functions
// =========================================

window.openCartModal = function() {
    const modal = document.getElementById('cartModal');
    const content = document.getElementById('cartContent');
    
    if (!modal || !content) {
        console.error('❌ Cart modal elements not found');
        return;
    }
    
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.classList.add('active');
    
    content.innerHTML = `
        <div class="modal-loading">
            <div class="ec-spinner"></div>
            <p>Loading your cart...</p>
        </div>
    `;
    
    fetch('/cart', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to load cart');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderCartModal(data);
            updateCartBadge(data.count);
        } else {
            throw new Error(data.message || 'Failed to load cart');
        }
    })
    .catch(error => {
        console.error('Cart error:', error);
        content.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: var(--ec-text-muted);">
                <p style="font-size: 3rem; margin-bottom: 1rem;">🛒</p>
                <p>${error.message}</p>
                <button class="btn-cancel" onclick="closeCartModal()" style="margin-top: 1rem;">Close</button>
            </div>
        `;
    });
};

function renderCartModal(data) {
    console.log('🛒 Cart item structure:', data.items[0]);
    const content = document.getElementById('cartContent');
    
    if (!data.items || data.items.length === 0) {
        content.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <p style="font-size: 3rem; margin-bottom: 1rem;">🛒</p>
                <p style="color: var(--ec-text-muted);">Your cart is empty</p>
                <button class="btn-primary" onclick="closeCartModal()" style="margin-top: 1rem;">
                    Continue Shopping
                </button>
            </div>
        `;
        return;
    }
    
    const itemsHtml = data.items.map(item => {
    // ✅ Ensure product_id exists, fallback to item.id or order_id
    const productId = item.product_id || item.id || item.product?.id;
    
    return `
        <div class="cart-item" data-product-id="${productId}">
            <div class="cart-item-info">
                <h4>${escapeHtml(item.product_name)}</h4>
                <p class="cart-price">Rs ${parseFloat(item.price).toFixed(2)} × ${item.quantity}</p>
            </div>
            <div class="cart-item-actions">
                <span class="cart-subtotal">Rs ${parseFloat(item.subtotal).toFixed(2)}</span>
                <button class="btn-remove" 
                        onclick="removeFromCart('${productId}')"
                        data-product-id="${productId}">
                    🗑️ Remove
                </button>
            </div>
        </div>
    `;
}).join('');
    
    content.innerHTML = `
        <div class="cart-modal-content">
            <h4 style="margin: 0 0 1rem 0;">🛒 Your Cart (${data.count} item${data.count !== 1 ? 's' : ''})</h4>
            <div class="cart-items-list">${itemsHtml}</div>
            <div class="cart-total">
                <div class="cart-total-row grand-total">
                    <span><strong>Total:</strong></span>
                    <span><strong>Rs ${data.total.toFixed(2)}</strong></span>
                </div>
            </div>
            <div class="cart-actions">
                <button class="btn-cancel" onclick="closeCartModal()">Continue Shopping</button>
                <button class="btn-primary" onclick="checkoutCart()">Proceed to Checkout →</button>
            </div>
        </div>
    `;
}

window.removeFromCart = function(productId) {
    // ✅ Validate productId
    if (!productId || productId === 'undefined' || productId === 'null') {
        console.error('❌ Invalid product ID:', productId);
        showNotification('❌ Could not remove item: Invalid product ID', 'error');
        return;
    }
    
    if (!confirm('Remove this item from your cart?')) return;
    
    fetch(`/cart/${productId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.status === 404) {
            throw new Error('Item not found in cart');
        }
        if (!response.ok) throw new Error('Failed to remove item');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('✅ Item removed from cart', 'success');
            openCartModal(); // Refresh modal
            updateCartBadge(data.cart_count || 0); // ✅ Update badge with new count
        } else {
            throw new Error(data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Remove error:', error);
        showNotification('❌ ' + error.message, 'error');
    });
};

window.closeCartModal = function() {
    const modal = document.getElementById('cartModal');
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => { modal.style.display = 'none'; }, 300);
};

window.checkoutCart = function() {
    showNotification('🚧 Checkout feature coming soon!', 'info');
};

// =========================================
// 🔄 Load Cart Count on Page Load
// =========================================

function loadCartBadge() {
    const isLoggedIn = document.querySelector('.user-profile .profile-avatar') !== null;
    
    if (isLoggedIn) {
        fetch('/cart', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch cart');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateCartBadge(data.count);
                console.log('🛒 Cart badge updated:', data.count);
            }
        })
        .catch(error => {
            console.log('🛒 Cart badge load skipped:', error.message);
        });
    }
}

// =========================================
// 📋 Registration Form Validation Helpers
// =========================================

/**
 * Attach real-time validation listeners to register form fields
 */
function attachRegisterValidations() {
    // Name: Capitalize each word
    const nameInput = document.getElementById('registerName');
    if (nameInput) {
        nameInput.addEventListener('input', function(e) {
            let value = e.target.value;
            // Capitalize first letter of each word, preserve rest as typed
            value = value.replace(/\b\w/g, char => char.toUpperCase());
            e.target.value = value;
            validateName(value);
        });
        nameInput.addEventListener('blur', function(e) {
            validateName(e.target.value);
        });
    }
    
    // Email: Convert to lowercase, validate format
    const emailInput = document.getElementById('registerEmail');
    if (emailInput) {
        emailInput.addEventListener('input', function(e) {
            // Convert to lowercase automatically
            e.target.value = e.target.value.toLowerCase();
            validateEmail(e.target.value);
        });
        emailInput.addEventListener('blur', function(e) {
            validateEmail(e.target.value);
        });
    }
    
    // Phone: Only digits, max 10
    const phoneInput = document.getElementById('registerPhone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Remove non-digit characters
            e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 10);
            validatePhone(e.target.value);
        });
        phoneInput.addEventListener('blur', function(e) {
            validatePhone(e.target.value);
        });
    }
    
    // Address: Just validate non-empty (location button handled separately)
    const addressInput = document.getElementById('registerAddress');
    if (addressInput) {
        addressInput.addEventListener('blur', function(e) {
            validateAddress(e.target.value);
        });
    }
    
    // Password: Real-time validation
    const passwordInput = document.getElementById('registerPassword');
    if (passwordInput) {
        passwordInput.addEventListener('input', function(e) {
            validatePassword(e.target.value);
        });
        passwordInput.addEventListener('blur', function(e) {
            validatePassword(e.target.value);
        });
    }
    
    // Confirm Password: Match validation
    const confirmInput = document.getElementById('registerPasswordConfirm');
    if (confirmInput) {
        confirmInput.addEventListener('input', function(e) {
            validatePasswordConfirm(passwordInput?.value, e.target.value);
        });
        confirmInput.addEventListener('blur', function(e) {
            validatePasswordConfirm(passwordInput?.value, e.target.value);
        });
    }
}

/**
 * Validate Name: Each word capitalized, letters/spaces only
 */
function validateName(value) {
    const errorEl = document.getElementById('nameError');
    const trimmed = value.trim();
    
    if (!trimmed) {
        showError(errorEl, 'Name is required');
        return false;
    }
    
    if (!/^[A-Za-z\s]+$/.test(trimmed)) {
        showError(errorEl, 'Only letters and spaces allowed');
        return false;
    }
    
    if (trimmed.length < 2) {
        showError(errorEl, 'Name must be at least 2 characters');
        return false;
    }
    
    clearError(errorEl);
    return true;
}

/**
 * Validate Email: Format + @ symbol + lowercase
 */
function validateEmail(value) {
    const errorEl = document.getElementById('emailError');
    const trimmed = value.trim().toLowerCase();
    
    if (!trimmed) {
        showError(errorEl, 'Email is required');
        return false;
    }
    
    // Basic email regex
    const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
    if (!emailRegex.test(trimmed)) {
        showError(errorEl, 'Enter a valid email (e.g., user@example.com)');
        return false;
    }
    
    clearError(errorEl);
    return true;
}

/**
 * Validate Phone: Only digits, exactly 10
 */
function validatePhone(value) {
    const errorEl = document.getElementById('phoneError');
    const trimmed = value.trim();
    
    if (!trimmed) {
        showError(errorEl, 'Phone number is required');
        return false;
    }
    
    if (!/^[0-9]{10}$/.test(trimmed)) {
        showError(errorEl, 'Enter exactly 10 digits');
        return false;
    }
    
    clearError(errorEl);
    return true;
}

/**
 * Validate Address: Non-empty
 */
function validateAddress(value) {
    const errorEl = document.getElementById('addressError');
    const trimmed = value.trim();
    
    if (!trimmed) {
        showError(errorEl, 'Address is required');
        return false;
    }
    
    clearError(errorEl);
    return true;
}

/**
 * Validate Password: Min 6 chars, letters + numbers
 */
function validatePassword(value) {
    const errorEl = document.getElementById('passwordError');
    
    if (!value) {
        showError(errorEl, 'Password is required');
        return false;
    }
    
    if (value.length < 6) {
        showError(errorEl, 'Password must be at least 6 characters');
        return false;
    }
    
    // Check for at least one letter and one number
    if (!/(?=.*[a-zA-Z])(?=.*[0-9])/.test(value)) {
        showError(errorEl, 'Must contain both letters and numbers');
        return false;
    }
    
    clearError(errorEl);
    return true;
}

/**
 * Validate Password Confirmation: Must match password
 */
function validatePasswordConfirm(password, confirm) {
    const errorEl = document.getElementById('passwordConfirmError');
    
    if (!confirm) {
        showError(errorEl, 'Please confirm your password');
        return false;
    }
    
    if (password !== confirm) {
        showError(errorEl, 'Passwords do not match');
        return false;
    }
    
    clearError(errorEl);
    return true;
}

/**
 * Validate entire register form before submission
 */
function validateRegisterForm() {
    const name = document.getElementById('registerName')?.value || '';
    const email = document.getElementById('registerEmail')?.value || '';
    const phone = document.getElementById('registerPhone')?.value || '';
    const address = document.getElementById('registerAddress')?.value || '';
    const password = document.getElementById('registerPassword')?.value || '';
    const confirm = document.getElementById('registerPasswordConfirm')?.value || '';
    
    const isValid = 
        validateName(name) &&
        validateEmail(email) &&
        validatePhone(phone) &&
        validateAddress(address) &&
        validatePassword(password) &&
        validatePasswordConfirm(password, confirm);
    
    return isValid;
}

/**
 * Show error message for a field
 */
function showError(element, message) {
    if (element) {
        element.textContent = message;
        element.style.color = '#ef4444';
        element.style.fontSize = '0.8rem';
        element.style.display = 'block';
    }
}

/**
 * Clear error message for a field
 */
function clearError(element) {
    if (element) {
        element.textContent = '';
        element.style.display = 'none';
    }
}

/**
 * Get location for registration address field
 */
window.getLocationForRegistration = function() {
    const addressField = document.getElementById('registerAddress');
    if (!addressField) return;
    
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }
    
    addressField.placeholder = '📍 Getting your location...';
    addressField.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                const data = await response.json();
                
                if (data.address) {
                    const address = [
                        data.address.house_number,
                        data.address.road,
                        data.address.city,
                        data.address.state,
                        data.address.postcode,
                        data.address.country
                    ].filter(Boolean).join(', ');
                    
                    addressField.value = address;
                    addressField.placeholder = 'Enter your address';
                    validateAddress(address);
                }
            } catch (error) {
                console.error('Geocoding error:', error);
                addressField.placeholder = 'Failed to get address. Please enter manually.';
            }
            addressField.disabled = false;
        },
        (error) => {
            console.error('Geolocation error:', error);
            addressField.placeholder = 'Location access denied. Please enter address manually.';
            addressField.disabled = false;
        }
    );
};

// =========================================
// 🚀 Initialize Page
// =========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🛍️ Product page loaded');
    
    // Load cart badge on page load
    loadCartBadge();
    
    // Close modals when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                const modalId = modal.id;
                if (modalId === 'productAboutModal') closeProductAboutModal();
                else if (modalId === 'buyProductModal') closeBuyProductModal();
                else if (modalId === 'authModal') closeAuthModal();
                else if (modalId === 'profileModal') closeProfileModal();
                else if (modalId === 'cartModal') closeCartModal();
            }
        });
    });
    
    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProductAboutModal();
            closeBuyProductModal();
            closeAuthModal();
            closeProfileModal();
            closeCartModal();
        }
    });
    
    // Auth modal close handlers
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.addEventListener('click', function(e) {
            if (e.target === authModal) closeAuthModal();
        });
    }
    
    console.log('✅ Product page initialized');

    const pendingBuy = sessionStorage.getItem('pendingBuyModal');
    if (pendingBuy) {
        const buyData = JSON.parse(pendingBuy);
        sessionStorage.removeItem('pendingBuyModal');
        
        // Small delay to ensure DOM is ready
        setTimeout(() => {
            if (window.openBuyModal) {
                window.openBuyModal(buyData.productId, buyData.name, buyData.price);
            }
        }, 500);
    }
});