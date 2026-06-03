<?php

/**
 * Backup Status Endpoint
 * 
 * Provides information about the latest database backups
 * No authentication required - only shows metadata, not backup content
 */

header('Content-Type: application/json');

try {
    // Bootstrap Laravel
    require __DIR__ . '/../../vendor/autoload.php';
    $app = require_once __DIR__ . '/../../bootstrap/app.php';
    
    // Get backup directory
    $backupPath = __DIR__ . '/../../storage/app/backups';
    
    if (!file_exists($backupPath)) {
        http_response_code(200);
        echo json_encode([
            'status' => 'no_backups',
            'message' => 'No backups found - backup system may not be configured',
            'total_backups' => 0,
            'last_backup' => null
        ]);
        exit;
    }
    
    // Get all backup files
    $backups = glob($backupPath . '/backup-*.{sql,sql.gz}', GLOB_BRACE);
    
    if (empty($backups)) {
        http_response_code(200);
        echo json_encode([
            'status' => 'no_backups',
            'message' => 'Backup directory exists but no backups found yet',
            'total_backups' => 0,
            'last_backup' => null
        ]);
        exit;
    }
    
    // Sort by modification time (newest first)
    usort($backups, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    // Get details of latest backups
    $recentBackups = array_slice($backups, 0, 10);
    $backupInfo = [];
    
    foreach ($recentBackups as $backup) {
        $backupInfo[] = [
            'filename' => basename($backup),
            'size' => filesize($backup),
            'size_formatted' => formatBytes(filesize($backup)),
            'created_at' => date('Y-m-d H:i:s', filemtime($backup)),
            'age_minutes' => round((time() - filemtime($backup)) / 60, 1)
        ];
    }
    
    // Check if backups are recent (last backup within 10 minutes)
    $lastBackupTime = filemtime($backups[0]);
    $minutesSinceLastBackup = round((time() - $lastBackupTime) / 60, 1);
    $isHealthy = $minutesSinceLastBackup <= 10; // Should have backup within last 10 minutes if running every 5 min
    
    // Calculate total storage used
    $totalSize = array_sum(array_map('filesize', $backups));
    
    http_response_code(200);
    echo json_encode([
        'status' => $isHealthy ? 'healthy' : 'warning',
        'message' => $isHealthy 
            ? 'Backup system is running normally' 
            : "Last backup was {$minutesSinceLastBackup} minutes ago (expected within 10 minutes)",
        'total_backups' => count($backups),
        'total_storage' => $totalSize,
        'total_storage_formatted' => formatBytes($totalSize),
        'last_backup_age_minutes' => $minutesSinceLastBackup,
        'recent_backups' => $backupInfo,
        'checked_at' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to check backup status',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
