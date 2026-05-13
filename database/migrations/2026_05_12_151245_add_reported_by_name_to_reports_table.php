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
        Schema::table('reports', function (Blueprint $table) {
            $table->string('reported_by_name')->nullable()->after('user_id');
        });
        
        // Populate existing records with current user names
        DB::statement("
            UPDATE reports 
            SET reported_by_name = (
                SELECT name FROM users WHERE users.id = reports.user_id
            )
            WHERE reported_by_name IS NULL AND user_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('reported_by_name');
        });
    }
};
