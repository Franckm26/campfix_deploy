<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'archive_folder_id')) {
                $table->foreignId('archive_folder_id')->nullable()->constrained('user_archive_folders')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'archive_folder_id')) {
                $table->dropForeign(['archive_folder_id']);
                $table->dropColumn('archive_folder_id');
            }
        });
    }
};
