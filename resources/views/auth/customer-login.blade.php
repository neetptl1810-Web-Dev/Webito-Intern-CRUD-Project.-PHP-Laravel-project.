<!DOCTYPE html>
<html>

<head>
    <title>Login - Customer Account</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>👤 Customer Login</h2>

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('customer.login') }}">
                @csrf

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>

            <p class="auth-switch">
                Don't have an account?
                <a href="{{ route('customer.register.form') }}">Register here</a>
            </p>

            <p class="auth-switch">
                Are you an admin?
                <a href="{{ route('login') }}">Login as Admin</a>
            </p>
        </div>
    </div>
</body>

</html>
