<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_archive_folders', function (Blueprint $table) {
            if (!Schema::hasColumn('user_archive_folders', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('user_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_archive_folders', function (Blueprint $table) {
            if (Schema::hasColumn('user_archive_folders', 'is_system')) {
                $table->dropColumn('is_system');
            }
        });
    }
};
