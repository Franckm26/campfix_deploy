<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('event_education_levels')) {
            Schema::create('event_education_levels', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('event_approval_chains')) {
            Schema::create('event_approval_chains', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_education_level_id')->constrained('event_education_levels')->cascadeOnDelete();
                $table->foreignId('event_request_type_id')->constrained('event_request_types')->cascadeOnDelete();
                $table->json('approval_roles');
                $table->timestamps();
                $table->unique(['event_education_level_id', 'event_request_type_id'], 'event_approval_chain_pair_unique');
            });
        }

        if (Schema::hasTable('event_requests') && ! Schema::hasColumn('event_requests', 'intended_user')) {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->string('intended_user', 100)->nullable()->after('education_level');
            });

            DB::table('event_requests')->update(['intended_user' => DB::raw('education_level')]);
            DB::table('event_requests')->where('education_level', '!=', 'shs')->update(['education_level' => 'tertiary']);
        }

        foreach ([['College/University', 'tertiary'], ['Senior High School (SHS)', 'shs']] as [$name, $code]) {
            DB::table('event_education_levels')->updateOrInsert(
                ['code' => $code],
                ['name' => $name, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $levels = DB::table('event_education_levels')->whereIn('code', ['tertiary', 'shs'])->pluck('id', 'code');
        $types = DB::table('event_request_types')->whereIn('name', ['Academic', 'Non-Academic'])->pluck('id', 'name');
        $defaults = [
            ['tertiary', 'Academic', ['program_head', 'academic_head', 'building_admin', 'school_admin']],
            ['tertiary', 'Non-Academic', ['building_admin', 'school_admin']],
            ['shs', 'Academic', ['principal_assistant', 'academic_head', 'school_admin']],
            ['shs', 'Non-Academic', ['principal_assistant', 'academic_head', 'school_admin']],
        ];
        foreach ($defaults as [$levelCode, $typeName, $roles]) {
            if (! isset($levels[$levelCode], $types[$typeName])) continue;
            DB::table('event_approval_chains')->updateOrInsert(
                ['event_education_level_id' => $levels[$levelCode], 'event_request_type_id' => $types[$typeName]],
                ['approval_roles' => json_encode($roles), 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('event_requests') && Schema::hasColumn('event_requests', 'intended_user')) {
            Schema::table('event_requests', fn (Blueprint $table) => $table->dropColumn('intended_user'));
        }
        Schema::dropIfExists('event_approval_chains');
        Schema::dropIfExists('event_education_levels');
    }
};
