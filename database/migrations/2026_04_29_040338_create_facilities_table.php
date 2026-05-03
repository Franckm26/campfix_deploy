<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('room'); // room, court, avr, library, lab, other
            $table->string('location')->nullable();   // building / floor
            $table->integer('capacity')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'unavailable', 'under_maintenance'])->default('available');
            $table->foreignId('managed_by')->nullable()->constrained('users')->nullOnDelete(); // building admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
