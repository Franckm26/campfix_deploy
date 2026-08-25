<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class MigrationController extends Controller
{
    public function runMigrations()
    {
        // Only allow MIS or superadmin
        if (!auth()->check() || !in_array(auth()->user()->role, ['superadmin', 'mis'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Check if password_reset_at column already exists
            $columnExists = Schema::hasColumn('users', 'password_reset_at');
            
            if ($columnExists) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Migration already completed. The password_reset_at column already exists.',
                    'column_exists' => true
                ]);
            }

            // Run only our specific migration
            $migrationFile = '2026_08_25_093621_add_password_reset_at_to_users_table';
            Artisan::call('migrate', [
                '--path' => 'database/migrations/' . $migrationFile . '.php',
                '--force' => true
            ]);
            $output = Artisan::output();

            // Verify it worked
            $columnExists = Schema::hasColumn('users', 'password_reset_at');

            return response()->json([
                'status' => 'success',
                'message' => $columnExists 
                    ? 'Migration completed successfully! Password reset tracking is now enabled.' 
                    : 'Migration ran but column not detected. Please refresh the page.',
                'output' => $output,
                'column_exists' => $columnExists
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Migration failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function checkMigrationStatus()
    {
        // Only allow MIS or superadmin
        if (!auth()->check() || !in_array(auth()->user()->role, ['superadmin', 'mis'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $columnExists = Schema::hasColumn('users', 'password_reset_at');
            
            return response()->json([
                'status' => 'success',
                'password_reset_column_exists' => $columnExists,
                'message' => $columnExists 
                    ? 'Migration is complete - password reset tracking is enabled' 
                    : 'Migration needed - password reset tracking is not yet enabled'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check migration status: ' . $e->getMessage()
            ], 500);
        }
    }
}