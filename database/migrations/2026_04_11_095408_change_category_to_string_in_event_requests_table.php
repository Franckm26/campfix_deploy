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
        // For PostgreSQL, we need to drop the enum type and recreate as varchar
        DB::statement("ALTER TABLE event_requests ALTER COLUMN category TYPE VARCHAR(255)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the enum type
        DB::statement("ALTER TABLE event_requests ALTER COLUMN category TYPE event_requests_category USING category::event_requests_category");
    }
};
