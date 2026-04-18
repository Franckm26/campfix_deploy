<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add archive_folder_id to reports table if it doesn't exist
        if (! Schema::hasColumn('reports', 'archive_folder_id')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->foreignId('archive_folder_id')->nullable()->constrained('archive_folders')->onDelete('set null');
            });
        }

        // Add is_deleted and deleted_by fields to reports table if they don't exist
        if (! Schema::hasColumn('reports', 'is_deleted')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false)->after('archive_folder_id');
            });
        }
        if (! Schema::hasColumn('reports', 'deleted_by')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('is_deleted');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Add archive_folder_id to event_requests table if it doesn't exist
        if (! Schema::hasColumn('event_requests', 'archive_folder_id')) {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->foreignId('archive_folder_id')->nullable()->constrained('archive_folders')->onDelete('set null');
            });
        }

        // Add is_deleted and deleted_by fields to event_requests table if they don't exist
        if (! Schema::hasColumn('event_requests', 'is_deleted')) {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false)->after('archive_folder_id');
            });
        }
        if (! Schema::hasColumn('event_requests', 'deleted_by')) {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('is_deleted');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Create the "Deleted Reports" archive folder
        DB::table('archive_folders')->updateOrInsert(
            ['name' => 'Deleted Reports'],
            [
                'description' => 'Reports that have been deleted and can be restored',
                'type' => 'reports',
                'item_count' => 0,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create the "Deleted Events" archive folder
        DB::table('archive_folders')->updateOrInsert(
            ['name' => 'Deleted Events'],
            [
                'description' => 'Events that have been deleted and can be restored',
                'type' => 'mixed',
                'item_count' => 0,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        // Remove fields from reports table
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['is_deleted', 'deleted_by']);
            $table->dropForeign(['archive_folder_id']);
            $table->dropColumn(['archive_folder_id']);
        });

        // Remove fields from event_requests table
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['is_deleted', 'deleted_by']);
            $table->dropForeign(['archive_folder_id']);
            $table->dropColumn(['archive_folder_id']);
        });

        // Delete the deleted folders
        DB::table('archive_folders')->where('name', 'Deleted Reports')->delete();
        DB::table('archive_folders')->where('name', 'Deleted Events')->delete();
    }
};
