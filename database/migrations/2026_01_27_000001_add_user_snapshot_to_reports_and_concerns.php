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
        // Add user snapshot fields to reports table
        Schema::table('reports', function (Blueprint $table) {
            // Snapshot of user info at report creation time
            $table->string('reporter_email')->nullable()->after('reported_by_name');
            $table->string('reporter_role')->nullable()->after('reporter_email');
            $table->string('reporter_department')->nullable()->after('reporter_role');
            $table->string('reporter_phone')->nullable()->after('reporter_department');
            $table->string('reporter_student_id')->nullable()->after('reporter_phone');
        });

        // Add user snapshot fields to concerns table
        Schema::table('concerns', function (Blueprint $table) {
            // Snapshot of user info at concern creation time
            $table->string('reporter_name')->nullable()->after('user_id');
            $table->string('reporter_email')->nullable()->after('reporter_name');
            $table->string('reporter_role')->nullable()->after('reporter_email');
            $table->string('reporter_department')->nullable()->after('reporter_role');
            $table->string('reporter_phone')->nullable()->after('reporter_department');
            $table->string('reporter_student_id')->nullable()->after('reporter_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn([
                'reporter_email',
                'reporter_role',
                'reporter_department',
                'reporter_phone',
                'reporter_student_id',
            ]);
        });

        Schema::table('concerns', function (Blueprint $table) {
            $table->dropColumn([
                'reporter_name',
                'reporter_email',
                'reporter_role',
                'reporter_department',
                'reporter_phone',
                'reporter_student_id',
            ]);
        });
    }
};
