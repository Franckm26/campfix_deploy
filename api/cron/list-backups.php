<?php
/**
 * List Available Backups
 * 
 * Lists all backup files stored in Supabase Storage
 */

header('Content-Type: application/json');

// Security check
$cronSecret = getenv('CRON_SECRET');
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

if (!$cronSecret || $authHeader !== 'Bearer ' . $cronSecret) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // Get Supabase credentials
    $supabaseUrl = getenv('SUPABASE_URL');
    $supabaseServiceKey = getenv('SUPABASE_KEY');
    $bucket = getenv('SUPABASE_BACKUP_BUCKET') ?: 'backups';
    
    if (!$supabaseUrl) {
        throw new Exception('SUPABASE_URL not configured');
    }
    
    if (!$supabaseServiceKey) {
        throw new Exception('SUPABASE_KEY not configured');
    }
    
    // Remove protocol if exists
    $supabaseUrl = str_replace(['https://', 'http://'], '', $supabaseUrl);
    
    // List files in backup folder
    $listUrl = "https://{$supabaseUrl}/storage/v1/object/list/{$bucket}";
    
    // Add query parameters for prefix
    $params = http_build_query(['prefix' => 'database-backups/', 'limit' => 100, 'offset' => 0]);
    $listUrl .= '?' . $params;
    
    $ch = curl_init($listUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabaseServiceKey,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to list backups. HTTP Code: ' . $httpCode . '. Response: ' . $response);
    }
    
    $files = json_decode($response, true);
    
    if (!is_array($files)) {
        throw new Exception('Invalid response from storage');
    }
    
    // Format backup list
    $backups = [];
    foreach ($files as $file) {
        if (isset($file['name']) && strpos($file['name'], '.json') !== false) {
            $backups[] = [
                'filename' => $file['name'],
                'size' => $file['metadata']['size'] ?? 0,
                'size_mb' => round(($file['metadata']['size'] ?? 0) / 1024 / 1024, 2),
                'created_at' => $file['created_at'] ?? $file['updated_at'] ?? 'unknown',
                'restore_url' => "https://www.campfixsti.com/api/cron/restore-backup?filename=" . urlencode($file['name'])
            ];
        }
    }
    
    // Sort by created date (newest first)
    usort($backups, function($a, $b) {
        return strcmp($b['created_at'], $a['created_at']);
    });
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'total_backups' => count($backups),
        'backups' => $backups,
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
