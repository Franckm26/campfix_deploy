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

// Allow test mode without authorization for debugging
$testMode = isset($_GET['mode']) && $_GET['mode'] === 'test';

if (!$testMode && (! $cronSecret || ! hash_equals('Bearer '.$cronSecret, $authorization))) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    // Reduce batch size to 50 for faster processing and avoid timeouts
    $batchSize = $testMode ? 1 : 50;
    
    $exitCode = $kernel->call('users:send-welcome-emails', ['--batch' => $batchSize]);
    $output = trim($kernel->output());

    http_response_code($exitCode === 0 ? 200 : 500);
    echo json_encode([
        'success' => $exitCode === 0,
        'message' => $output,
        'batch_size' => $batchSize,
        'test_mode' => $testMode
    ]);
} catch (Throwable $exception) {
    report($exception);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Welcome-email batch failed: ' . $exception->getMessage(),
        'trace' => explode("\n", $exception->getTraceAsString())
    ]);
}
