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
            if (!Schema::hasColumn('concerns', 'damaged_part')) {
                $table->string('damaged_part', 255)->nullable()->after('cost');
            }
            if (!Schema::hasColumn('concerns', 'replaced_part')) {
                $table->string('replaced_part', 255)->nullable()->after('damaged_part');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (Schema::hasColumn('concerns', 'damaged_part')) {
                $table->dropColumn('damaged_part');
            }
            if (Schema::hasColumn('concerns', 'replaced_part')) {
                $table->dropColumn('replaced_part');
            }
        });
    }
};
