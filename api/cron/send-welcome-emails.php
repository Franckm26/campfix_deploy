<?php

/**
 * Short-running Vercel endpoint used by cron-job.org.
 *
 * Each invocation processes only five recipients. The Artisan command keeps
 * the authoritative daily limit and deduplication state in Supabase.
 */

header('Content-Type: application/json');

$cronSecret = getenv('CRON_SECRET');
$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

if (! $cronSecret || ! hash_equals('Bearer '.$cronSecret, $authorization)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    $exitCode = $kernel->call('users:send-welcome-emails', ['--batch' => 5]);
    $output = trim($kernel->output());

    http_response_code($exitCode === 0 ? 200 : 500);
    echo json_encode([
        'success' => $exitCode === 0,
        'message' => $output,
    ]);
} catch (Throwable $exception) {
    report($exception);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Welcome-email batch failed. Check the Vercel function logs.',
    ]);
}
