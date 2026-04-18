<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CampFix OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            text-align: center;
        }
        .otp-code {
            background-color: #007bff;
            color: white;
            font-size: 32px;
            font-weight: bold;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 4px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CampFix Login Verification</h1>
    </div>
    
    <div class="content">
        <h2>Your OTP Verification Code</h2>
        <p>Please use the following code to complete your login:</p>
        
        <div class="otp-code"><?php echo e($otp); ?></div>
        
        <p><strong>This code expires in 5 minutes.</strong></p>
        
        <p>If you didn't request this code, please ignore this email.</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message from CampFix. Please do not reply to this email.</p>
    </div>
</body>
</html><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/emails/otp.blade.php ENDPATH**/ ?>