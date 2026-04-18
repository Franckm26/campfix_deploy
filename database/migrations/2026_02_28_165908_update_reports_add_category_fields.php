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
        if (Schema::hasColumn('reports', 'title')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropColumn('title');
            });
        }

        Schema::table('reports', function (Blueprint $table) {

            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('severity', ['low', 'medium', 'high', 'critical'])
                ->nullable();

            $table->string('photo_path')->nullable();

            $table->boolean('is_archived')->default(false);

            $table->timestamp('auto_delete_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {

            $table->string('title')->nullable();

            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'category_id',
                'severity',
                'photo_path',
                'is_archived',
                'auto_delete_at',
            ]);
        });
    }
};
