<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_archive_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Folder name like "SY2025-2026"
            $table->string('description')->nullable();
            $table->integer('user_count')->default(0); // Number of users in this folder
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_archive_folders');
    }
};
