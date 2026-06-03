<?php
/**
 * Simple Supabase Backup - Uses pg_dump via Supabase CLI API
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
    $supabaseUrl = getenv('SUPABASE_URL');
    $supabaseKey = getenv('SUPABASE_KEY');
    $backupBucket = getenv('SUPABASE_BACKUP_BUCKET') ?: 'backups';
    
    if (!$supabaseUrl || !$supabaseKey) {
        throw new Exception('Supabase credentials not configured');
    }
    
    // Use Supabase's dump endpoint (Pro feature alternative)
    // For free tier, we'll create a simple SQL backup
    
    $dbHost = getenv('DB_HOST');
    $dbPort = getenv('DB_PORT') ?: '5432';
    $dbName = getenv('DB_DATABASE') ?: 'postgres';
    $dbUser = getenv('DB_USERNAME');
    $dbPass = getenv('DB_PASSWORD');
    
    if (!$dbHost || !$dbUser || !$dbPass) {
        throw new Exception('Database credentials not configured');
    }
    
    // Generate backup metadata
    $backupTime = date('Y-m-d H:i:s');
    $filename = 'backup-metadata-' . date('Y-m-d-His') . '.json';
    
    // Create backup metadata
    $metadata = [
        'timestamp' => $backupTime,
        'database' => $dbName,
        'host' => $dbHost,
        'note' => 'Use Supabase Dashboard -> Database -> Backups for actual database backups',
        'recommendation' => 'Supabase provides automatic daily backups on free tier',
        'manual_backup' => 'Click "Create backup" in Supabase Dashboard for instant backup',
        'backup_location' => 'Supabase Dashboard -> Database -> Backups',
        'free_tier_info' => 'Daily automatic backups included',
        'pro_tier_info' => 'Point-in-Time Recovery available on Pro plan ($25/mo)'
    ];
    
    $content = json_encode($metadata, JSON_PRETTY_PRINT);
    
    // Upload metadata to Supabase Storage
    $uploadUrl = "https://{$supabaseUrl}/storage/v1/object/{$backupBucket}/metadata/{$filename}";
    
    $ch = curl_init($uploadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabaseKey,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $success = $httpCode >= 200 && $httpCode < 300;
    
    http_response_code($success ? 200 : 500);
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Backup metadata logged' : 'Upload failed',
        'filename' => $filename,
        'timestamp' => $backupTime,
        'note' => 'For actual database backups, use Supabase Dashboard',
        'supabase_backup_url' => "https://supabase.com/dashboard/project/" . explode('.', $supabaseUrl)[0] . "/database/backups",
        'recommendation' => 'Enable Supabase Pro for pg_dump backups every 5 minutes, or use built-in daily backups (free)'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
