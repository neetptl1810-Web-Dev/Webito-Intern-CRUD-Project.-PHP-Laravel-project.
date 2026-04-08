<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Company Management</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('internicon.ico') }}">

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>

    <div class="animated-particles"></div>
<div class="auth-container">
    <div class="auth-box">
        <h2>Create Account</h2>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/register" id="registerForm">
            @csrf

            {{-- Step 1: Name --}}
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text"
                       name="name"
                       id="name"
                       placeholder="Enter your full name"
                       value="{{ old('name') }}"
                       required
                       autofocus>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Step 2: Email --}}
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email"
                       name="email"
                       id="email"
                       placeholder="Enter your email"
                       value="{{ old('email') }}"
                       required>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Step 3: Send Verification Code Button --}}
            <button type="button"
                    class="btn-send-code"
                    id="sendCodeBtn"
                    onclick="sendVerificationCode()">
                📧 Send Verification Code
            </button>

            {{-- Step 4: Verification Code Section (Hidden by default) --}}
            <div class="verification-section" id="verificationSection">
                <label>Enter Verification Code *</label>
                <p class="verification-info">
                    We sent a 6-digit code to <strong id="emailDisplay"></strong>
                </p>

                <div class="verification-code-input">
                    <input type="text" maxlength="1" class="code-digit" data-index="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="1" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="2" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="3" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="4" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="5" inputmode="numeric">
                </div>
                <input type="hidden" name="verification_code" id="verificationCode">

                @error('verification_code')
                    <span class="error-message">{{ $message }}</span>
                @enderror

                <div class="resend-code">
                    <span>Didn't receive code? </span>
                    <button type="button" id="resendBtn" onclick="resendCode()">Resend</button>
                    <span id="resendTimer" class="resend-timer"></span>
                </div>
            </div>

            {{-- Step 5: Password --}}
<div class="form-group">
    <label for="password">Password *</label>
    <input type="password"
           name="password"
           id="password"
           placeholder="Create a password"
           required
           minlength="8"
           pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}">

    {{-- Password Strength Meter --}}
    <div class="password-strength" id="passwordStrengthContainer" style="display: none;">
        <div class="strength-meter-container">
            <div class="strength-meter-track">
                <div class="strength-meter-fill" id="passwordStrengthMeter"></div>
            </div>
            <span class="strength-meter-label" id="passwordStrengthLabel">Strength</span>
        </div>
        <div class="password-requirements">
            <span class="password-requirement" data-rule="length">8+ characters</span>
            <span class="password-requirement" data-rule="uppercase">1 uppercase letter</span>
            <span class="password-requirement" data-rule="lowercase">1 lowercase letter</span>
            <span class="password-requirement" data-rule="number">1 number</span>
            <span class="password-requirement" data-rule="special">1 special symbol</span>
        </div>
    </div>

    @error('password')
        <span class="error-message">{{ $message }}</span>
    @enderror
    <small class="form-hint">Must include: uppercase, lowercase, number & special symbol</small>
</div>

            {{-- Step 6: Confirm Password --}}
            <div class="form-group">
                <label for="password_confirmation">Confirm Password *</label>
                <input type="password"
                       name="password_confirmation"
                       id="password_confirmation"
                       placeholder="Re-enter your password"
                       required>
                @error('password_confirmation')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Step 7: Terms Checkbox --}}
            <div class="terms-checkbox">
                <input type="checkbox"
                       name="accept_terms"
                       id="accept_terms"
                       required
                       {{ old('accept_terms') ? 'checked' : '' }}>
                <label for="accept_terms">
                    I accept to share my email address with this demo website.
                    Your email will only be used for account verification and important updates.
                </label>
            </div>
            @error('accept_terms')
                <span class="error-message">{{ $message }}</span>
            @enderror

            {{-- Submit Button --}}
            <button type="submit"
                    class="btn-primary btn-submit"
                    id="submitBtn"
                    disabled>
                Create Account
            </button>
        </form>

        <a href="/login" class="login-link">
            Already have an account? Login
        </a>
    </div>
</div>

{{-- JavaScript --}}
<script src="{{ asset('js/register.js') }}"></script>

</body>
</html>
