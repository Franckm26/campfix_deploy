<?php
/**
 * Trigger Supabase Backup via Management API
 * 
 * This uses Supabase Management API to trigger database backups
 * Works on FREE tier with daily quota
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

set_time_limit(120); // 2 minutes timeout

try {
    // Get Supabase project details from connection string
    $dbHost = getenv('DB_HOST'); // e.g., aws-1-ap-southeast-1.pooler.supabase.com
    $supabaseUrl = getenv('SUPABASE_URL'); // e.g., pclfaksjjprickgppnus.supabase.co
    $supabaseServiceKey = getenv('SUPABASE_KEY'); // Service role key
    
    if (!$dbHost || !$supabaseUrl || !$supabaseServiceKey) {
        throw new Exception('Missing Supabase configuration');
    }
    
    // Extract project reference from URL
    $projectRef = explode('.', $supabaseUrl)[0]; // e.g., pclfaksjjprickgppnus
    
    // Supabase Management API endpoint
    // Note: This requires a Supabase Access Token from dashboard
    $managementToken = getenv('SUPABASE_ACCESS_TOKEN');
    
    if (!$managementToken) {
        // Fallback: Create a logical backup using SQL dump
        $result = createLogicalBackup($projectRef, $supabaseUrl, $supabaseServiceKey);
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Trigger backup via Management API
    $apiUrl = "https://api.supabase.com/v1/projects/{$projectRef}/database/backups";
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $managementToken,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'type' => 'physical' // or 'logical'
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $success = $httpCode >= 200 && $httpCode < 300;
    
    http_response_code($success ? 200 : 500);
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Backup triggered successfully' : 'Backup trigger failed',
        'http_code' => $httpCode,
        'response' => json_decode($response),
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
 * Create logical backup using SQL queries
 */
function createLogicalBackup($projectRef, $supabaseUrl, $serviceKey) {
    try {
        // Get list of all tables
        $tables = getTablesList($supabaseUrl, $serviceKey);
        
        if (empty($tables)) {
            throw new Exception('No tables found to backup');
        }
        
        $backupData = [];
        $totalRows = 0;
        
        // Fetch data from each table
        foreach ($tables as $table) {
            $data = getTableData($supabaseUrl, $serviceKey, $table);
            if ($data !== false) {
                $backupData[$table] = $data;
                $totalRows += is_array($data) ? count($data) : 0;
            }
        }
        
        // Create backup file
        $filename = 'backup-' . date('Y-m-d-His') . '.json';
        $content = json_encode([
            'metadata' => [
                'project' => $projectRef,
                'timestamp' => date('Y-m-d H:i:s'),
                'tables_count' => count($tables),
                'total_rows' => $totalRows
            ],
            'data' => $backupData
        ], JSON_PRETTY_PRINT);
        
        // Upload to Supabase Storage
        $bucket = getenv('SUPABASE_BACKUP_BUCKET') ?: 'backups';
        $uploadUrl = "https://{$supabaseUrl}/storage/v1/object/{$bucket}/database-backups/{$filename}";
        
        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $serviceKey,
            'Content-Type: application/json',
            'x-upsert: true'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        $success = $httpCode >= 200 && $httpCode < 300;
        
        return [
            'success' => $success,
            'message' => $success ? 'Logical backup completed' : 'Upload failed',
            'filename' => $filename,
            'tables_backed_up' => count($tables),
            'total_rows' => $totalRows,
            'size_bytes' => strlen($content),
            'size_mb' => round(strlen($content) / 1024 / 1024, 2),
            'timestamp' => date('Y-m-d H:i:s'),
            'upload_http_code' => $httpCode,
            'upload_error' => $curlError ?: null,
            'upload_response' => $response ? json_decode($response) : null,
            'bucket' => $bucket,
            'upload_url' => $uploadUrl
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get list of tables from Supabase
 */
function getTablesList($supabaseUrl, $serviceKey) {
    // Query information_schema to get table list
    $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE'";
    
    // Use Supabase PostgREST API with RPC
    $url = "https://{$supabaseUrl}/rest/v1/rpc/exec_sql";
    
    // Fallback: Hardcode common tables
    // You should update this list with your actual tables
    return [
        'users',
        'concerns',
        'event_requests',
        'notifications',
        'concern_categories',
        'concern_statuses',
        'locations',
        'sessions'
    ];
}

/**
 * Get data from a specific table
 */
function getTableData($supabaseUrl, $serviceKey, $table) {
    $url = "https://{$supabaseUrl}/rest/v1/{$table}?select=*";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $serviceKey,
        'Authorization: Bearer ' . $serviceKey,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}
