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
                $table->unique(['event_intended_user_id', 'event_request_type_id'], 'event_approval_chain_intended_type_unique');
            });
        } elseif (! Schema::hasColumn('event_approval_chains', 'event_intended_user_id')) {
            Schema::table('event_approval_chains', function (Blueprint $table) {
                $table->foreignId('event_intended_user_id')->nullable()->constrained('event_intended_users')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('event_approval_chains', 'event_education_level_id')) {
            if (Schema::hasTable('event_education_levels')) {
                foreach (DB::table('event_education_levels')->get() as $legacyLevel) {
                    $alreadyExists = DB::table('event_intended_users')
                        ->where('code', $legacyLevel->code)
                        ->orWhere('name', $legacyLevel->name)
                        ->exists();
                    if (! $alreadyExists) {
                        DB::table('event_intended_users')->insert([
                            'name' => $legacyLevel->name,
                            'code' => $legacyLevel->code,
                            'approval_roles' => null,
                            'is_active' => $legacyLevel->is_active,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $intendedUsers = DB::table('event_intended_users')->pluck('id', 'code');
                $legacyChains = DB::table('event_approval_chains as chains')
                    ->join('event_education_levels as levels', 'levels.id', '=', 'chains.event_education_level_id')
                    ->select('chains.id', 'levels.code')
                    ->get();
                foreach ($legacyChains as $legacyChain) {
                    if (isset($intendedUsers[$legacyChain->code])) {
                        DB::table('event_approval_chains')->where('id', $legacyChain->id)->update([
                            'event_intended_user_id' => $intendedUsers[$legacyChain->code],
                        ]);
                    }
                }
            }

            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE event_approval_chains DROP COLUMN event_education_level_id CASCADE');
            }
        }

        $intendedUsers = DB::table('event_intended_users')->pluck('id', 'code');
        $types = DB::table('event_request_types')->pluck('id', 'name');

        DB::table('event_approval_chains')->whereNull('event_intended_user_id')->delete();
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE event_approval_chains ALTER COLUMN event_intended_user_id SET NOT NULL');
        }

        foreach ($intendedUsers as $code => $intendedUserId) {
            foreach ($types as $typeName => $requestTypeId) {
                $existing = DB::table('event_approval_chains')
                    ->where('event_intended_user_id', $intendedUserId)
                    ->where('event_request_type_id', $requestTypeId)
                    ->exists();
                if ($existing) continue;

                $roles = $code === 'shs'
                    ? ['principal_assistant', 'academic_head', 'school_admin']
                    : ($typeName === 'Academic'
                        ? ['program_head', 'academic_head', 'building_admin', 'school_admin']
                        : ['building_admin', 'school_admin']);

                DB::table('event_approval_chains')->insert([
                    'event_intended_user_id' => $intendedUserId,
                    'event_request_type_id' => $requestTypeId,
                    'approval_roles' => json_encode($roles),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS event_approval_chain_intended_type_unique ON event_approval_chains (event_intended_user_id, event_request_type_id)');
        }
    }

    public function down(): void
    {
        // Keep the intended-user chains because existing requests may depend on them.
    }
};
