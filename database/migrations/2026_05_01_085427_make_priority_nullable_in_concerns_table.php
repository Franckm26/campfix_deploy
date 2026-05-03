<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            $table->string('priority')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            $table->string('priority')->nullable(false)->default('medium')->change();
        });
    }
};
