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
            $table->boolean('student_archived')->default(false)->after('is_archived');
            $table->boolean('faculty_archived')->default(false)->after('student_archived');
            $table->boolean('building_admin_archived')->default(false)->after('faculty_archived');
            $table->boolean('school_admin_archived')->default(false)->after('building_admin_archived');
            $table->boolean('academic_head_archived')->default(false)->after('school_admin_archived');
            $table->boolean('program_head_archived')->default(false)->after('academic_head_archived');
            $table->boolean('mis_archived')->default(false)->after('program_head_archived');
            $table->boolean('maintenance_archived')->default(false)->after('mis_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->dropColumn([
                'student_archived',
                'faculty_archived',
                'building_admin_archived',
                'school_admin_archived',
                'academic_head_archived',
                'program_head_archived',
                'mis_archived',
                'maintenance_archived',
            ]);
        });
    }
};
