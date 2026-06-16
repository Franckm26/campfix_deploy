<?php

/**
 * Supabase Storage Usage Report
 * 
 * This script generates a detailed report of storage usage
 * across all buckets in Supabase Storage
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
    
    if (!$supabaseUrl || !$supabaseKey) {
        throw new Exception('SUPABASE_URL or SUPABASE_KEY not configured');
    }
    
    // Clean URL
    $supabaseUrl = str_replace(['https://', 'http://'], '', $supabaseUrl);
    
    // Buckets to check
    $buckets = [
        'backups' => 'database-backups/',
        'concerns' => 'concerns/',
        'profile_pictures' => 'profile_pictures/',
    ];
    
    $report = [];
    $totalSize = 0;
    $totalFiles = 0;
    
    foreach ($buckets as $bucket => $prefix) {
        // List files in bucket
        $listUrl = "https://{$supabaseUrl}/storage/v1/object/list/{$bucket}";
        if ($prefix) {
            $listUrl .= "?prefix=" . urlencode($prefix);
        }
        
        $ch = curl_init($listUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $supabaseKey,
            'apikey: ' . $supabaseKey,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $files = json_decode($response, true);
            
            if (is_array($files)) {
                $bucketSize = 0;
                $fileCount = count($files);
                
                foreach ($files as $file) {
                    $bucketSize += $file['metadata']['size'] ?? 0;
                }
                
                $report[$bucket] = [
                    'file_count' => $fileCount,
                    'total_size_bytes' => $bucketSize,
                    'total_size_formatted' => formatBytes($bucketSize),
                    'prefix' => $prefix,
                    'oldest_file' => isset($files[0]) ? ($files[0]['created_at'] ?? 'unknown') : null,
                    'newest_file' => isset($files[$fileCount - 1]) ? ($files[$fileCount - 1]['created_at'] ?? 'unknown') : null,
                ];
                
                $totalSize += $bucketSize;
                $totalFiles += $fileCount;
            } else {
                $report[$bucket] = [
                    'error' => 'Invalid response format',
                    'http_code' => $httpCode
                ];
            }
        } else {
            $report[$bucket] = [
                'error' => 'Failed to fetch',
                'http_code' => $httpCode,
                'response' => $response
            ];
        }
    }
    
    // Supabase Free Tier Limit: 1 GB
    $freeLimit = 1 * 1024 * 1024 * 1024; // 1 GB in bytes
    $usagePercent = ($totalSize / $freeLimit) * 100;
    
    echo json_encode([
        'success' => true,
        'storage_report' => $report,
        'summary' => [
            'total_files' => $totalFiles,
            'total_size_bytes' => $totalSize,
            'total_size_formatted' => formatBytes($totalSize),
            'free_tier_limit' => formatBytes($freeLimit),
            'usage_percent' => round($usagePercent, 2) . '%',
            'remaining_space' => formatBytes($freeLimit - $totalSize),
            'warning' => $usagePercent > 80 ? 'Storage usage above 80%! Consider cleanup.' : null
        ],
        'recommendations' => [
            'If backups bucket is large' => 'Run /api/cron/cleanup-old-backups to delete old backups',
            'If concerns bucket is large' => 'Consider implementing image compression or cleanup of deleted concern images',
            'If profile_pictures is large' => 'Consider implementing profile picture cleanup for deleted users'
        ],
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
