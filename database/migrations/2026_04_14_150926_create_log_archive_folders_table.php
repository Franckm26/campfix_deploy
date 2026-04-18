<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_archive_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('log_count')->default(0);
            $table->timestamps();
        });

        // Add folder FK to activity_logs
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('log_archive_folder_id')->nullable()->after('archived_by');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn('log_archive_folder_id');
        });
        Schema::dropIfExists('log_archive_folders');
    }
};
