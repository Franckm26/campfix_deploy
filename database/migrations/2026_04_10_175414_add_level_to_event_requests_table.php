<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            // 'tertiary' or 'shs'
            if (!Schema::hasColumn('event_requests', 'education_level')) {
                $table->string('education_level')->default('tertiary')->after('department');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            if (Schema::hasColumn('event_requests', 'education_level')) {
                $table->dropColumn('education_level');
            }
        });
    }
};
