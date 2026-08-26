<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Concern;
use App\Models\AuditReport;
use App\Models\AuditConcern;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportAuditService
{
    /**
     * Create a report with user snapshot and audit trail
     * Uses ACID transaction to ensure data integrity
     */
    public function createReportWithAudit(array $reportData, User $user)
    {
        return DB::transaction(function () use ($reportData, $user) {
            try {
                // Snapshot user information (immutable)
                $userSnapshot = $this->snapshotUser($user);
                
                // Merge user snapshot with report data
                $reportData = array_merge($reportData, $userSnapshot);
                
                // Create the report
                $report = Report::create($reportData);
                
                // Get category name if category_id exists
                $categoryName = null;
                if (isset($reportData['category_id'])) {
                    $category = Category::find($reportData['category_id']);
                    $categoryName = $category ? $category->name : null;
                }
                
                // Create immutable audit record
                $this->createReportAuditRecord($report, $user, 'created', $categoryName);
                
                Log::info('[ReportAuditService] Report created with audit trail', [
                    'report_id' => $report->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ]);
                
                return $report;
                
            } catch (\Exception $e) {
                Log::error('[ReportAuditService] Failed to create report', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Create a concern with user snapshot and audit trail
     * Uses ACID transaction to ensure data integrity
     */
    public function createConcernWithAudit(array $concernData, User $user)
    {
        return DB::transaction(function () use ($concernData, $user) {
            try {
                // Snapshot user information (immutable)
                $userSnapshot = $this->snapshotUser($user);
                
                // Merge user snapshot with concern data
                $concernData = array_merge($concernData, $userSnapshot);
                
                // Create the concern
                $concern = Concern::create($concernData);
                
                // Get category name if category_id exists
                $categoryName = null;
                if (isset($concernData['category_id'])) {
                    $category = Category::find($concernData['category_id']);
                    $categoryName = $category ? $category->name : null;
                }
                
                // Create immutable audit record
                $this->createConcernAuditRecord($concern, $user, 'created', $categoryName);
                
                Log::info('[ReportAuditService] Concern created with audit trail', [
                    'concern_id' => $concern->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ]);
                
                return $concern;
                
            } catch (\Exception $e) {
                Log::error('[ReportAuditService] Failed to create concern', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Update report and create audit trail
     * Uses ACID transaction
     */
    public function updateReportWithAudit(Report $report, array $updateData, User $actionBy)
    {
        return DB::transaction(function () use ($report, $updateData, $actionBy) {
            try {
                // Update the report
                $report->update($updateData);
                
                // Get category name if exists
                $categoryName = $report->category ? $report->category->name : null;
                
                // Create audit record for the update
                $this->createReportAuditRecord($report, $actionBy, 'updated', $categoryName);
                
                Log::info('[ReportAuditService] Report updated with audit trail', [
                    'report_id' => $report->id,
                    'action_by' => $actionBy->id,
                ]);
                
                return $report;
                
            } catch (\Exception $e) {
                Log::error('[ReportAuditService] Failed to update report', [
                    'error' => $e->getMessage(),
                    'report_id' => $report->id,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Update concern and create audit trail
     * Uses ACID transaction
     */
    public function updateConcernWithAudit(Concern $concern, array $updateData, User $actionBy)
    {
        return DB::transaction(function () use ($concern, $updateData, $actionBy) {
            try {
                // Update the concern
                $concern->update($updateData);
                
                // Get category name if exists
                $categoryName = $concern->categoryRelation ? $concern->categoryRelation->name : null;
                
                // Create audit record for the update
                $this->createConcernAuditRecord($concern, $actionBy, 'updated', $categoryName);
                
                Log::info('[ReportAuditService] Concern updated with audit trail', [
                    'concern_id' => $concern->id,
                    'action_by' => $actionBy->id,
                ]);
                
                return $concern;
                
            } catch (\Exception $e) {
                Log::error('[ReportAuditService] Failed to update concern', [
                    'error' => $e->getMessage(),
                    'concern_id' => $concern->id,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Snapshot user information at current moment
     * This data will be immutable in the report/concern
     */
    private function snapshotUser(User $user): array
    {
        return [
            'reporter_name' => $user->name,
            'reporter_email' => $user->email,
            'reporter_role' => $user->role,
            'reporter_department' => $user->department,
            'reporter_phone' => $user->phone,
            'reporter_student_id' => $user->student_id,
        ];
    }

    /**
     * Create immutable audit record for report
     */
    private function createReportAuditRecord(Report $report, User $actionBy, string $action, ?string $categoryName = null)
    {
        $assignedToName = null;
        if ($report->assigned_to) {
            $assignedUser = User::find($report->assigned_to);
            $assignedToName = $assignedUser ? $assignedUser->name : null;
        }

        AuditReport::create([
            'original_report_id' => $report->id,
            'user_id' => $report->user_id,
            'reporter_name' => $report->reported_by_name ?? $report->reporter_name,
            'reporter_email' => $report->reporter_email,
            'reporter_role' => $report->reporter_role,
            'reporter_department' => $report->reporter_department,
            'reporter_phone' => $report->reporter_phone,
            'reporter_student_id' => $report->reporter_student_id,
            'title' => $report->title,
            'description' => $report->description,
            'details' => $report->details,
            'location' => $report->location,
            'severity' => $report->severity,
            'is_safety_hazard' => $report->is_safety_hazard,
            'status' => $report->status,
            'photo_path' => $report->photo_path,
            'category_id' => $report->category_id,
            'category_name' => $categoryName,
            'assigned_to' => $report->assigned_to,
            'assigned_to_name' => $assignedToName,
            'assigned_at' => $report->assigned_at,
            'resolution_notes' => $report->resolution_notes,
            'resolved_at' => $report->resolved_at,
            'cost' => $report->cost,
            'damaged_part' => $report->damaged_part,
            'replaced_part' => $report->replaced_part,
            'action' => $action,
            'action_by' => $actionBy->id,
            'action_by_name' => $actionBy->name,
            'action_at' => now(),
        ]);
    }

    /**
     * Create immutable audit record for concern
     */
    private function createConcernAuditRecord(Concern $concern, User $actionBy, string $action, ?string $categoryName = null)
    {
        $assignedToName = null;
        if ($concern->assigned_to) {
            $assignedUser = User::find($concern->assigned_to);
            $assignedToName = $assignedUser ? $assignedUser->name : null;
        }

        AuditConcern::create([
            'original_concern_id' => $concern->id,
            'user_id' => $concern->user_id,
            'reporter_name' => $concern->reporter_name,
            'reporter_email' => $concern->reporter_email,
            'reporter_role' => $concern->reporter_role,
            'reporter_department' => $concern->reporter_department,
            'reporter_phone' => $concern->reporter_phone,
            'reporter_student_id' => $concern->reporter_student_id,
            'is_anonymous' => $concern->is_anonymous,
            'title' => $concern->title,
            'description' => $concern->description,
            'details' => $concern->details,
            'location' => $concern->location,
            'location_type' => $concern->location_type,
            'room_number' => $concern->room_number,
            'priority' => $concern->priority,
            'status' => $concern->status,
            'image_path' => $concern->image_path,
            'category_id' => $concern->category_id,
            'category_name' => $categoryName,
            'assigned_to' => $concern->assigned_to,
            'assigned_to_name' => $assignedToName,
            'assigned_at' => $concern->assigned_at,
            'resolution_notes' => $concern->resolution_notes,
            'resolved_at' => $concern->resolved_at,
            'cost' => $concern->cost,
            'damaged_part' => $concern->damaged_part,
            'replaced_part' => $concern->replaced_part,
            'action' => $action,
            'action_by' => $actionBy->id,
            'action_by_name' => $actionBy->name,
            'action_at' => now(),
        ]);
    }
}
