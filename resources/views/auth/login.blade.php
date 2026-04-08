<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="icon" type="image/x-icon" href="{{ asset('internicon.ico') }}">

<div class="animated-particles"></div>

<div class="auth-container">
    <div class="auth-box">
        <h2>Login</h2>

        @if(session('error'))
            <p style="color: red; margin-bottom: 10px;">
                {{ session('error') }}
            </p>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <input type="email" name="email" placeholder="Enter your email" required>

            <input type="password" name="password" placeholder="Enter password" required>

            <button type="submit">Login</button>
        </form>

        <a href="{{ url('/register') }}">Create account</a>
    </div>
</div> type="password"
name="password"
placeholder="••••••••"
required
>
</div>
</div>

<button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">
    Access Dashboard <i class="fas fa-arrow-right"></i>
</button>
</form>

<a href="{{ url('/register') }}" class="login-link">Create account</a>
</div>
</div>
</body>

</html>
