<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $driver = Schema::getConnection()->getDriverName();
            
            if ($driver === 'pgsql') {
                // For PostgreSQL, we need to drop the enum type and recreate as varchar
                DB::statement("ALTER TABLE event_requests ALTER COLUMN category TYPE VARCHAR(255)");
            } elseif ($driver === 'sqlite') {
                // SQLite doesn't support ALTER COLUMN, but it stores everything as text anyway
                // So we can skip this migration for SQLite
                return;
            } else {
                // For MySQL/MariaDB
                Schema::table('event_requests', function (Blueprint $table) {
                    $table->string('category', 255)->change();
                });
            }
        } catch (\Exception $e) {
            // If the migration fails (e.g., column already correct type), just continue
            // This handles cases where the migration was partially applied
            \Log::info('Category column type change skipped: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'pgsql') {
            // Recreate the enum type
            DB::statement("ALTER TABLE event_requests ALTER COLUMN category TYPE event_requests_category USING category::event_requests_category");
        }
        // For SQLite and MySQL, we don't need to do anything in down
    }
};
