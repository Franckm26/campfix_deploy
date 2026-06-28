<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concern_reporters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concern_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_original')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamps();

            $table->unique(['concern_id', 'user_id']);
        });

        Schema::table('concerns', function (Blueprint $table) {
            if (! Schema::hasColumn('concerns', 'report_count')) {
                $table->unsignedInteger('report_count')->default(1)->after('is_anonymous');
            }
        });

        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'report_count')) {
                $table->unsignedInteger('report_count')->default(1)->after('status');
            }
        });

        DB::table('concerns')
            ->whereNull('report_count')
            ->orWhere('report_count', '<', 1)
            ->update(['report_count' => 1]);

        DB::table('reports')
            ->whereNull('report_count')
            ->orWhere('report_count', '<', 1)
            ->update(['report_count' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('concern_reporters');

        Schema::table('concerns', function (Blueprint $table) {
            if (Schema::hasColumn('concerns', 'report_count')) {
                $table->dropColumn('report_count');
            }
        });

        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'report_count')) {
                $table->dropColumn('report_count');
            }
        });
    }
};
