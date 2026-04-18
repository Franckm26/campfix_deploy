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
        // For PostgreSQL: alter the enum column to VARCHAR
        DB::statement("ALTER TABLE event_requests ALTER COLUMN level TYPE VARCHAR(50)");

        // Also ensure education_level column exists and is VARCHAR
        if (!Schema::hasColumn('event_requests', 'education_level')) {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->string('education_level', 50)->default('tertiary')->after('level');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to enum (only original values)
        DB::statement("ALTER TABLE event_requests ALTER COLUMN level TYPE event_requests_level USING level::event_requests_level");
    }
};
