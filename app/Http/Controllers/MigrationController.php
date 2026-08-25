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

            // Run the migration
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return response()->json([
                'status' => 'success',
                'message' => 'Migration completed successfully',
                'output' => $output,
                'column_exists' => Schema::hasColumn('users', 'password_reset_at')
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