<?php

/**
 * Supabase-Native Backup Endpoint (v1.1)
 * 
 * This uses Supabase's native backup features instead of pg_dump
 * Works on Vercel serverless without PostgreSQL client tools
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
    // Get Supabase credentials
    $supabaseUrl = getenv('SUPABASE_URL');
    $supabaseKey = getenv('SUPABASE_KEY');
    
    if (!$supabaseUrl) {
        throw new Exception('SUPABASE_URL not configured in environment variables');
    }
    
    if (!$supabaseKey) {
        throw new Exception('SUPABASE_KEY not configured in environment variables');
    }
    
    // List of tables to backup (add your tables here)
    // For storage optimization: backup only essential data
    // Exclude temporary/regenerable data like sessions and notifications
    $tables = [
        'users',
        'concerns',
        'event_requests',
        // 'notifications', // Excluded - can be regenerated
        // 'sessions',      // Excluded - temporary data
        // Add more tables as needed
    ];
    
    $backupData = [];
    $totalRecords = 0;
    
    // Fetch data from each table
    foreach ($tables as $table) {
        $url = "https://{$supabaseUrl}/rest/v1/{$table}?select=*";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $backupData[$table] = $data;
            $totalRecords += count($data);
        } else {
            $backupData[$table] = ['error' => 'Failed to fetch', 'http_code' => $httpCode];
        }
    }
    
    // Create backup filename
    $filename = 'supabase-backup-' . date('Y-m-d-His') . '.json';
    
    // Compress backup content to save storage space
    // This can reduce file size by 70-90%
    $backupJson = json_encode($backupData);
    $backupContent = gzencode($backupJson, 9); // Max compression
    $filename = str_replace('.json', '.json.gz', $filename);
    
    // Upload to Supabase Storage
    $bucket = getenv('SUPABASE_BACKUP_BUCKET') ?: 'backups';
    $uploadUrl = "https://{$supabaseUrl}/storage/v1/object/{$bucket}/database-backups/{$filename}";
    
    $ch = curl_init($uploadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabaseKey,
        'Content-Type: application/gzip',
        'Content-Encoding: gzip',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $backupContent);
    
    $uploadResponse = curl_exec($ch);
    $uploadHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $success = $uploadHttpCode >= 200 && $uploadHttpCode < 300;
    
    http_response_code($success ? 200 : 500);
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Backup completed successfully' : 'Backup upload failed',
        'filename' => $filename,
        'tables_backed_up' => count($tables),
        'total_records' => $totalRecords,
        'size_bytes' => strlen($backupContent),
        'size_formatted' => formatBytes(strlen($backupContent)),
        'uncompressed_size' => formatBytes(strlen($backupJson)),
        'compression_ratio' => round((1 - strlen($backupContent) / strlen($backupJson)) * 100, 1) . '%',
        'timestamp' => date('Y-m-d H:i:s'),
        'upload_status' => $uploadHttpCode
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'error_line' => $e->getLine(),
        'error_file' => basename($e->getFile()),
        'timestamp' => date('Y-m-d H:i:s'),
        'env_check' => [
            'supabase_url_set' => !empty(getenv('SUPABASE_URL')),
            'supabase_key_set' => !empty(getenv('SUPABASE_KEY')),
            'supabase_bucket_set' => !empty(getenv('SUPABASE_BUCKET')),
        ]
    ], JSON_PRETTY_PRINT);
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}
