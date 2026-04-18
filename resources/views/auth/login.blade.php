<!DOCTYPE html>
<html>
<head>
    <title>Campfix Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/auth-login.css') }}">
</head>
<body>

<div class="login-container">
    <div class="logo-container">
        <img src="{{ asset('Campfix/Images/logo.png') }}" alt="Campfix Logo" class="logo-img">
    </div>

    <h2>Login</h2>

    @if(session('error'))
    <div class="error-message">
        {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="/login">
        @csrf

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Login</button>
    </form>

    <p class="register-link">
        No account?
        <a href="/register">Register</a>
    </p>
</div>

</body>
</html>
