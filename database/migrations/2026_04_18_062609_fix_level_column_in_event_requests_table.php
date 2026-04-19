<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * The 'level' column in event_requests is a PostgreSQL enum that doesn't
     * include 'shs'. We need to convert it to a plain VARCHAR to accept all values.
     */
    public function up(): void
    {
        try {
            $driver = Schema::getConnection()->getDriverName();
            
            if ($driver === 'pgsql') {
                // For PostgreSQL: alter the enum column to VARCHAR
                DB::statement("ALTER TABLE event_requests ALTER COLUMN level TYPE VARCHAR(50)");
            } elseif ($driver === 'sqlite') {
                // SQLite doesn't support ALTER COLUMN, skip
                // SQLite stores everything as text anyway
            } else {
                // For MySQL/MariaDB
                Schema::table('event_requests', function (Blueprint $table) {
                    $table->string('level', 50)->change();
                });
            }

            // Also ensure education_level column exists and is VARCHAR
            if (!Schema::hasColumn('event_requests', 'education_level')) {
                Schema::table('event_requests', function (Blueprint $table) {
                    $table->string('education_level', 50)->default('tertiary')->after('level');
                });
            }
        } catch (\Exception $e) {
            // If the migration fails, log and continue
            \Log::info('Level column type change skipped: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            $driver = Schema::getConnection()->getDriverName();
            
            if ($driver === 'pgsql') {
                // Convert back to enum (only original values)
                DB::statement("ALTER TABLE event_requests ALTER COLUMN level TYPE event_requests_level USING level::event_requests_level");
            }
        } catch (\Exception $e) {
            \Log::info('Level column rollback skipped: ' . $e->getMessage());
        }
    }
};
