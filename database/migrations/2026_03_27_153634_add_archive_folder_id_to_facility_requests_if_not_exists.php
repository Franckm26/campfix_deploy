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
        Schema::table('facility_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('facility_requests', 'archive_folder_id')) {
                $table->foreignId('archive_folder_id')->nullable()->constrained('archive_folders')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            if (Schema::hasColumn('facility_requests', 'archive_folder_id')) {
                $table->dropForeign(['archive_folder_id']);
                $table->dropColumn('archive_folder_id');
            }
        });
    }
};
