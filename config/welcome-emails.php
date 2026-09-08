<?php

return [
    // No application daily cap. Provider quotas still apply.
    'send_at' => env('WELCOME_EMAIL_SEND_AT', '00:10'),
    'timezone' => env('WELCOME_EMAIL_TIMEZONE', 'UTC'),
    'max_attempts' => max(1, (int) env('WELCOME_EMAIL_MAX_ATTEMPTS', 5)),
];
