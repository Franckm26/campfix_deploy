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
            if (!Schema::hasColumn('event_requests', 'department')) {
                $table->enum('department', ['GE', 'ICT', 'Business Management', 'THM'])->nullable()->after('category');
            }
            if (!Schema::hasColumn('event_requests', 'other_category')) {
                $table->string('other_category')->nullable()->after('department');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $columnsToCheck = ['department', 'other_category'];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('event_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
