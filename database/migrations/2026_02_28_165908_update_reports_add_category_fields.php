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
            if (!Schema::hasColumn('reports', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('reports', 'severity')) {
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])
                    ->nullable();
            }

            if (!Schema::hasColumn('reports', 'photo_path')) {
                $table->string('photo_path')->nullable();
            }

            if (!Schema::hasColumn('reports', 'is_archived')) {
                $table->boolean('is_archived')->default(false);
            }

            if (!Schema::hasColumn('reports', 'auto_delete_at')) {
                $table->timestamp('auto_delete_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'title')) {
                $table->string('title')->nullable();
            }

            if (Schema::hasColumn('reports', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
            
            $columnsToCheck = ['severity', 'photo_path', 'is_archived', 'auto_delete_at'];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('reports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
