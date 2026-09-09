<?php

/**
 * Short-running Vercel endpoint used by cron-job.org.
 *
 * Each invocation processes up to 50 recipients. The Artisan command keeps
 * deduplication state in Supabase. There is no application daily cap;
 * the mail provider's own quotas still apply.
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
    $exitCode = $kernel->call('users:send-welcome-emails', ['--batch' => 50]);
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
