<!DOCTYPE html>
<html>
<head>
    <title>Campfix - OTP Delivery Choice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/auth-otp-choice.css')); ?>">
</head>
<body>

<div class="login-container">
    
    <!-- Logo -->
    <div class="logo-container">
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="<?php echo e(asset('Campfix/Images/logo.png')); ?>" alt="STI Logo" class="logo-img">
            <span style="font-size: 28px; font-weight: 700; color: #1a1a1a;">Campfix</span>
        </div>
    </div>
    
    <!-- Title -->
    <h1 class="title">Choose verification method</h1>
    
    <!-- Subtitle -->
    <p class="subtitle">
        How would you like to receive your security code?
    </p>
    
    <!-- Error Message -->
    <?php if(session('error')): ?>
        <div class="alert alert-error">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>
    
    <!-- Success Message -->
    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    
    <form method="POST" action="/otp-delivery" id="otpForm">
        <?php echo csrf_field(); ?>
        
        <!-- Email Option -->
        <div class="form-group">
            <label class="radio-label" id="email-option">
                <input type="radio" name="delivery_method" value="email" required>
                <span class="radio-text">
                    <i class="fas fa-envelope" style="margin-right: 8px; color: #666;"></i>
                    Send to email: <strong><?php echo e(session('otp_email', 'your email')); ?></strong>
                </span>
            </label>
        </div>
        
        <!-- Phone Option - Only show if user has phone number -->
        <?php if(session('otp_phone')): ?>
        <div class="form-group">
            <label class="radio-label" id="phone-option">
                <input type="radio" name="delivery_method" value="phone">
                <span class="radio-text">
                    <i class="fas fa-mobile-alt" style="margin-right: 8px; color: #666;"></i>
                    Send to phone: 
                    <strong>
                        <?php
                            $phone = session('otp_phone');
                            if ($phone && strlen($phone) > 4) {
                                // Mask phone number: show first 2 digits (09) and last 2 digits
                                echo substr($phone, 0, 2) . '******' . substr($phone, -2);
                            } else {
                                echo $phone;
                            }
                        ?>
                    </strong>
                </span>
            </label>
        </div>
        <?php endif; ?>
        
        <!-- Submit Button -->
        <button type="submit" class="btn-primary" id="submitBtn">
            Send Code
        </button>
    </form>
    
    <!-- Back Link -->
    <a href="/" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to sign in
    </a>
    
</div>

<script src="<?php echo e(asset('js/auth-otp-choice.js')); ?>"></script>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\Campfix\resources\views/auth/otp-choice.blade.php ENDPATH**/ ?>