<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_intended_users', function (Blueprint $table) {
            $table->json('approval_roles')->nullable()->after('code');
        });

        // Preserve the established SHS workflow when moving it into configurable setup.
        DB::table('event_intended_users')
            ->where('code', 'shs')
            ->whereNull('approval_roles')
            ->update(['approval_roles' => json_encode(['principal_assistant', 'academic_head', 'school_admin']), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('event_intended_users', function (Blueprint $table) {
            $table->dropColumn('approval_roles');
        });
    }
};
