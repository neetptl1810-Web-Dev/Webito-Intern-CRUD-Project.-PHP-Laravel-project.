/**
 * Registration Form JavaScript
 * Features:
 * - Name: Auto-capitalize first letter of each word
 * - Email: Auto-convert to lowercase
 * - Password: Validation (uppercase, lowercase, number, special char, 8+ chars)
 * - Email verification code with 6-digit input
 * - Real-time form validation & strength meter
 */

// =========================================
// 1. HELPER FUNCTIONS (Define First!)
// =========================================

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#10b981' : '#e53e3e'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(notification);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

/**
 * Get CSRF token from meta tag or form
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ||
           document.querySelector('[name="_token"]')?.value || '';
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Update hidden field with combined verification code
 */
function updateVerificationCode() {
    const digits = document.querySelectorAll('.code-digit');
    const code = Array.from(digits).map(input => input.value).join('');
    const verificationInput = document.getElementById('verificationCode');
    if (verificationInput) {
        verificationInput.value = code;
    }
    toggleSubmitButton();
}

/**
 * Toggle submit button based on form validity
 */
function toggleSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    if (!submitBtn) return;

    const codeInput = document.getElementById('verificationCode');
    const termsCheckbox = document.getElementById('accept_terms');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');

    const code = codeInput?.value || '';
    const terms = termsCheckbox?.checked || false;
    const password = passwordInput?.value || '';
    const confirm = confirmInput?.value || '';

    const isCodeValid = code.length === 6;
    const passwordResult = validatePassword(password);
    const isPasswordValid = passwordResult.valid && password === confirm && password.length > 0;
    const isTermsAccepted = terms;

    submitBtn.disabled = !(isCodeValid && isPasswordValid && isTermsAccepted);
}

// =========================================
// 2. PASSWORD VALIDATION FUNCTIONS
// =========================================

/**
 * Password requirements configuration
 */
const PASSWORD_RULES = {
    minLength: 8,
    requiresUppercase: true,
    requiresLowercase: true,
    requiresNumber: true,
    requiresSpecial: true,
    specialChars: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/
};

/**
 * Check if password meets all requirements
 */
function validatePassword(password) {
    if (!password) {
        return {
            valid: false,
            checks: {
                length: false,
                uppercase: false,
                lowercase: false,
                number: false,
                special: false
            }
        };
    }

    const checks = {
        length: password.length >= PASSWORD_RULES.minLength,
        uppercase: PASSWORD_RULES.requiresUppercase ? /[A-Z]/.test(password) : true,
        lowercase: PASSWORD_RULES.requiresLowercase ? /[a-z]/.test(password) : true,
        number: PASSWORD_RULES.requiresNumber ? /[0-9]/.test(password) : true,
        special: PASSWORD_RULES.requiresSpecial ? PASSWORD_RULES.specialChars.test(password) : true,
    };

    return {
        valid: Object.values(checks).every(Boolean),
        checks: checks
    };
}

/**
 * Get password strength score (0-4)
 */
function getPasswordStrength(password) {
    if (!password) return 0;

    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) score++;
    return score;
}

/**
 * Get strength label and color
 */
function getStrengthLabel(score) {
    const labels = [
        { text: 'Very Weak', color: '#e53e3e' },
        { text: 'Weak', color: '#ed8936' },
        { text: 'Fair', color: '#ecc94b' },
        { text: 'Good', color: '#48bb78' },
        { text: 'Strong', color: '#38a169' }
    ];
    return labels[score] || labels[0];
}

/**
 * Update password strength meter UI
 */
function updatePasswordStrength(password) {
    const meter = document.getElementById('passwordStrengthMeter');
    const label = document.getElementById('passwordStrengthLabel');
    const requirements = document.querySelectorAll('.password-requirement');

    if (!meter || !label) return;

    const result = validatePassword(password);
    const score = getPasswordStrength(password);
    const strength = getStrengthLabel(score);

    // Update meter bar
    meter.style.width = `${(score / 4) * 100}%`;
    meter.style.background = strength.color;

    // Update label
    label.textContent = strength.text;
    label.style.color = strength.color;

    // Update requirement indicators
    requirements.forEach(req => {
        const rule = req.dataset.rule;
        const passed = result.checks[rule];
        req.classList.toggle('passed', passed);
        req.classList.toggle('failed', !passed && password.length > 0);
    });

    return result.valid;
}

