<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (! Schema::hasColumn('concerns', 'title')) {
                $table->string('title')->nullable();
            }
            if (! Schema::hasColumn('concerns', 'location')) {
                $table->string('location')->nullable();
            }
            if (! Schema::hasColumn('concerns', 'category')) {
                $table->string('category')->nullable();
            }
            if (! Schema::hasColumn('concerns', 'description')) {
                $table->text('description')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('concerns', function (Blueprint $table) {
            $table->dropColumn(['title', 'location', 'category', 'description']);
        });
    }
};
