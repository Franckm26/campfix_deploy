<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--compress : Compress the backup file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the PostgreSQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Starting database backup...');

            $filename = 'backup-' . now()->format('Y-m-d-His') . '.sql';
            $compressedFilename = $filename . '.gz';
            
            $host = config('database.connections.pgsql.host');
            $port = config('database.connections.pgsql.port');
            $database = config('database.connections.pgsql.database');
            $username = config('database.connections.pgsql.username');
            $password = config('database.connections.pgsql.password');

            // Create temporary backup directory
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $backupFile = $backupPath . '/' . $filename;

            // Set PGPASSWORD environment variable for authentication
            putenv("PGPASSWORD={$password}");

            // Use pg_dump to create backup
            $command = sprintf(
                'pg_dump --host=%s --port=%s --username=%s --dbname=%s --no-password --clean --if-exists > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($database),
                escapeshellarg($backupFile)
            );

            exec($command, $output, $returnVar);

            // Clear the password from environment
            putenv("PGPASSWORD");

            if ($returnVar !== 0) {
                $this->error('Backup failed: ' . implode("\n", $output));
                Log::error('Database backup failed', ['output' => $output]);
                return 1;
            }

            // Compress if option is set
            if ($this->option('compress')) {
                $this->info('Compressing backup...');
                $compressedFile = $backupPath . '/' . $compressedFilename;
                
                if (function_exists('gzencode')) {
                    $content = file_get_contents($backupFile);
                    file_put_contents($compressedFile, gzencode($content, 9));
                    unlink($backupFile);
                    $finalFile = $compressedFilename;
                } else {
                    $this->warn('gzip compression not available, keeping uncompressed backup');
                    $finalFile = $filename;
                }
            } else {
                $finalFile = $filename;
            }

            // Upload to Supabase Storage (optional)
            if (config('services.supabase.url')) {
                $this->uploadToSupabase($backupPath . '/' . $finalFile, $finalFile);
            }

            // Clean up old backups (keep last 96 backups = 1 day if running every 15 minutes)
            // Adjust this based on your backup frequency:
            // Every 5 min: 288/day, Every 15 min: 96/day, Every 30 min: 48/day, Hourly: 24/day
            $this->cleanOldBackups($backupPath, 96);

            $fileSize = $this->formatBytes(filesize($backupPath . '/' . $finalFile));
            $this->info("Backup completed successfully: {$finalFile} ({$fileSize})");
            Log::info('Database backup completed', ['filename' => $finalFile, 'size' => $fileSize]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Backup error: ' . $e->getMessage());
            Log::error('Database backup error', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Upload backup to Supabase Storage
     */
    private function uploadToSupabase($filePath, $filename)
    {
        try {
            $this->info('Uploading backup to Supabase...');
            
            $supabaseUrl = config('services.supabase.url');
            $supabaseKey = config('services.supabase.key');
            $bucket = config('services.supabase.bucket', 'backups');

            $url = "https://{$supabaseUrl}/storage/v1/object/{$bucket}/database-backups/{$filename}";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $supabaseKey,
                'Content-Type: application/octet-stream',
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filePath));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $this->info('Backup uploaded to Supabase successfully');
            } else {
                $this->warn('Failed to upload to Supabase: ' . $response);
            }

        } catch (\Exception $e) {
            $this->warn('Supabase upload error: ' . $e->getMessage());
        }
    }

    /**
     * Clean up old backup files
     */
    private function cleanOldBackups($directory, $keep = 48)
    {
        $files = glob($directory . '/backup-*.{sql,sql.gz}', GLOB_BRACE);
        
        if (count($files) > $keep) {
            // Sort by modification time
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Delete oldest files
            $filesToDelete = array_slice($files, 0, count($files) - $keep);
            foreach ($filesToDelete as $file) {
                unlink($file);
                $this->info('Deleted old backup: ' . basename($file));
            }
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
