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
        Schema::table('concerns', function (Blueprint $table) {
            if (!Schema::hasColumn('concerns', 'follow_up_sent')) {
                $table->boolean('follow_up_sent')->default(false)->after('assigned_at');
            }
            if (!Schema::hasColumn('concerns', 'follow_up_sent_at')) {
                $table->timestamp('follow_up_sent_at')->nullable()->after('follow_up_sent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            $columnsToCheck = ['follow_up_sent', 'follow_up_sent_at'];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('concerns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
