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
            // user_id: should exist, if not add it
            if (! Schema::hasColumn('concerns', 'user_id')) {
                $table->unsignedBigInteger('user_id');
            }

            // Title: optional
            if (! Schema::hasColumn('concerns', 'title')) {
                $table->string('title')->nullable();
            } else {
                $table->string('title')->nullable()->change();
            }

            // Location
            if (! Schema::hasColumn('concerns', 'location')) {
                $table->string('location')->nullable();
            } else {
                $table->string('location')->nullable()->change();
            }

            // Category
            if (! Schema::hasColumn('concerns', 'category')) {
                $table->string('category')->nullable();
            } else {
                $table->string('category')->nullable()->change();
            }

            // Description
            if (! Schema::hasColumn('concerns', 'description')) {
                $table->text('description')->nullable();
            } else {
                $table->text('description')->nullable()->change();
            }

            // Timestamps
            if (! Schema::hasColumn('concerns', 'created_at') || ! Schema::hasColumn('concerns', 'updated_at')) {
                $table->timestamps();
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
