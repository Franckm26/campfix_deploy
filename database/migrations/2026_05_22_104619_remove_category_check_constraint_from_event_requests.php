<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            // Drop the check constraint if it exists
            DB::statement("ALTER TABLE event_requests DROP CONSTRAINT IF EXISTS event_requests_category_check");
            
            // Also ensure the column is VARCHAR and not an enum type
            DB::statement("ALTER TABLE event_requests ALTER COLUMN category TYPE VARCHAR(255)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to recreate the constraint as it limits flexibility
        // Leave as VARCHAR without constraints
    }
};
