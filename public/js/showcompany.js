/**
 * =========================================
 * 📦 Product Form Validation & Enhancement
 * ✨ Matches Premium Design System
 * ========================================= */

document.addEventListener('DOMContentLoaded', function() {
    console.log('📦 Product form validation loaded');
    
    // Process both Add and Edit Product forms
    const forms = [
        document.getElementById('addProductForm'),
        document.getElementById('editProductForm')
    ].filter(Boolean); // Only keep forms that exist on the page
    
    forms.forEach(form => {
        initProductFormValidation(form);
    });

    function initProductFormValidation(form) {
        // Find inputs specifically within this form
        const nameInput = form.querySelector('input[name="name"]');
        let typeInput = form.querySelector('input[name="type"]') || form.querySelector('select[name="type"]');
        const priceInput = form.querySelector('input[name="price"]');
        const descriptionInput = form.querySelector('textarea[name="description"]');
        const imageInput = form.querySelector('input[name="image"]');
        
        // =========================================
        // 2. Product Type Dropdown Replacement
        // =========================================
        if (typeInput && typeInput.tagName === 'INPUT') {
            const dropdown = document.createElement('select');
            dropdown.id = typeInput.id || `type_${form.id}`;
            dropdown.name = 'type';
            dropdown.required = typeInput.required;
            dropdown.className = typeInput.className;
            
            const currentValue = typeInput.value;
            
            const productTypes = [
                { value: '', label: 'Select a product type...', disabled: true, selected: true },
                { value: 'electronics', label: '📱 Electronics' },
                { value: 'clothing', label: '👕 Clothing' },
                { value: 'food', label: '🍔 Food & Beverages' },
                { value: 'home', label: '🏠 Home & Living' },
                { value: 'beauty', label: '💄 Beauty & Personal Care' },
                { value: 'sports', label: '⚽ Sports & Fitness' },
                { value: 'books', label: '📚 Books & Media' },
                { value: 'toys', label: '🧸 Toys & Games' },
                { value: 'automotive', label: '🚗 Automotive' },
                { value: 'other', label: '📦 Other' },
            ];
            
            productTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.value;
                option.textContent = type.label;
                if (type.disabled) option.disabled = true;
                if (currentValue && type.value.toLowerCase() === currentValue.toLowerCase()) {
                    option.selected = true;
                } else if (type.selected && !currentValue) {
                    option.selected = true;
                }
                dropdown.appendChild(option);
            });
            
            typeInput.parentNode.replaceChild(dropdown, typeInput);
            typeInput = dropdown; // Update local reference
        }
        
        // =========================================
// 3. Product Name Validation - First Letter Capital, Rest User-Controlled
// =========================================
if (nameInput) {
    nameInput.addEventListener('input', function(e) {
        let value = e.target.value;
        
        // ✅ Only capitalize first letter, leave rest as user typed
        if (value.length > 0) {
            value = value.charAt(0).toUpperCase() + value.slice(1);
        }
        
        e.target.value = value;
        validateField(nameInput, validateProductName);
    });
    
    nameInput.addEventListener('blur', function(e) {
        let value = e.target.value;
        
        // ✅ Only capitalize first letter on blur, preserve user casing
        if (value.length > 0) {
            value = value.charAt(0).toUpperCase() + value.slice(1);
            e.target.value = value;
        }
        
        validateField(nameInput, validateProductName);
    });
}

function validateProductName(value) {
    const trimmedValue = value.trim();
    
    if (trimmedValue.length === 0) return { valid: false, required: true, message: 'Required' };
    if (trimmedValue.length < 2) return { valid: false, message: 'Product name must be at least 2 characters' };
    
    // ✅ Only check that first letter is capital (don't force rest to lowercase)
    if (trimmedValue.charAt(0) !== trimmedValue.charAt(0).toUpperCase()) {
        return { valid: false, message: 'First letter must be capital' };
    }
    
    return { valid: true };
}
        
        // =========================================
        // 4. Product Type Validation (Dropdown)
        // =========================================
        if (typeInput) {
            typeInput.addEventListener('change', function() {
                validateField(typeInput, validateProductType);
            });
            typeInput.addEventListener('blur', function() {
                validateField(typeInput, validateProductType);
            });
        }
        
        function validateProductType(value) {
            if (!value || value === '') return { valid: false, message: 'Please select a product type' };
            return { valid: true };
        }
        
        // =========================================
        // 5. Product Price Validation
        // =========================================
        if (priceInput) {
            priceInput.addEventListener('input', function(e) {
                let value = e.target.value;
                value = value.replace(/[^0-9.]/g, '');
                const parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }
                e.target.value = value;
                validateField(priceInput, validateProductPrice);
            });
            
            priceInput.addEventListener('blur', function(e) {
                const res = validateField(priceInput, validateProductPrice);
                if (res.valid && priceInput.value) {
                    priceInput.value = parseFloat(priceInput.value).toFixed(2);
                }
            });
        }
        
        function validateProductPrice(value) {
            const price = parseFloat(value);
            if (!value || isNaN(price)) return { valid: false, message: 'Please enter a valid price' };
            if (price < 1) return { valid: false, message: 'Minimum price is Rs 1' };
            return { valid: true };
        }
        
        // =========================================
