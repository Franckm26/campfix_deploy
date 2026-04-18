<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            // Add is_deleted column if it doesn't exist
            if (! Schema::hasColumn('concerns', 'is_deleted')) {
                $table->boolean('is_deleted')->default(false)->after('is_archived');
            }

            // Add archived_at column if it doesn't exist
            if (! Schema::hasColumn('concerns', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archived');
            }

            // Add archived_by column if it doesn't exist
            if (! Schema::hasColumn('concerns', 'archived_by')) {
                $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');
            }

            // Add deleted_by column if it doesn't exist
            if (! Schema::hasColumn('concerns', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('is_deleted');
            }

            // Add deleted_at column if it doesn't exist
            if (! Schema::hasColumn('concerns', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('deleted_by');
            }

            // Add foreign key constraints after all columns exist
            if (Schema::hasColumn('concerns', 'archived_by') && ! Schema::hasForeignConstraint('concerns', 'archived_by')) {
                $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
            }
            if (Schema::hasColumn('concerns', 'deleted_by') && ! Schema::hasForeignConstraint('concerns', 'deleted_by')) {
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (Schema::hasColumn('concerns', 'deleted_by')) {
                try {
                    $table->dropForeign(['deleted_by']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
            }
            if (Schema::hasColumn('concerns', 'archived_by')) {
                try {
                    $table->dropForeign(['archived_by']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
            }
            if (Schema::hasColumn('concerns', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
            if (Schema::hasColumn('concerns', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
            if (Schema::hasColumn('concerns', 'archived_by')) {
                $table->dropColumn('archived_by');
            }
            if (Schema::hasColumn('concerns', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }
            if (Schema::hasColumn('concerns', 'is_deleted')) {
                $table->dropColumn('is_deleted');
            }
        });
    }
};
