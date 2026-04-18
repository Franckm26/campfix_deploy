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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->foreignId('category_id')->nullable()->constrained();
            $table->string('severity')->nullable();
            $table->string('status')->default('pending');
            $table->string('photo_path')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->boolean('is_archived')->default(false);
            $table->timestamp('auto_delete_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
