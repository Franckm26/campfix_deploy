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
        Schema::table('event_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('event_requests', 'request_type')) {
                $table->string('request_type', 50)->nullable()->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            if (Schema::hasColumn('event_requests', 'request_type')) {
                $table->dropColumn('request_type');
            }
        });
    }
};
