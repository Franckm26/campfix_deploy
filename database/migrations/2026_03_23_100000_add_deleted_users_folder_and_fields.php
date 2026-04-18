<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_system field to user_archive_folders table
        Schema::table('user_archive_folders', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('user_count');
        });

        // Add is_deleted field to users table (for soft delete)
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('is_archived');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('is_deleted');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });

        // Create the "Deleted Users" archive folder
        \App\Models\UserArchiveFolder::firstOrCreate(
            ['name' => 'Deleted Users'],
            [
                'description' => 'Users that have been deleted and can be restored',
                'user_count' => 0,
                'is_system' => true, // Mark as system folder that cannot be deleted
            ]
        );
    }

    public function down(): void
    {
        // Remove is_system field from user_archive_folders
        Schema::table('user_archive_folders', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['is_deleted', 'deleted_by']);
        });

        // Delete the Deleted Users folder
        \App\Models\UserArchiveFolder::where('name', 'Deleted Users')->delete();
    }
};