// =========================================
// 3. TIMER & UI FUNCTIONS
// =========================================

/**
 * Resend timer (60 seconds cooldown)
 */
function startResendTimer() {
    let seconds = 60;
    const timerSpan = document.getElementById('resendTimer');
    const resendBtn = document.getElementById('resendBtn');

    if (!timerSpan || !resendBtn) return;

    resendBtn.disabled = true;
    timerSpan.style.display = 'inline';
    timerSpan.textContent = `(60s)`;

    const interval = setInterval(() => {
        seconds--;
        timerSpan.textContent = `(${seconds}s)`;

        if (seconds <= 0) {
            clearInterval(interval);
            resendBtn.disabled = false;
            timerSpan.style.display = 'none';
            timerSpan.textContent = '';
        }
    }, 1000);
}

/**
 * Resend verification code
 */
function resendCode() {
    const resendBtn = document.getElementById('resendBtn');
    const timerSpan = document.getElementById('resendTimer');

    if (resendBtn) resendBtn.disabled = true;
    if (timerSpan) timerSpan.style.display = 'inline';

    sendVerificationCode();
    startResendTimer();
}

// =========================================
// 4. MAIN FUNCTION: Send Verification Code
// =========================================

/**
 * Send verification code via AJAX
 */
async function sendVerificationCode() {
    const emailInput = document.getElementById('email');
    const sendBtn = document.getElementById('sendCodeBtn');

    if (!emailInput || !sendBtn) {
        console.error('Required elements not found');
        return;
    }

    const email = emailInput.value.trim();

    // Validate email first
    if (!email || !isValidEmail(email)) {
        showNotification('Please enter a valid email address first.', 'error');
        emailInput.focus();
        return;
    }

    // Disable button & show loading
    sendBtn.disabled = true;
    sendBtn.classList.add('loading');
    sendBtn.innerHTML = '⏳ Sending...';

    try {
        const response = await fetch('/send-verification-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ email: email })
        });

        // ✅ Check if response is JSON before parsing
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // Server returned HTML (e.g., 404 page)
            const text = await response.text();
            console.error('Server returned non-JSON response:', text.substring(0, 200));
            throw new Error('Server error: ' + response.status + ' ' + response.statusText);
        }

        const data = await response.json();

        if (response.ok && data.success) {
    // ✅ TESTING MODE: Show code directly in notification
    if (data.dev_code) {
        showNotification(`🔑 Verification Code For Demo: ${data.dev_code}`, 'success');
        console.log('✅ TESTING MODE - Code for', data.email, ':', data.dev_code);
        console.log('⏰ Expires in:', data.expires_in || '10 minutes');
    } else {
        // Production: Email was sent
        showNotification('Verification code sent to your email!', 'success');
    }

    // Show verification section
    const verificationSection = document.getElementById('verificationSection');
    const emailDisplay = document.getElementById('emailDisplay');

    if (verificationSection) verificationSection.classList.add('active');
    if (emailDisplay) emailDisplay.textContent = data.email || email;

    // Start resend timer
    startResendTimer();

    // Focus first code input
    const firstCodeInput = document.querySelector('.code-digit');
    if (firstCodeInput) firstCodeInput.focus();

} else {
    // Handle API error message
    showNotification(data.message || 'Failed to send code. Please try again.', 'error');
}
    } catch (error) {
        console.error('Error:', error);

        // Show user-friendly error
        if (error.message.includes('404')) {
            showNotification('Verification service not available. Please try again later.', 'error');
        } else if (error.message.includes('NetworkError')) {
            showNotification('Connection error. Please check your internet.', 'error');
        } else {
            showNotification('Failed to send verification code. Please try again.', 'error');
        }
    } finally {
        sendBtn.disabled = false;
        sendBtn.classList.remove('loading');
        sendBtn.innerHTML = '📧 Send Verification Code';
    }
}

