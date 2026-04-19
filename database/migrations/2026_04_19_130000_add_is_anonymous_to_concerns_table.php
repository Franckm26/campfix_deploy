<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (!Schema::hasColumn('concerns', 'image_path')) {
                $table->string('image_path')->nullable()->after('description');
            }
            if (!Schema::hasColumn('concerns', 'is_anonymous')) {
                $table->boolean('is_anonymous')->default(false)->after('image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (Schema::hasColumn('concerns', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('concerns', 'is_anonymous')) {
                $table->dropColumn('is_anonymous');
            }
        });
    }
};
