<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('event_approval_chains')) {
            Schema::create('event_approval_chains', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_intended_user_id')->constrained('event_intended_users')->cascadeOnDelete();
                $table->foreignId('event_request_type_id')->constrained('event_request_types')->cascadeOnDelete();
                $table->json('approval_roles');
                $table->timestamps();
                $table->unique(['event_intended_user_id', 'event_request_type_id'], 'event_approval_chain_pair_unique');
            });
        }

        if (Schema::hasTable('event_requests') && ! Schema::hasColumn('event_requests', 'intended_user')) {
            Schema::table('event_requests', function (Blueprint $table) {
                $table->string('intended_user', 100)->nullable()->after('education_level');
            });

            DB::table('event_requests')->update(['intended_user' => DB::raw('education_level')]);
            DB::table('event_requests')->where('education_level', '!=', 'shs')->update(['education_level' => 'tertiary']);
        }

        $intendedUsers = DB::table('event_intended_users')->pluck('id', 'code');
        $types = DB::table('event_request_types')->whereIn('name', ['Academic', 'Non-Academic'])->pluck('id', 'name');
        foreach ($intendedUsers as $code => $intendedUserId) {
            foreach ($types as $typeName => $requestTypeId) {
                $roles = $code === 'shs'
                    ? ['principal_assistant', 'academic_head', 'school_admin']
                    : ($typeName === 'Academic'
                        ? ['program_head', 'academic_head', 'building_admin', 'school_admin']
                        : ['building_admin', 'school_admin']);
                DB::table('event_approval_chains')->updateOrInsert(
                    ['event_intended_user_id' => $intendedUserId, 'event_request_type_id' => $requestTypeId],
                    ['approval_roles' => json_encode($roles), 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('event_requests') && Schema::hasColumn('event_requests', 'intended_user')) {
            Schema::table('event_requests', fn (Blueprint $table) => $table->dropColumn('intended_user'));
        }
        Schema::dropIfExists('event_approval_chains');
    }
};
