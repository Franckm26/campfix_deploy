<!DOCTYPE html>
<html>
<head>
    <title>Campfix - Verify Code</title>
    <meta name="viewport" content="width=device=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth-verify-otp.css') }}">
</head>
<body>

<div class="login-container">
    
    <!-- Logo -->
    <div class="logo-container">
        <div class="logo-wrapper">
            <img src="{{ asset('Campfix/Images/logo.png') }}" alt="STI Logo" class="logo-img">
            <span class="logo-text">Campfix</span>
        </div>
    </div>
    
    <!-- Title -->
    <h1 class="title">Verify {{ session('otp_delivery') == 'phone' ? 'SMS' : 'Email' }}</h1>
    
    <!-- Subtitle -->
    <p class="subtitle">
        Enter the 6-digit code sent to<br>
        <strong>
            @php
                $destination = session('otp_destination') ?? session('otp_email') ?? 'your email';
                if (session('otp_delivery') == 'phone' && strlen($destination) > 4) {
                    // Mask phone number: show first 2 digits (09) and last 2 digits
                    $destination = substr($destination, 0, 2) . '******' . substr($destination, -2);
                }
                echo $destination;
            @endphp
        </strong>
    </p>
    
    <!-- Error Message -->
    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif
    
    <form method="POST" action="/verify-otp" id="otpForm">
        @csrf
        
        <!-- OTP Input Boxes -->
        <div class="otp-container">
            <input type="text" maxlength="1" class="otp-input" data-index="0">
            <input type="text" maxlength="1" class="otp-input" data-index="1">
            <input type="text" maxlength="1" class="otp-input" data-index="2">
            <input type="text" maxlength="1" class="otp-input" data-index="3">
            <input type="text" maxlength="1" class="otp-input" data-index="4">
            <input type="text" maxlength="1" class="otp-input" data-index="5">
        </div>
        
        <!-- Hidden field to combine OTP -->
        <input type="hidden" name="otp" id="otp">
        
        <!-- Submit Button -->
        <button type="submit" class="btn-primary">
            Verify Code
        </button>
    </form>
    
    <!-- Resend Link -->
    <p class="resend-link">
        Didn't receive the code? 
        <a href="{{ route('resend.otp') }}">Resend</a>
    </p>
    
</div>

<script src="{{ asset('js/auth-verify-otp.js') }}"></script>

</body>
</html>