// =========================================
// 5. EVENT LISTENERS (Run on DOM Load)
// =========================================

document.addEventListener('DOMContentLoaded', function() {

    // -------------------------------------
    // Verification Code Input Handling
    // -------------------------------------
    const codeInputs = document.querySelectorAll('.code-digit');

    codeInputs.forEach((input, index, inputs) => {
        // Auto-advance to next input
        input.addEventListener('input', function(e) {
            const value = e.target.value;

            // Only allow numbers
            if (!/^\d*$/.test(value)) {
                e.target.value = '';
                return;
            }

            // Mark as filled
            if (value.length === 1) {
                e.target.classList.add('filled');

                // Move to next input
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }

            // Combine all digits into hidden field
            updateVerificationCode();
        });

        // Handle backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].classList.remove('filled');
            }
        });

        // Handle paste
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, 6);

            digits.split('').forEach((digit, i) => {
                if (inputs[i]) {
                    inputs[i].value = digit;
                    inputs[i].classList.add('filled');
                }
            });

            if (digits.length === 6 && inputs[5]) {
                inputs[5].focus();
            }

            updateVerificationCode();
        });

        // Handle focus - select all text
        input.addEventListener('focus', function() {
            this.select();
        });
    });

    // -------------------------------------
    // Name Field: Auto-Capitalize Each Word
    // -------------------------------------
    const nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('input', function(e) {
            let value = e.target.value;
            // Capitalize first letter of each word using toUpperCase()
            value = value.replace(/\b\w/g, char => char.toUpperCase());
            e.target.value = value;
        });

        nameInput.addEventListener('blur', function(e) {
            let value = e.target.value;
            value = value.replace(/\b\w/g, char => char.toUpperCase());
            e.target.value = value;
        });
    }

    // -------------------------------------
    // Email Field: Auto-Convert to Lowercase
    // -------------------------------------
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', function(e) {
            // Convert all letters to lowercase using toLowerCase()
            e.target.value = e.target.value.toLowerCase();
        });

        emailInput.addEventListener('blur', function(e) {
            e.target.value = e.target.value.toLowerCase();
        });

        // Email validation on blur
        emailInput.addEventListener('blur', function() {
            if (this.value && !isValidEmail(this.value)) {
                showNotification('Please enter a valid email address.', 'error');
            }
        });
    }

    // -------------------------------------
    // Password Field: Validation & Strength
    // -------------------------------------
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const termsCheckbox = document.getElementById('accept_terms');
    const strengthContainer = document.getElementById('passwordStrengthContainer');

    if (passwordInput) {
        // Show strength meter on focus
        passwordInput.addEventListener('focus', function() {
            if (strengthContainer) {
                strengthContainer.style.display = 'block';
            }
        });

        // Validate on input
        passwordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value);
            toggleSubmitButton();

            // Hide meter if password is empty
            if (strengthContainer && !this.value) {
                strengthContainer.style.display = 'none';
            }
        });

        // Initialize on page load if form was re-submitted
        if (passwordInput.value && strengthContainer) {
            strengthContainer.style.display = 'block';
            updatePasswordStrength(passwordInput.value);
        }
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', toggleSubmitButton);
    }

    if (termsCheckbox) {
        termsCheckbox.addEventListener('change', toggleSubmitButton);
    }

    // -------------------------------------
    // Verification Code: Auto-submit when complete
    // -------------------------------------
    const lastCodeInput = document.querySelectorAll('.code-digit')[5];
    if (lastCodeInput) {
        lastCodeInput.addEventListener('input', function() {
            if (this.value) {
                toggleSubmitButton();
            }
        });
    }

    // -------------------------------------
    // Initialize submit button state
    // -------------------------------------
    toggleSubmitButton();
});

// =========================================
// 6. ANIMATION KEYFRAMES (Injected via JS)
// =========================================

const style = document.createElement('style');
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
