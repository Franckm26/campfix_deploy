<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS API Configuration (UnisSMS)
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'api_key'     => env('SMS_API_KEY'),
        'api_url'     => env('SMS_API_URL', 'https://unismsapi.com/api/sms'),
        'sender_name' => env('SMS_SENDER_NAME', 'CampFix'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supabase Storage Configuration
    |--------------------------------------------------------------------------
    */
    'supabase' => [
        // Try Vercel integration variables first, then custom variables
        'url' => env('SUPABASE_URL') ?? env('NEXT_PUBLIC_SUPABASE_URL'),
        'key' => env('SUPABASE_SERVICE_ROLE_KEY') ?? env('SUPABASE_KEY'),
        'bucket' => env('SUPABASE_BUCKET', 'concerns'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Microsoft OAuth Configuration
    |--------------------------------------------------------------------------
    */
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
        'tenant' => env('MICROSOFT_TENANT_ID', 'common'), // 'common', 'organizations', 'consumers', or specific tenant ID
        'scopes' => array_values(array_filter(array_map('trim', explode(',', env('MICROSOFT_OAUTH_SCOPES', 'User.Read'))))),
        'allowed_domains' => array_values(array_filter(array_map('trim', explode(',', env('MICROSOFT_ALLOWED_DOMAINS', 'novaliches.sti.edu.ph'))))),
        'enforce_tenant' => env('MICROSOFT_ENFORCE_TENANT', false),
    ],

];
