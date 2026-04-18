<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This table tracks which user has archived which report.
     * Each user can have their own archive view.
     */
    public function up(): void
    {
        Schema::create('user_archived_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->timestamp('archived_at')->useCurrent();
            $table->string('archive_folder_name')->nullable()->default('My Archive');
            $table->timestamps();

            // Unique constraint - a user can only archive a report once
            $table->unique(['user_id', 'report_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_archived_reports');
    }
};
