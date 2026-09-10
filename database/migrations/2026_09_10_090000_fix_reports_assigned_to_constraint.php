<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['assigned_to']);
            
            // Make assigned_to nullable and remove constraint
            // We'll handle the relationship in the application layer instead
            $table->unsignedBigInteger('assigned_to')->nullable()->change();
        });
        
        // Do the same for concerns table
        if (Schema::hasColumn('concerns', 'assigned_to')) {
            Schema::table('concerns', function (Blueprint $table) {
                // Check if foreign key exists before dropping
                try {
                    $table->dropForeign(['assigned_to']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                
                $table->unsignedBigInteger('assigned_to')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->constrained('users')->change();
        });
        
        if (Schema::hasColumn('concerns', 'assigned_to')) {
            Schema::table('concerns', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->constrained('users')->change();
            });
        }
    }
};
