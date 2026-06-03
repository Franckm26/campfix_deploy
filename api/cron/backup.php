<?php

/**
 * Vercel Cron Job Endpoint for Database Backup
 * 
 * This endpoint is called by Vercel Cron to trigger database backups
 * Configure in vercel.json with schedule: "* /5 * * * *" for every 5 minutes
 * 
 * Security: Uses CRON_SECRET environment variable for authentication
 */

// Security check - verify cron secret
$cronSecret = getenv('CRON_SECRET');
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

if (!$cronSecret || $authHeader !== 'Bearer ' . $cronSecret) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized - Invalid or missing CRON_SECRET'
    ]);
    exit;
}

// Set time limit for backup operation
set_time_limit(300); // 5 minutes

// Bootstrap Laravel
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';

// Get console kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Capture output
ob_start();

try {
    // Run backup command
    $exitCode = $kernel->call('db:backup', [
        '--compress' => true
    ]);
    
    $output = ob_get_clean();
    
    // Get last backup file info
    $backupPath = storage_path('app/backups');
    $backups = glob($backupPath . '/backup-*.{sql,sql.gz}', GLOB_BRACE);
    
    $lastBackup = null;
    if (!empty($backups)) {
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $lastBackup = [
            'filename' => basename($backups[0]),
            'size' => filesize($backups[0]),
            'created_at' => date('Y-m-d H:i:s', filemtime($backups[0]))
        ];
    }
    
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $exitCode === 0,
        'exit_code' => $exitCode,
        'message' => $exitCode === 0 ? 'Backup completed successfully' : 'Backup failed',
        'output' => $output,
        'last_backup' => $lastBackup,
        'timestamp' => date('Y-m-d H:i:s'),
        'total_backups' => count($backups)
    ]);
    
} catch (\Exception $e) {
    ob_end_clean();
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
