<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (!Schema::hasColumn('concerns', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('concerns', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            }
            if (!Schema::hasColumn('concerns', 'priority')) {
                $table->string('priority')->default('medium')->after('status');
            }
            if (!Schema::hasColumn('concerns', 'resolution_notes')) {
                $table->text('resolution_notes')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('concerns', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('resolution_notes');
            }
            if (!Schema::hasColumn('concerns', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('category');
            }
            if (!Schema::hasColumn('concerns', 'is_deleted')) {
                $table->boolean('is_deleted')->default(false)->after('is_archived');
            }
            if (!Schema::hasColumn('concerns', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            }
            if (!Schema::hasColumn('concerns', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
            }
            if (!Schema::hasColumn('concerns', 'archived_at')) {
                $table->timestamp('archived_at')->nullable();
            }
            if (!Schema::hasColumn('concerns', 'archived_by')) {
                $table->unsignedBigInteger('archived_by')->nullable();
            }
            if (!Schema::hasColumn('concerns', 'archive_folder_id')) {
                $table->unsignedBigInteger('archive_folder_id')->nullable();
            }
            if (!Schema::hasColumn('concerns', 'admin_archived')) {
                $table->boolean('admin_archived')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'student_archived')) {
                $table->boolean('student_archived')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'faculty_archived')) {
                $table->boolean('faculty_archived')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'building_admin_archived')) {
                $table->boolean('building_admin_archived')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'school_admin_archived')) {
                $table->boolean('school_admin_archived')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'academic_head_archived')) {
                $table->boolean('academic_head_archived')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'program_head_archived')) {
                $table->boolean('program_head_archived')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'mis_archived')) {
                $table->boolean('mis_archived')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'maintenance_archived')) {
                $table->boolean('maintenance_archived')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'student_deleted')) {
                $table->boolean('student_deleted')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'faculty_deleted')) {
                $table->boolean('faculty_deleted')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'building_admin_deleted')) {
                $table->boolean('building_admin_deleted')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'school_admin_deleted')) {
                $table->boolean('school_admin_deleted')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'academic_head_deleted')) {
                $table->boolean('academic_head_deleted')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'program_head_deleted')) {
                $table->boolean('program_head_deleted')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'mis_deleted')) {
                $table->boolean('mis_deleted')->default(false);
            }
            if (!Schema::hasColumn('concerns', 'maintenance_deleted')) {
                $table->boolean('maintenance_deleted')->default(false);
            }
        });
    }

    public function down(): void
    {
        // No rollback needed
    }
};
