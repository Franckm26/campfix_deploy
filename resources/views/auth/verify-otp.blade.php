<!DOCTYPE html>
<html>
<head>
    <title>Campfix - Verify Code</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        <?php echo file_get_contents(public_path('css/auth-verify-otp.css')); ?>
        
        .countdown-timer {
            text-align: center;
            margin: 15px 0;
            font-size: 0.9rem;
            color: #666;
        }
        .countdown-timer.expired {
            color: #dc3545;
        }
        .countdown-timer .time {
            font-weight: bold;
            font-size: 1.1rem;
            color: #007bff;
            display: block;
            margin-top: 3px;
        }
        .countdown-timer.expired .time {
            color: #dc3545;
        }
        .resend-btn {
            display: none;
            margin: 15px auto;
            padding: 12px 40px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
            font-weight: 500;
        }
        .resend-btn:hover {
            background: #0056b3;
        }
        .resend-btn.show {
            display: block;
        }
        .btn-primary:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .resend-link.hidden {
            display: none;
        }
        .countdown-timer.hidden {
            display: none;
        }
        .try-another-way-link {
            color: #1a73e8;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background 0.2s;
            font-size: 14px;
        }
        .try-another-way-link:hover {
            background: #f1f3f4;
        }
    </style>
</head>
<body>

<div class="login-container">
    
    <!-- Logo -->
    <div class="logo-container">
        <div class="logo-wrapper">
            <?php
            $logoPath = public_path('Campfix/Images/logo.png');
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                echo '<img src="data:image/png;base64,' . $logoData . '" alt="Campfix Logo" class="logo-img">';
            }
            ?>
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
        <button type="submit" class="btn-primary" id="verifyBtn">
            Verify Code
        </button>
    </form>

    <!-- Countdown Timer (below verify button) -->
    <div class="countdown-timer" id="countdownTimer">
        <div>Code expires in</div>
        <span class="time" id="timeDisplay">5:00</span>
    </div>
    
    <!-- Resend Button (hidden initially, shows when timer expires) -->
    <form method="POST" action="{{ route('resend.otp') }}" id="resendForm" style="text-align: center;">
        @csrf
        <button type="submit" class="resend-btn" id="resendBtn">
            <i class="fas fa-redo"></i> Resend Code
        </button>
    </form>
    
    <!-- Resend Link (hidden initially, shows when timer expires) -->
    <p class="resend-link hidden" id="resendLink">
        Didn't receive the code? 
        <a href="{{ route('resend.otp') }}">Resend</a>
    </p>
    
    <!-- Try Another Way Link (Always visible) -->
    <div style="text-align: center; margin-top: 20px;">
        <a href="/otp-choice?from=verify" class="try-another-way-link">
            <i class="fas fa-sync-alt"></i> Try another way
        </a>
    </div>
    
</div>

<script>
// Countdown Timer
const countdownTimer = document.getElementById('countdownTimer');
const timeDisplay = document.getElementById('timeDisplay');
const verifyBtn = document.getElementById('verifyBtn');
const resendBtn = document.getElementById('resendBtn');
const resendLink = document.getElementById('resendLink');
const otpInputs = document.querySelectorAll('.otp-input');

let timeLeft = 300; // 5 minutes in seconds
let timerInterval;

// Check if we need to reset the timer (new OTP sent)
@if(session('reset_timer'))
    sessionStorage.removeItem('otpExpiryTime');
@endif

// Check if there's an existing timer from a previous page load
const storedExpiryTime = sessionStorage.getItem('otpExpiryTime');
if (storedExpiryTime && !@json(session('reset_timer'))) {
    const remainingTime = Math.floor((parseInt(storedExpiryTime) - Date.now()) / 1000);
    if (remainingTime > 0 && remainingTime <= 300) {
        timeLeft = remainingTime;
    } else if (remainingTime <= 0) {
        // Timer already expired
        timeLeft = 0;
    }
} else {
    // First time loading the page or timer reset, set the expiry time
    const expiryTime = Date.now() + (timeLeft * 1000);
    sessionStorage.setItem('otpExpiryTime', expiryTime);
}

function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    timeDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    if (timeLeft <= 0) {
        clearInterval(timerInterval);
        
        // Hide countdown timer
        countdownTimer.classList.add('hidden');
        
        // Disable verify button and OTP inputs
        verifyBtn.disabled = true;
        otpInputs.forEach(input => input.disabled = true);
        
        // Show resend link (not the button)
        resendLink.classList.remove('hidden');
        
        return;
    }
    
    timeLeft--;
}

function startTimer() {
    // Reset UI state
    countdownTimer.classList.remove('hidden');
    resendLink.classList.add('hidden');
    verifyBtn.disabled = false;
    otpInputs.forEach(input => input.disabled = false);
    
    // Clear any existing interval
    if (timerInterval) {
        clearInterval(timerInterval);
    }
    
    // Start the countdown
    timerInterval = setInterval(updateTimer, 1000);
    updateTimer(); // Initial call to display immediately
}

// Start the timer
startTimer();

// OTP Input Handling
otpInputs.forEach((input, index) => {
    // Handle input
    input.addEventListener('input', function(e) {
        const value = e.target.value;
        
        // Only allow numbers
        if (!/^\d*$/.test(value)) {
            e.target.value = '';
            return;
        }
        
        // Move to next input if value entered
        if (value && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }
        
        // Update hidden field
        updateOTPField();
    });
    
    // Handle backspace
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
            otpInputs[index - 1].focus();
        }
    });
    
    // Handle paste
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text').trim();
        
        // Only process if it's 6 digits
        if (/^\d{6}$/.test(pastedData)) {
            // Fill all inputs with the pasted digits
            pastedData.split('').forEach((digit, i) => {
                if (otpInputs[i]) {
                    otpInputs[i].value = digit;
                }
            });
            
            // Focus the last input
            otpInputs[5].focus();
            
            // Update hidden field
            updateOTPField();
        }
    });
});

// Update hidden OTP field
function updateOTPField() {
    const otpValue = Array.from(otpInputs).map(input => input.value).join('');
    document.getElementById('otp').value = otpValue;
}

// Auto-focus first input on page load
otpInputs[0].focus();
</script>

</body>
</html>
