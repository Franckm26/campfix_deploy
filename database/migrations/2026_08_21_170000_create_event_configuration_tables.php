<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_request_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('requires_department')->default(false);
            $table->json('approval_roles')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('event_intended_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('event_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::table('event_requests', function (Blueprint $table) {
            $table->json('approval_route')->nullable()->after('approval_history');
        });

        foreach ([
            ['Academic', true, ['program_head', 'academic_head', 'building_admin', 'school_admin']],
            ['Non-Academic', false, ['building_admin', 'school_admin']],
        ] as [$name, $requiresDepartment, $roles]) {
            \DB::table('event_request_types')->insert(['name' => $name, 'requires_department' => $requiresDepartment, 'approval_roles' => json_encode($roles), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach ([['Faculty', 'faculty'], ['Tertiary', 'tertiary'], ['Senior High School', 'shs'], ['Staff', 'staff'], ['Maintenance', 'maintenance']] as [$name, $code]) {
            \DB::table('event_intended_users')->insert(['name' => $name, 'code' => $code, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach (['GE', 'ICT', 'Business Management', 'THM'] as $name) {
            \DB::table('event_departments')->insert(['name' => $name, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('event_requests', fn (Blueprint $table) => $table->dropColumn('approval_route'));
        Schema::dropIfExists('event_departments');
        Schema::dropIfExists('event_intended_users');
        Schema::dropIfExists('event_request_types');
    }
};
