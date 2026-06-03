<?php

/**
 * Debug endpoint to test CRON_SECRET authentication
 */

header('Content-Type: application/json');

$cronSecret = getenv('CRON_SECRET');
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

// Also check alternate header names
$altAuth = $_SERVER['Authorization'] ?? '';
$httpAuth = $_SERVER['HTTP_AUTHENTICATION'] ?? '';

echo json_encode([
    'cron_secret_exists' => !empty($cronSecret),
    'cron_secret_length' => strlen($cronSecret),
    'cron_secret_preview' => $cronSecret ? substr($cronSecret, 0, 10) . '...' : 'NOT SET',
    'auth_header_received' => !empty($authHeader),
    'auth_header_preview' => $authHeader ? substr($authHeader, 0, 20) . '...' : 'NOT RECEIVED',
    'auth_header_full_length' => strlen($authHeader),
    'alternate_auth' => !empty($altAuth) ? substr($altAuth, 0, 20) . '...' : 'none',
    'http_auth' => !empty($httpAuth) ? substr($httpAuth, 0, 20) . '...' : 'none',
    'expected_format' => 'Bearer ' . ($cronSecret ? substr($cronSecret, 0, 10) . '...' : 'SECRET'),
    'match' => $authHeader === 'Bearer ' . $cronSecret,
    'all_headers' => array_filter($_SERVER, function($key) {
        return strpos($key, 'HTTP_') === 0 || strpos(strtolower($key), 'auth') !== false;
    }, ARRAY_FILTER_USE_KEY)
], JSON_PRETTY_PRINT);
