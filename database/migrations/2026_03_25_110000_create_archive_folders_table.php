<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('archive_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('type', ['concerns', 'reports', 'facilities', 'mixed'])->default('mixed');
            $table->integer('item_count')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Add archive_folder_id to concerns table
        Schema::table('concerns', function (Blueprint $table) {
            $table->foreignId('archive_folder_id')->nullable()->constrained('archive_folders')->onDelete('set null');
        });

        // Add archive_folder_id to reports table
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('archive_folder_id')->nullable()->constrained('archive_folders')->onDelete('set null');
        });

        // Add archive_folder_id to facility_requests table
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->foreignId('archive_folder_id')->nullable()->constrained('archive_folders')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('concerns', function (Blueprint $table) {
            $table->dropForeign(['archive_folder_id']);
            $table->dropColumn('archive_folder_id');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['archive_folder_id']);
            $table->dropColumn('archive_folder_id');
        });

        Schema::table('facility_requests', function (Blueprint $table) {
            $table->dropForeign(['archive_folder_id']);
            $table->dropColumn('archive_folder_id');
        });

        Schema::dropIfExists('archive_folders');
    }
};
