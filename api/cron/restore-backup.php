<?php
/**
 * Restore Database from Backup
 * 
 * This restores data from a JSON backup file stored in Supabase Storage
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

set_time_limit(300); // 5 minutes timeout

try {
    // Get backup filename from request
    $filename = $_GET['filename'] ?? $_POST['filename'] ?? null;
    
    if (!$filename) {
        throw new Exception('Backup filename is required. Use ?filename=backup-2026-06-03-120240.json');
    }
    
    // Get Supabase credentials
    $supabaseUrl = getenv('SUPABASE_URL');
    $supabaseServiceKey = getenv('SUPABASE_KEY');
    $bucket = getenv('SUPABASE_BACKUP_BUCKET') ?: 'backups';
    
    if (!$supabaseUrl || !$supabaseServiceKey) {
        throw new Exception('Supabase credentials not configured');
    }
    
    // Remove protocol if exists
    $supabaseUrl = str_replace(['https://', 'http://'], '', $supabaseUrl);
    
    // Download backup file from Supabase Storage
    $downloadUrl = "https://{$supabaseUrl}/storage/v1/object/{$bucket}/database-backups/{$filename}";
    
    $ch = curl_init($downloadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabaseServiceKey,
    ]);
    
    $backupContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to download backup file. HTTP Code: ' . $httpCode);
    }
    
    // Parse backup JSON
    $backup = json_decode($backupContent, true);
    
    if (!$backup || !isset($backup['data'])) {
        throw new Exception('Invalid backup file format');
    }
    
    $restoredTables = [];
    $restoredRows = 0;
    $errors = [];
    
    // Restore each table
    foreach ($backup['data'] as $tableName => $rows) {
        if (empty($rows) || !is_array($rows)) {
            continue;
        }
        
        $tableRowsRestored = 0;
        $tableErrors = [];
        
        // Insert each row
        foreach ($rows as $row) {
            $result = restoreRow($supabaseUrl, $supabaseServiceKey, $tableName, $row);
            if ($result['success']) {
                $tableRowsRestored++;
            } else {
                $tableErrors[] = $result['error'];
            }
        }
        
        $restoredTables[] = [
            'table' => $tableName,
            'rows' => $tableRowsRestored,
            'errors' => count($tableErrors),
            'error_samples' => array_slice($tableErrors, 0, 3) // Show first 3 errors
        ];
        
        $restoredRows += $tableRowsRestored;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Restore completed',
        'filename' => $filename,
        'backup_timestamp' => $backup['metadata']['timestamp'] ?? 'unknown',
        'tables_restored' => count($restoredTables),
        'total_rows_restored' => $restoredRows,
        'details' => $restoredTables,
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

/**
 * Restore a single row to a table
 */
function restoreRow($supabaseUrl, $serviceKey, $table, $row) {
    $url = "https://{$supabaseUrl}/rest/v1/{$table}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $serviceKey,
        'Authorization: Bearer ' . $serviceKey,
        'Content-Type: application/json',
        'Prefer: resolution=merge-duplicates' // Update if exists
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($row));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => "HTTP {$httpCode}: {$response}"];
    }
}