// 6. Product Description Validation - First Letter Capital, Rest User-Controlled
// =========================================
if (descriptionInput) {
    descriptionInput.addEventListener('input', function(e) {
        let value = e.target.value;
        
        // ✅ Only capitalize first letter, leave rest as user typed
        if (value.length > 0) {
            value = value.charAt(0).toUpperCase() + value.slice(1);
        }
        
        e.target.value = value;
        validateField(descriptionInput, validateProductDescription);
    });
    
    descriptionInput.addEventListener('blur', function(e) {
        // ✅ Optional: Re-apply first letter capitalization on blur (preserves user casing)
        let value = e.target.value;
        if (value.length > 0) {
            value = value.charAt(0).toUpperCase() + value.slice(1);
            e.target.value = value;
        }
        validateField(descriptionInput, validateProductDescription);
    });
}

function validateProductDescription(value) {
    const trimmedValue = value.trim();
    
    // Empty description is allowed (optional field)
    if (trimmedValue.length === 0) return { valid: true };
    
    // If provided, must be at least 2 characters
    if (trimmedValue.length < 2) return { valid: false, message: 'Description must be at least 2 characters' };
    
    return { valid: true };
}
        
        // =========================================
        // 7. Product Image Validation
        // =========================================
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                validateField(imageInput, () => validateProductImage(file));
            });
        }
        
        function validateProductImage(file) {
            if (!file) {
                if (imageInput && imageInput.required) return { valid: false, message: 'Product image is required' };
                return { valid: true };
            }
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                return { valid: false, message: 'Invalid image format. Use JPG, PNG, GIF, or WebP', clear: true };
            }
            const maxSize = 2 * 1024 * 1024; // 2MB limit
            if (file.size > maxSize) {
                return { valid: false, message: 'Image size must be less than 2MB', clear: true };
            }
            return { valid: true };
        }
        
        // =========================================
        // Helper to run field validation
        // =========================================
        function validateField(inputConfig, validationFn) {
            if (!inputConfig) return { valid: true };
            const value = inputConfig.value;
            const res = validationFn(value);
            
            if (res.valid) {
                inputConfig.classList.add('valid');
                inputConfig.classList.remove('invalid');
                hideFieldError(inputConfig);
            } else {
                if (res.required && !value) {
                    inputConfig.classList.remove('valid', 'invalid');
                    hideFieldError(inputConfig);
                } else {
                    inputConfig.classList.add('invalid');
                    inputConfig.classList.remove('valid');
                    showFieldError(inputConfig, res.message);
                }
                
                if (res.clear) {
                    inputConfig.value = '';
                }
            }
            return res;
        }

        function validateAllFields() {
            const nameRes = nameInput ? validateField(nameInput, validateProductName) : { valid: true };
            const typeRes = typeInput ? validateField(typeInput, validateProductType) : { valid: true };
            const priceRes = priceInput ? validateField(priceInput, validateProductPrice) : { valid: true };
            const descRes = descriptionInput ? validateField(descriptionInput, validateProductDescription) : { valid: true };
            
            const imageRes = imageInput ? validateField(imageInput, () => validateProductImage(imageInput.files[0])) : { valid: true };
            
            // Check if anything evaluated to false (meaning invalid)
            return nameRes.valid && typeRes.valid && priceRes.valid && descRes.valid && imageRes.valid;
        }
        
        // =========================================
        // 9. Form Submission Validation
        // =========================================
        form.addEventListener('submit', function(e) {
            if (!validateAllFields()) {
                e.preventDefault();
                showNotification('Please fix all validation errors before submitting.', 'error');
                
                const firstError = form.querySelector('.invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                return false;
            }
            
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                setTimeout(() => {
                    submitBtn.style.pointerEvents = 'none';
                    submitBtn.style.opacity = '0.7';
                    submitBtn.innerHTML = '⏳ Saving...';
                }, 10);
            }
        });
    }
    
    // =========================================
    // Shared Helpers
    // =========================================
    function showFieldError(field, message) {
        hideFieldError(field);
        const errorEl = document.createElement('small');
        errorEl.className = 'error-message field-error';
        errorEl.textContent = message;
        errorEl.style.cssText = `
            display: block;
            color: #ef4444;
            font-size: 0.8rem;
            margin-top: 0.35rem;
            font-weight: 500;
            animation: errorShake 0.3s ease;
        `;
        field.parentNode.insertBefore(errorEl, field.nextSibling);
    }
    
    function hideFieldError(field) {
        const errorEl = field.parentNode.querySelector('.field-error');
        if (errorEl) {
            errorEl.remove();
        }
    }
    
    if (typeof window.showNotification !== 'function') {
        window.showNotification = function(message, type = 'info') {
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
            
            if (!document.getElementById('notification-keyframes')) {
                const style = document.createElement('style');
                style.id = 'notification-keyframes';
                style.textContent = `
                    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
                    @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
                `;
                document.head.appendChild(style);
            }
        };
    }
    
    if (!document.getElementById('error-shake-keyframes')) {
        const style = document.createElement('style');
        style.id = 'error-shake-keyframes';
        style.textContent = `
            @keyframes errorShake {
                0%, 100% { transform: translateX(0); }
                20%, 60% { transform: translateX(-3px); }
                40%, 80% { transform: translateX(3px); }
            }
        `;
        document.head.appendChild(style);
    }
});