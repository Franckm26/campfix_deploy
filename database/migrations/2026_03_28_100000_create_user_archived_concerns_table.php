<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This table tracks which user has archived which concern.
     * Each user can have their own archive view.
     */
    public function up(): void
    {
        Schema::create('user_archived_concerns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('concern_id')->constrained()->onDelete('cascade');
            $table->timestamp('archived_at')->useCurrent();
            $table->string('archive_folder_name')->nullable()->default('My Archive');
            $table->timestamps();

            // Unique constraint - a user can only archive a concern once
            $table->unique(['user_id', 'concern_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_archived_concerns');
    }
};
