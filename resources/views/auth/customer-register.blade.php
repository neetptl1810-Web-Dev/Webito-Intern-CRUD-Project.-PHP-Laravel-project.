<!DOCTYPE html>
<html>

<head>
    <title>Register - Customer Account</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>👤 Create Account</h2>

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('customer.register') }}">
                @csrf

                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="3">{{ old('address') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn-primary">Create Account</button>
            </form>

            <p class="auth-switch">
                Already have an account?
                <a href="{{ route('customer.login.form') }}">Login here</a>
            </p>

            <p class="auth-switch">
                Are you an admin?
                <a href="{{ route('login') }}">Login as Admin</a>
            </p>
        </div>
    </div>
</body>

</html>
