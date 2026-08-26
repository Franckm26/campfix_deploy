<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create audit_reports table - immutable copy of all reports
        Schema::create('audit_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_report_id')->index();
            
            // User info snapshot (immutable)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('reporter_name');
            $table->string('reporter_email');
            $table->string('reporter_role');
            $table->string('reporter_department')->nullable();
            $table->string('reporter_phone')->nullable();
            $table->string('reporter_student_id')->nullable();
            
            // Report details
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('details')->nullable();
            $table->string('location')->nullable();
            $table->string('severity')->nullable();
            $table->boolean('is_safety_hazard')->default(false);
            $table->string('status')->default('Pending');
            $table->string('photo_path')->nullable();
            
            // Category snapshot
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('category_name')->nullable();
            
            // Assignment info
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('assigned_to_name')->nullable();
            $table->timestamp('assigned_at')->nullable();
            
            // Resolution info
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('damaged_part')->nullable();
            $table->string('replaced_part')->nullable();
            
            // Audit metadata
            $table->string('action')->default('created'); // created, updated, deleted
            $table->unsignedBigInteger('action_by')->nullable();
            $table->string('action_by_name')->nullable();
            $table->timestamp('action_at');
            
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('original_report_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('action_at');
        });

        // Create audit_concerns table - immutable copy of all concerns
        Schema::create('audit_concerns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_concern_id')->index();
            
            // User info snapshot (immutable)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('reporter_name');
            $table->string('reporter_email');
            $table->string('reporter_role');
            $table->string('reporter_department')->nullable();
            $table->string('reporter_phone')->nullable();
            $table->string('reporter_student_id')->nullable();
            $table->boolean('is_anonymous')->default(false);
            
            // Concern details
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('details')->nullable();
            $table->string('location')->nullable();
            $table->string('location_type')->nullable();
            $table->string('room_number')->nullable();
            $table->string('priority')->nullable();
            $table->string('status')->default('Pending');
            $table->string('image_path')->nullable();
            
            // Category snapshot
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('category_name')->nullable();
            
            // Assignment info
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('assigned_to_name')->nullable();
            $table->timestamp('assigned_at')->nullable();
            
            // Resolution info
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('damaged_part')->nullable();
            $table->string('replaced_part')->nullable();
            
            // Audit metadata
            $table->string('action')->default('created'); // created, updated, deleted
            $table->unsignedBigInteger('action_by')->nullable();
            $table->string('action_by_name')->nullable();
            $table->timestamp('action_at');
            
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('original_concern_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('action_at');
        });

        // Add comment to indicate these are immutable tables
        DB::statement("COMMENT ON TABLE audit_reports IS 'Immutable audit log of all reports - no deletes allowed, for record keeping only'");
        DB::statement("COMMENT ON TABLE audit_concerns IS 'Immutable audit log of all concerns - no deletes allowed, for record keeping only'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_reports');
        Schema::dropIfExists('audit_concerns');
    }
};
