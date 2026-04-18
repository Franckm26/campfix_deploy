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
        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'resolution_notes')) {
                $table->text('resolution_notes')->nullable()->after('status');
            }
            if (! Schema::hasColumn('reports', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('resolution_notes');
            }
            if (! Schema::hasColumn('reports', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->after('resolved_at');
            }
            if (! Schema::hasColumn('reports', 'damaged_part')) {
                $table->string('damaged_part', 255)->nullable()->after('cost');
            }
            if (! Schema::hasColumn('reports', 'replaced_part')) {
                $table->string('replaced_part', 255)->nullable()->after('damaged_part');
            }
            if (! Schema::hasColumn('reports', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('maintenance_archived');
            }
            if (! Schema::hasColumn('reports', 'archived_by')) {
                $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'resolution_notes')) {
                $table->dropColumn('resolution_notes');
            }
            if (Schema::hasColumn('reports', 'resolved_at')) {
                $table->dropColumn('resolved_at');
            }
            if (Schema::hasColumn('reports', 'cost')) {
                $table->dropColumn('cost');
            }
            if (Schema::hasColumn('reports', 'damaged_part')) {
                $table->dropColumn('damaged_part');
            }
            if (Schema::hasColumn('reports', 'replaced_part')) {
                $table->dropColumn('replaced_part');
            }
            if (Schema::hasColumn('reports', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
            if (Schema::hasColumn('reports', 'archived_by')) {
                $table->dropColumn('archived_by');
            }
        });
    }
};
