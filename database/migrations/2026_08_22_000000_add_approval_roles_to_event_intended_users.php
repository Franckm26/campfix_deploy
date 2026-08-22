<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_intended_users', function (Blueprint $table) {
            $table->json('approval_roles')->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('event_intended_users', function (Blueprint $table) {
            $table->dropColumn('approval_roles');
        });
    }
};
