<!DOCTYPE html>
<html>
<head>
    <title>Campfix - Verification Method</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Back button at top */
        .back-button-top {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #5f6368;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .back-button-top:hover {
            background: #f1f3f4;
        }

        .back-button-top i {
            font-size: 16px;
        }

        /* Main container */
        .container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            width: 100%;
            max-width: 450px;
            padding: 48px 40px 36px;
        }

        /* Logo and branding - centered */
        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-img {
            width: 48px;
            height: 48px;
        }

        .brand-name {
            font-size: 28px;
            font-weight: 500;
            color: #202124;
        }

        /* Title */
        .title {
            font-size: 24px;
            font-weight: 400;
            color: #202124;
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 14px;
            color: #5f6368;
            text-align: center;
            margin-bottom: 24px;
        }

        /* Alert messages */
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-error {
            background: #fce8e6;
            color: #c5221f;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #e6f4ea;
            color: #137333;
            border: 1px solid #c3e6cd;
        }

        /* Primary method - always visible */
        .primary-method {
            margin-bottom: 16px;
        }

        .method-card {
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .method-card:hover {
            background: #f8f9fa;
            border-color: #4285f4;
        }

        .method-card.selected {
            background: #e8f0fe;
            border-color: #4285f4;
            border-width: 2px;
        }

        .method-icon {
            font-size: 20px;
            color: #5f6368;
        }

        .method-text {
            flex: 1;
        }

        .method-label {
            font-size: 14px;
            color: #5f6368;
            margin-bottom: 2px;
        }

        .method-value {
            font-size: 15px;
            color: #202124;
            font-weight: 500;
        }

        /* Hidden methods container */
        .hidden-methods {
            display: none;
            margin-bottom: 16px;
        }

        .hidden-methods.show {
            display: block;
        }

        .hidden-method-item {
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .hidden-method-item:hover {
            background: #f8f9fa;
            border-color: #4285f4;
        }

        .hidden-method-item.selected {
            background: #e8f0fe;
            border-color: #4285f4;
            border-width: 2px;
        }

        /* Try another way link */
        .try-another-way {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #1a73e8;
            text-decoration: none;
            font-size: 14px;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .try-another-way:hover {
            background: #f1f3f4;
        }

        .try-another-way.hidden {
            display: none;
        }

        /* Submit button */
        .btn-primary {
            width: 100%;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 14px 24px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #1765cc;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .btn-primary:active {
            background: #1557b0;
        }

        /* Hide radio buttons */
        input[type="radio"] {
            display: none;
        }

        @media (max-width: 480px) {
            .card {
                padding: 32px 24px;
            }

            .title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<!-- Back button at top -->
<a href="/" class="back-button-top">
    <i class="fas fa-arrow-left"></i> Back to sign in
</a>

<div class="container">
    <div class="card">
        
        <!-- Centered Logo and Brand -->
        <div class="logo-container">
            <div class="logo-wrapper">
                <?php
                $logoPath = public_path('Campfix/Images/logo.png');
                if (file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                    echo '<img src="data:image/png;base64,' . $logoData . '" alt="Campfix Logo" class="logo-img">';
                }
                ?>
                <span class="brand-name">Campfix</span>
            </div>
        </div>
        
        <!-- Title -->
        <h1 class="title">2-Step Verification</h1>
        <p class="subtitle">To help keep your account safe, Campfix wants to make sure it's really you trying to sign in</p>
        
        <!-- Error Message -->
        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        <form method="POST" action="/otp-delivery" id="otpForm">
            @csrf
            
            @php
                // Check if this is from "Try another way" (coming back from verify page)
                $fromVerify = request()->get('from') === 'verify';
                $lastMethod = session('last_otp_method');
            @endphp
            
            @if($fromVerify && $lastMethod)
                <!-- Coming from verify page - show alternative methods excluding the one used -->
                
                @if($lastMethod === 'email')
                    <!-- User chose primary email, show Phone and Backup Email -->
                    
                    @if(session('otp_phone'))
                    <div class="primary-method">
                        <div class="method-card selected" onclick="selectMethod('phone', this)">
                            <i class="fas fa-mobile-alt method-icon"></i>
                            <div class="method-text">
                                <div class="method-label">Send to phone</div>
                                <div class="method-value">
                                    @php
                                        $phone = session('otp_phone');
                                        if ($phone && strlen($phone) > 2) {
                                            echo str_repeat('•', strlen($phone) - 2) . substr($phone, -2);
                                        } else {
                                            echo $phone;
                                        }
                                    @endphp
                                </div>
                            </div>
                        </div>
                        <input type="radio" name="delivery_method" value="phone" checked required>
                    </div>
                    @endif
                    
                    @if(session('otp_backup_email'))
                    <div class="primary-method">
                        <div class="method-card @if(!session('otp_phone')) selected @endif" onclick="selectMethod('backup_email', this)">
                            <i class="fas fa-envelope method-icon"></i>
                            <div class="method-text">
                                <div class="method-label">Send to backup email</div>
                                <div class="method-value">
                                    @php
                                        $backupEmail = session('otp_backup_email');
                                        $parts = explode('@', $backupEmail);
                                        if (count($parts) === 2) {
                                            $username = $parts[0];
                                            $domain = $parts[1];
                                            if (strlen($username) > 3) {
                                                $masked = substr($username, 0, 2) . str_repeat('*', strlen($username) - 3) . substr($username, -1);
                                            } else {
                                                $masked = substr($username, 0, 1) . str_repeat('*', strlen($username) - 1);
                                            }
                                            echo $masked . '@' . $domain;
                                        } else {
                                            echo $backupEmail;
                                        }
                                    @endphp
                                </div>
                            </div>
                        </div>
                        <input type="radio" name="delivery_method" value="backup_email" @if(!session('otp_phone')) checked @endif>
                    </div>
                    @endif
                    
                @elseif($lastMethod === 'phone')
                    <!-- User chose phone, show Primary Email and Backup Email -->
                    
                    <div class="primary-method">
                        <div class="method-card selected" onclick="selectMethod('email', this)">
                            <i class="fas fa-envelope method-icon"></i>
                            <div class="method-text">
                                <div class="method-label">Send to email</div>
                                <div class="method-value">{{ session('otp_email', 'your email') }}</div>
                            </div>
                        </div>
                        <input type="radio" name="delivery_method" value="email" checked required>
                    </div>
                    
                    @if(session('otp_backup_email'))
                    <div class="primary-method">
                        <div class="method-card" onclick="selectMethod('backup_email', this)">
                            <i class="fas fa-envelope method-icon"></i>
                            <div class="method-text">
                                <div class="method-label">Send to backup email</div>
                                <div class="method-value">
                                    @php
                                        $backupEmail = session('otp_backup_email');
                                        $parts = explode('@', $backupEmail);
                                        if (count($parts) === 2) {
                                            $username = $parts[0];
                                            $domain = $parts[1];
                                            if (strlen($username) > 3) {
                                                $masked = substr($username, 0, 2) . str_repeat('*', strlen($username) - 3) . substr($username, -1);
                                            } else {
                                                $masked = substr($username, 0, 1) . str_repeat('*', strlen($username) - 1);
                                            }
                                            echo $masked . '@' . $domain;
                                        } else {
                                            echo $backupEmail;
                                        }
                                    @endphp
                                </div>
                            </div>
                        </div>
                        <input type="radio" name="delivery_method" value="backup_email">
                    </div>
                    @endif
                    
                @endif
                
            @else
                <!-- Initial load - show Primary Email and Phone only (NO backup email) -->
                
                <div class="primary-method">
                    <div class="method-card selected" onclick="selectMethod('email', this)">
                        <i class="fas fa-envelope method-icon"></i>
                        <div class="method-text">
                            <div class="method-label">Send to email</div>
                            <div class="method-value">{{ session('otp_email', 'your email') }}</div>
                        </div>
                    </div>
                    <input type="radio" name="delivery_method" value="email" checked required>
                </div>
                
                @if(session('otp_phone'))
                <div class="primary-method">
                    <div class="method-card" onclick="selectMethod('phone', this)">
                        <i class="fas fa-mobile-alt method-icon"></i>
                        <div class="method-text">
                            <div class="method-label">Send to phone</div>
                            <div class="method-value">
                                @php
                                    $phone = session('otp_phone');
                                    if ($phone && strlen($phone) > 2) {
                                        echo str_repeat('•', strlen($phone) - 2) . substr($phone, -2);
                                    } else {
                                        echo $phone;
                                    }
                                @endphp
                            </div>
                        </div>
                    </div>
                    <input type="radio" name="delivery_method" value="phone">
                </div>
                @endif
                
            @endif
            
            <!-- Submit Button -->
            <button type="submit" class="btn-primary">
                Continue
            </button>
        </form>
        
    </div>
</div>

<script>
// Show other verification methods
function showOtherMethods(event) {
    event.preventDefault();
    document.getElementById('hiddenMethods').classList.add('show');
    document.getElementById('tryAnotherWayLink').classList.add('hidden');
}

// Select a method
function selectMethod(method, element) {
    // Remove selected class from all method cards
    document.querySelectorAll('.method-card, .hidden-method-item').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked card
    element.classList.add('selected');
    
    // Check the corresponding radio button
    const radio = document.querySelector(`input[value="${method}"]`);
    if (radio) {
        radio.checked = true;
    }
}
</script>

</body>
</html>
