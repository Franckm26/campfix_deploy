<?php

/**
 * Cleanup Old Backups from Supabase Storage
 * 
 * This script deletes old backup files from Supabase Storage
 * to prevent storage quota issues on free tier
 * 
 * Recommendation: Keep only last 7 backups (1 week)
 */

header('Content-Type: application/json');

// Security check
$cronSecret = getenv('CRON_SECRET');
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

if (!$cronSecret || $authHeader !== 'Bearer ' . $cronSecret) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized - Invalid or missing CRON_SECRET'
    ]);
    exit;
}

try {
    // Configuration
    // Default: 168 backups = 7 days for hourly backups
    $keepCount = (int)(getenv('BACKUP_RETENTION_COUNT') ?: 168);
    
    // Get Supabase credentials
    $supabaseUrl = getenv('SUPABASE_URL');
    $supabaseKey = getenv('SUPABASE_KEY');
    $bucket = getenv('SUPABASE_BACKUP_BUCKET') ?: 'backups';
    
    if (!$supabaseUrl || !$supabaseKey) {
        throw new Exception('SUPABASE_URL or SUPABASE_KEY not configured. Check environment variables.');
    }
    
    // Clean URL
    $supabaseUrl = str_replace(['https://', 'http://'], '', $supabaseUrl);
    
    // List all backup files
    $listUrl = "https://{$supabaseUrl}/storage/v1/object/list/{$bucket}";
    
    // Add prefix as POST body for Supabase Storage API
    $postData = json_encode([
        'prefix' => 'database-backups/',
        'limit' => 1000,
        'offset' => 0
    ]);
    
    $ch = curl_init($listUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabaseKey,
        'apikey: ' . $supabaseKey,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        // If bucket doesn't exist or no files, return gracefully
        if ($httpCode === 404 || $httpCode === 400) {
            echo json_encode([
                'success' => true,
                'message' => 'No backups found or backup bucket not created yet',
                'total_backups' => 0,
                'deleted_backups' => 0,
                'space_freed' => '0 B',
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_PRETTY_PRINT);
            exit;
        }
        
        throw new Exception("Failed to list backups: HTTP {$httpCode} - {$response}" . ($curlError ? " | cURL: {$curlError}" : ""));
    }
    
    $files = json_decode($response, true);
    
    if (!is_array($files)) {
        throw new Exception('Invalid response from Supabase Storage');
    }
    
    // Filter and sort backup files by creation date (newest first)
    $backups = array_filter($files, function($file) {
        return isset($file['name']) && 
               (strpos($file['name'], '.json') !== false || 
                strpos($file['name'], '.json.gz') !== false);
    });
    
    usort($backups, function($a, $b) {
        $timeA = strtotime($a['created_at'] ?? $a['updated_at'] ?? 0);
        $timeB = strtotime($b['created_at'] ?? $b['updated_at'] ?? 0);
        return $timeB - $timeA; // Newest first
    });
    
    $totalBackups = count($backups);
    $deletedCount = 0;
    $deletedSize = 0;
    $deletedFiles = [];
    
    // Delete old backups (keep only the most recent ones)
    if ($totalBackups > $keepCount) {
        $backupsToDelete = array_slice($backups, $keepCount);
        
        foreach ($backupsToDelete as $backup) {
            $fileName = $backup['name'];
            $fileSize = $backup['metadata']['size'] ?? 0;
            
            // Delete file from Supabase Storage
            // Remove 'database-backups/' prefix if it exists in the name
            $cleanFileName = str_replace('database-backups/', '', $fileName);
            $deleteUrl = "https://{$supabaseUrl}/storage/v1/object/{$bucket}/database-backups/{$cleanFileName}";
            
            $ch = curl_init($deleteUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $supabaseKey,
                'apikey: ' . $supabaseKey,
            ]);
            
            $deleteResponse = curl_exec($ch);
            $deleteHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($deleteHttpCode >= 200 && $deleteHttpCode < 300) {
                $deletedCount++;
                $deletedSize += $fileSize;
                $deletedFiles[] = $fileName;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Backup cleanup completed',
        'total_backups' => $totalBackups,
        'kept_backups' => min($totalBackups, $keepCount),
        'deleted_backups' => $deletedCount,
        'space_freed' => formatBytes($deletedSize),
        'space_freed_bytes' => $deletedSize,
        'deleted_files' => $deletedFiles,
        'retention_policy' => "Keep last {$keepCount} backups",
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}
