<?php

return [
    // Brevo Free allows 300 total email sends per day. Reduce this value if
    // CampFix must reserve part of that allowance for OTP and workflow mail.
    'daily_limit' => min(300, max(1, (int) env('WELCOME_EMAIL_DAILY_LIMIT', 300))),
    'send_at' => env('WELCOME_EMAIL_SEND_AT', '00:10'),
    'timezone' => env('WELCOME_EMAIL_TIMEZONE', 'UTC'),
    'max_attempts' => max(1, (int) env('WELCOME_EMAIL_MAX_ATTEMPTS', 5)),
];
