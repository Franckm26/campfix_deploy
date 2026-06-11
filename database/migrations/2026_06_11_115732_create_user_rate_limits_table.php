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
        Schema::create('user_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action_type', 50); // 'submission' or 'upload'
            $table->integer('count')->default(0);
            $table->date('date');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'action_type', 'date']);
            $table->unique(['user_id', 'action_type', 'date']);

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rate_limits');
    }
};
