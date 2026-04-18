<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->tinyInteger('approval_level')->default(0)->after('status');
            $table->json('approval_history')->nullable()->after('approval_level');
            $table->unsignedBigInteger('approved_by_level_1')->nullable()->after('approval_history');
            $table->timestamp('approved_at_level_1')->nullable()->after('approved_by_level_1');
            $table->unsignedBigInteger('approved_by_level_2')->nullable()->after('approved_at_level_1');
            $table->timestamp('approved_at_level_2')->nullable()->after('approved_by_level_2');
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropColumn([
                'approval_level',
                'approval_history',
                'approved_by_level_1',
                'approved_at_level_1',
                'approved_by_level_2',
                'approved_at_level_2',
            ]);
        });
    }
};
