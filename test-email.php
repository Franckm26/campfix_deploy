<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    Illuminate\Support\Facades\Mail::raw('This is a test email from CampFix', function ($message) {
        $message->to('test@example.com')
                ->subject('CampFix Email Test');
    });
    
    echo "✓ Email sent successfully (or logged if SMTP failed)\n";
    echo "Check storage/logs/laravel.log for email content if SMTP failed\n";
} catch (Exception $e) {
    echo "✗ Email failed: " . $e->getMessage() . "\n";
}
