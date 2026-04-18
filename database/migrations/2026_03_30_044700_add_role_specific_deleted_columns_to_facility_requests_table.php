<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->boolean('student_deleted')->default(false)->after('delete_after_days');
            $table->boolean('faculty_deleted')->default(false)->after('student_deleted');
            $table->boolean('building_admin_deleted')->default(false)->after('faculty_deleted');
            $table->boolean('school_admin_deleted')->default(false)->after('building_admin_deleted');
            $table->boolean('academic_head_deleted')->default(false)->after('school_admin_deleted');
            $table->boolean('program_head_deleted')->default(false)->after('academic_head_deleted');
            $table->boolean('mis_deleted')->default(false)->after('program_head_deleted');
            $table->boolean('maintenance_deleted')->default(false)->after('mis_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->dropColumn([
                'student_deleted',
                'faculty_deleted',
                'building_admin_deleted',
                'school_admin_deleted',
                'academic_head_deleted',
                'program_head_deleted',
                'mis_deleted',
                'maintenance_deleted',
            ]);
        });
    }
};
