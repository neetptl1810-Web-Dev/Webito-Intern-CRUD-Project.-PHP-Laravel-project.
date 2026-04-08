/**
 * =========================================
 * 🏢 Company Form Validation & Username Check
 * =========================================
 */

document.addEventListener('DOMContentLoaded', function() {

    // =========================================
    // 1. DOM Elements
    // =========================================
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const usernameInput = document.getElementById('username');
    const checkUsernameBtn = document.getElementById('checkUsernameBtn');
    const usernameStatus = document.getElementById('usernameStatus');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const submitBtn = document.getElementById('submitBtn');
    const strengthContainer = document.getElementById('passwordStrengthContainer');
    const strengthMeter = document.getElementById('passwordStrengthMeter');
    const strengthLabel = document.getElementById('passwordStrengthLabel');
    const requirements = document.querySelectorAll('.password-requirement');

    let usernameChecked = false;
    let usernameAvailable = false;

    // =========================================
    // 2. Company Name - Auto Capitalize Each Word
    // =========================================
    if (nameInput) {
        nameInput.addEventListener('input', function(e) {
            let value = e.target.value;

            // Capitalize first letter of each word
            value = value.replace(/\b\w/g, char => char.toUpperCase());

            // Update the input value
            e.target.value = value;

            // Validate minimum 2 characters
            validateName(value);
        });

        nameInput.addEventListener('blur', function(e) {
            let value = e.target.value;
            value = value.replace(/\b\w/g, char => char.toUpperCase());
            e.target.value = value;
            validateName(value);
        });
    }

    function validateName(value) {
        const trimmedValue = value.trim();

        if (trimmedValue.length === 0) {
            nameInput.classList.remove('valid', 'invalid');
            return false;
        }

        if (trimmedValue.length < 2) {
            nameInput.classList.add('invalid');
            nameInput.classList.remove('valid');
            return false;
        }

        nameInput.classList.add('valid');
        nameInput.classList.remove('invalid');
        return true;
    }

    // =========================================
// 3. Description - First Letter Capital, Rest User-Controlled
// =========================================
if (descriptionInput) {
    descriptionInput.addEventListener('input', function(e) {
        let value = e.target.value;

        // ✅ Only capitalize first letter, leave rest as user typed
        if (value.length > 0) {
            value = value.charAt(0).toUpperCase() + value.slice(1);
        }

        // Update the input value
        e.target.value = value;

        // Validate minimum 2 characters
        validateDescription(value);
    });

    descriptionInput.addEventListener('blur', function(e) {
        let value = e.target.value;
        
        // ✅ Only capitalize first letter on blur, preserve user casing
        if (value.length > 0) {
            value = value.charAt(0).toUpperCase() + value.slice(1);
        }
        
        e.target.value = value;
        validateDescription(value);
    });
}

function validateDescription(value) {
    const trimmedValue = value.trim();

    if (trimmedValue.length === 0) {
        descriptionInput.classList.remove('valid', 'invalid');
        return false;
    }

    if (trimmedValue.length < 2) {
        descriptionInput.classList.add('invalid');
        descriptionInput.classList.remove('valid');
        return false;
    }

    descriptionInput.classList.add('valid');
    descriptionInput.classList.remove('invalid');
    return true;
}

    // =========================================
    // 4. Username - Validation & AJAX Check
    // =========================================
    if (usernameInput && checkUsernameBtn) {
        // Check on button click
        checkUsernameBtn.addEventListener('click', function() {
            checkUsername(usernameInput.value.trim());
        });

        // Check on input (debounced)
        let usernameTimeout;
        usernameInput.addEventListener('input', function() {
            clearTimeout(usernameTimeout);
            usernameTimeout = setTimeout(() => {
                if (usernameInput.value.length >= 3) {
                    checkUsername(usernameInput.value.trim());
                } else {
                    updateUsernameStatus('', 'idle');
                    usernameChecked = false;
                    toggleSubmitButton();
                }
            }, 500);
        });

        // Validate on blur
        usernameInput.addEventListener('blur', function() {
            validateUsername(this.value.trim());
        });
    }

    async function checkUsername(username) {
        if (!username || username.length < 3) {
            updateUsernameStatus('Username must be at least 3 characters', 'error');
            return;
        }

        // Check if contains at least 1 number
        if (!/\d/.test(username)) {
            updateUsernameStatus('Username must contain at least 1 number', 'error');
            usernameInput.classList.add('invalid');
            usernameInput.classList.remove('valid');
            return;
        }

        // Show loading
        updateUsernameStatus('Checking availability...', 'checking');
        checkUsernameBtn.disabled = true;
        checkUsernameBtn.textContent = '⏳';

        try {
            const response = await fetch(`/check-username?username=${encodeURIComponent(username)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                   document.querySelector('[name="_token"]')?.value
                }
            });

            const data = await response.json();

            if (data.available) {
                updateUsernameStatus('Username is available!', 'available');
                usernameInput.classList.add('valid');
                usernameInput.classList.remove('invalid');
                usernameAvailable = true;
                usernameChecked = true;
            } else {
                updateUsernameStatus(data.message, 'taken');
                usernameInput.classList.add('invalid');
                usernameInput.classList.remove('valid');
                usernameAvailable = false;
                usernameChecked = false;
            }
        } catch (error) {
            console.error('Error checking username:', error);
            updateUsernameStatus('⚠ Could not check availability', 'error');
            usernameAvailable = false;
            usernameChecked = false;
        } finally {
            checkUsernameBtn.disabled = false;
            checkUsernameBtn.textContent = 'Check';
            toggleSubmitButton();
        }
    }

    function validateUsername(username) {
        if (!username) {
            usernameInput.classList.remove('valid', 'invalid');
            return false;
        }

        if (username.length < 3) {
            usernameInput.classList.add('invalid');
            usernameInput.classList.remove('valid');
            return false;
        }

        if (!/^[a-zA-Z0-9]+$/.test(username)) {
            usernameInput.classList.add('invalid');
            usernameInput.classList.remove('valid');
            return false;
        }

        if (!/\d/.test(username)) {
            usernameInput.classList.add('invalid');
            usernameInput.classList.remove('valid');
            return false;
        }

        // Don't mark as valid until checked with server
        if (usernameChecked && usernameAvailable) {
            usernameInput.classList.add('valid');
            usernameInput.classList.remove('invalid');
            return true;
        }

        return false;
    }

    function updateUsernameStatus(message, state) {
        if (!usernameStatus) return;

        usernameStatus.textContent = message;
        usernameStatus.className = 'username-status ' + state;
    }

    // =========================================
    // 5. Password Validation & Strength Meter
    // =========================================
    if (passwordInput) {
        passwordInput.addEventListener('focus', function() {
            if (strengthContainer) {
                strengthContainer.style.display = 'block';
            }
        });

        passwordInput.addEventListener('input', function() {
            const isValid = updatePasswordStrength(this.value);
            toggleSubmitButton();

            if (!this.value && strengthContainer) {
                strengthContainer.style.display = 'none';
            }
        });

        // Initialize if value exists (form resubmission)
        if (passwordInput.value && strengthContainer) {
            strengthContainer.style.display = 'block';
            updatePasswordStrength(passwordInput.value);
        }
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            validatePasswordMatch();
            toggleSubmitButton();
        });
    }

    function validatePassword(password) {
        return {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*]/.test(password),
        };
    }

    function getPasswordStrength(password) {
        const checks = validatePassword(password);
        return Object.values(checks).filter(Boolean).length;
    }

    function updatePasswordStrength(password) {
        if (!password || !strengthContainer) {
            if (strengthContainer) strengthContainer.style.display = 'none';
            return false;
        }

        strengthContainer.style.display = 'block';
        const checks = validatePassword(password);
        const score = getPasswordStrength(password);

        // Update meter
        strengthMeter.style.width = `${(score / 5) * 100}%`;

        // Color based on strength
        if (score <= 2) {
            strengthMeter.style.background = '#ef4444';
        } else if (score <= 3) {
            strengthMeter.style.background = '#f59e0b';
        } else if (score <= 4) {
            strengthMeter.style.background = '#10b981';
        } else {
            strengthMeter.style.background = 'linear-gradient(90deg, #10b981, #059669)';
        }

        // Update label
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        strengthLabel.textContent = labels[score] || 'Very Strong';
        strengthLabel.style.color = strengthMeter.style.background.includes('gradient') ? '#10b981' : strengthMeter.style.background;

        // Update requirements
        requirements.forEach(req => {
            const rule = req.dataset.rule;
            req.classList.toggle('passed', checks[rule]);
            req.classList.toggle('failed', !checks[rule] && password.length > 0);
        });

        return Object.values(checks).every(Boolean);
    }

    function validatePasswordMatch() {
        if (!confirmInput || !passwordInput) return false;

        const password = passwordInput.value;
        const confirm = confirmInput.value;

        if (confirm.length === 0) {
            confirmInput.classList.remove('valid', 'invalid');
            return false;
        }

        if (password === confirm) {
            confirmInput.classList.add('valid');
            confirmInput.classList.remove('invalid');
            return true;
        } else {
            confirmInput.classList.add('invalid');
            confirmInput.classList.remove('valid');
            return false;
        }
    }

    // =========================================
    // 6. Toggle Submit Button
    // =========================================
    function toggleSubmitButton() {
        if (!submitBtn) return;

        const name = nameInput?.value.trim() || '';
        const description = descriptionInput?.value.trim() || '';
        const username = usernameInput?.value.trim() || '';
        const password = passwordInput?.value || '';
        const confirm = confirmInput?.value || '';

        // Validate all fields
        const isNameValid = name.length >= 2;
        const isDescriptionValid = description.length >= 2;
        const isUsernameValid = username.length >= 3 && /\d/.test(username) && usernameChecked && usernameAvailable;
        const passwordChecks = validatePassword(password);
        const isPasswordValid = passwordChecks.length && passwordChecks.uppercase &&
                               passwordChecks.number && passwordChecks.special &&
                               password === confirm && password.length >= 8;

        // Enable only if all validations pass
        submitBtn.disabled = !(isNameValid && isDescriptionValid && isUsernameValid && isPasswordValid);
    }

    // =========================================
    // 7. Form Submission
    // =========================================
    const companyForm = document.getElementById('companyForm');
    if (companyForm) {
        companyForm.addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent default page reload

            // Final validation before submit
            const isNameValid = validateName(nameInput?.value.trim() || '');
            const isDescriptionValid = validateDescription(descriptionInput?.value.trim() || '');
            const isUsernameValid = validateUsername(usernameInput?.value.trim() || '');
            const isPasswordMatch = validatePasswordMatch();

            if (!isNameValid || !isDescriptionValid || !isUsernameValid || !isPasswordMatch) {
                showNotification('Please fix all validation errors before submitting.', 'error');
                return false;
            }

            // Disable button & show loading
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Adding Company...';

            try {
                // Submit form via AJAX
                const formData = new FormData(companyForm);

                const response = await fetch(companyForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                       document.querySelector('[name="_token"]')?.value,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // ✅ Success - Show notification & auto-refresh
                    showNotification('✅ Company added successfully!', 'success');

                    // Auto-refresh page after 1.5 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);

                } else {
                    // ❌ Error - Show error message
                    const errorMessage = result.message || result.errors ?
                        Object.values(result.errors).flat().join(', ') :
                        'Failed to add company. Please try again.';

                    showNotification('❌ ' + errorMessage, 'error');

                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Company';
                }

            } catch (error) {
                console.error('Submit error:', error);
                showNotification('⚠ Network error. Please check your connection.', 'error');

                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Company';
            }
        });
    }

    // =========================================
    // 8. Notification Helper Function
    // =========================================
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existing = document.querySelector('.form-notification');
        if (existing) existing.remove();

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `form-notification form-notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
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

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);

        // Add animation keyframes if not already present
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // =========================================
    // 9. Initialize on Page Load
    // =========================================
    toggleSubmitButton();

/**
 * =========================================
 * 🔐 Company Login Modal Functions (GLOBAL SCOPE)
 * ========================================= */

// ✅ Make these functions globally accessible
window.openCompanyLoginModal = function(slug, companyName) {
    console.log('🔐 Modal opening for:', companyName, 'Slug:', slug);

    const modal = document.getElementById('companyLoginModal');
    const nameSpan = document.getElementById('modalCompanyName');
    const slugInput = document.getElementById('modalCompanySlug');
    const usernameInput = document.getElementById('modalUsername');
    const errorDiv = document.getElementById('modalError');

    if (!modal) {
        console.error('❌ Modal element not found!');
        return;
    }

    if (nameSpan) nameSpan.textContent = companyName;
    if (slugInput) slugInput.value = slug;

    if (usernameInput) usernameInput.value = '';
    const passwordInput = document.getElementById('modalPassword');
    if (passwordInput) passwordInput.value = '';
    if (errorDiv) {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
    }

    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.classList.add('active');

    if (usernameInput) setTimeout(() => usernameInput.focus(), 100);

    console.log('✅ Modal opened successfully');
};

window.closeCompanyLoginModal = function() {
    console.log('🔐 Modal closing...');
    const modal = document.getElementById('companyLoginModal');
    if (!modal) return;

    modal.classList.remove('active');
    setTimeout(() => { modal.style.display = 'none'; }, 300);
};

window.handleModalEscape = function(e) {
    if (e.key === 'Escape') window.closeCompanyLoginModal();
};

// =========================================
// 🔐 Company Login Form Handler
// =========================================
window.companyLoginForm = function(companyId, companyName) {
    const modal = document.getElementById('companyLoginModal');
    const title = document.getElementById('companyLoginTitle');
    const form = document.getElementById('companyLoginForm');
    
    if (!modal || !title || !form) {
        console.error('❌ Company login modal elements not found');
        return;
    }
    
    // Set modal title
    title.textContent = `🔐 Login to ${companyName}`;
    
    // Set form action
    form.action = `/company/${companyId}/login`;
    
    // Show modal
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.classList.add('active');
    
    // Focus first input
    const firstInput = form.querySelector('input');
    if (firstInput) firstInput.focus();
    
    console.log(`✅ Company login modal opened for: ${companyName}`);
};

// Close company login modal
window.closeCompanyLoginModal = function() {
    const modal = document.getElementById('companyLoginModal');
    if (!modal) return;
    
    modal.classList.remove('active');
    setTimeout(() => { modal.style.display = 'none'; }, 300);
    
    // Reset form
    const form = document.getElementById('companyLoginForm');
    if (form) form.reset();
};

window.showNotification = function(message, type = 'info') {
    console.log('📢 Notification:', message, type);
    const existing = document.querySelector('.form-notification');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `form-notification form-notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 12px 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999; font-weight: 500; animation: slideIn 0.3s ease; max-width: 350px;
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

    // ✅ Setting up modal listeners...
    console.log('📄 DOM Loaded - Setting up modal listeners...');

    // Close modal when clicking outside
    const modal = document.getElementById('companyLoginModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) window.closeCompanyLoginModal();
        });
    }

    // Handle modal form submission via AJAX
    const companyLoginForm = document.getElementById('companyLoginForm');
    if (companyLoginForm) {
        console.log('✅ Found companyLoginForm');

        companyLoginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('📤 Form submitted via AJAX');

            const submitBtn = document.getElementById('modalSubmitBtn');
            const btnText = document.getElementById('modalBtnText');
            const btnLoading = document.getElementById('modalBtnLoading');
            const errorDiv = document.getElementById('modalError');

            if (submitBtn) submitBtn.disabled = true;
            if (btnText) btnText.style.display = 'none';
            if (btnLoading) btnLoading.style.display = 'inline';
            if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }

            try {
                const formData = new FormData(companyLoginForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                console.log('📤 Sending to:', companyLoginForm.action);

                const response = await fetch(companyLoginForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json'
                    }
                });

                console.log('📥 Response status:', response.status);
                const result = await response.json();
                console.log('📥 Response JSON:', result);

                if (response.ok && result.success) {
                    console.log('✅ Login successful! Redirect URL:', result.redirect_url);

                    window.closeCompanyLoginModal();
                    window.showNotification('✅ Access granted! Redirecting...', 'success');

                    setTimeout(() => {
    // ✅ Use redirect_url from server, fallback to company.show
    const finalUrl = result.redirect_url || `/company/${result.company_slug}`;
    console.log('🔄 Redirecting to:', finalUrl);
    window.location.href = finalUrl;
    }, 1000);

                } else {
                    console.error('❌ Login failed:', result.message);
                    if (errorDiv) {
                        errorDiv.textContent = result.message || 'Invalid username or password.';
                        errorDiv.style.display = 'block';
                    }
                    if (submitBtn) submitBtn.disabled = false;
                    if (btnText) btnText.style.display = 'inline';
                    if (btnLoading) btnLoading.style.display = 'none';
                }

            } catch (error) {
                console.error('❌ Network error:', error);
                if (errorDiv) {
                    errorDiv.textContent = '⚠ Network error. Please try again.';
                    errorDiv.style.display = 'block';
                }
                if (submitBtn) submitBtn.disabled = false;
                if (btnText) btnText.style.display = 'inline';
                if (btnLoading) btnLoading.style.display = 'none';
            }
        });
    } else {
        console.error('❌ companyLoginForm not found!');
    }

    // Notification helper (if not already defined)
    function showNotification(message, type = 'info') {
    // Remove existing
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

});
