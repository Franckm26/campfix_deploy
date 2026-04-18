<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Concern;
use App\Models\EventRequest;
use App\Models\User;
use App\Notifications\ConcernAssignedNotification;
use App\Notifications\ConcernResolvedNotification;
use App\Notifications\EventRequestApprovalNotification;
use App\Notifications\NewEventRequestNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to user about their event request approval progress
     * Uses both database notification and email via Laravel's notification system
     *
     * @param  User  $user  The user to notify
     * @param  string  $eventTitle  The title of the event
     * @param  int  $approvalLevel  The current approval level (1, 2, or 3)
     * @param  string  $status  The current status (Approved, Rejected)
     */
    public function notifyEventRequestStatus(User $user, string $eventTitle, int $approvalLevel, string $status): bool
    {
        try {
            // Send notification via Laravel's notification system (both email and database)
            $user->notify(new EventRequestApprovalNotification($eventTitle, $approvalLevel, $status));

            // Also log in activity log for reference
            $this->logNotification($user, $eventTitle, $approvalLevel, $status);

            return true;

        } catch (\Exception $e) {
            Log::error('Notification failed: '.$e->getMessage());

            // Fallback: just log the notification attempt
            $this->logNotification($user, $eventTitle, $approvalLevel, $status);

            return false;
        }
    }

    /**
     * Log notification in activity log as fallback
     */
    private function logNotification(User $user, string $eventTitle, int $approvalLevel, string $status): void
    {
        $levelNames = [
            1 => 'Program Head',
            2 => 'Academic Head',
            3 => 'School Admin',
        ];

        $levelName = $levelNames[$approvalLevel] ?? 'Approval';

        if ($status === 'Rejected') {
            $message = "Event request '{$eventTitle}' REJECTED by {$levelName} - Notification sent to {$user->email}";
        } elseif ($approvalLevel === 3) {
            $message = "Event request '{$eventTitle}' FULLY APPROVED - Notification sent to {$user->email}";
        } else {
            $nextLevel = $approvalLevel + 1;
            $nextLevelName = $levelNames[$nextLevel] ?? 'Next Level';
            $message = "Event request '{$eventTitle}' approved by {$levelName} - Waiting for {$nextLevelName} - Notification sent to {$user->email}";
        }

        ActivityLog::log('notification_sent', $message, $user->id, 'user');
    }

    /**
     * Notify ONLY the next approver in the sequential approval chain.
     * Notifications are sent strictly one level at a time.
     *
     * Tertiary chain: Program Head (0) → Academic Head (1) → Building Admin (2) → School Admin (3)
     * SHS chain:      Principal Assistant (0) → Academic Head (1) → School Admin (2)
     *
     * @param  EventRequest  $eventRequest  The event request to notify about
     */
    public function notifyApproversOfNewEvent(EventRequest $eventRequest): bool
    {
        try {
            $currentLevel = $eventRequest->approval_level ?? 0;
            $isShs = ($eventRequest->education_level ?? 'tertiary') === 'shs';

            $roleToNotify = null;

            if ($isShs) {
                // SHS chain: Principal Assistant → Academic Head → School Admin
                switch ($currentLevel) {
                    case EventRequest::LEVEL_NONE:
                        $roleToNotify = User::ROLE_PRINCIPAL_ASSISTANT;
                        break;
                    case EventRequest::LEVEL_1_PROGRAM_HEAD:
                        $roleToNotify = User::ROLE_ACADEMIC_HEAD;
                        break;
                    case EventRequest::LEVEL_2_ACADEMIC_HEAD:
                        $roleToNotify = User::ROLE_SCHOOL_ADMIN;
                        break;
                    default:
                        $roleToNotify = User::ROLE_SCHOOL_ADMIN;
                        break;
                }
            } else {
                // Tertiary chain: Program Head → Academic Head → Building Admin → School Admin
                switch ($currentLevel) {
                    case EventRequest::LEVEL_NONE:
                        $roleToNotify = User::ROLE_PROGRAM_HEAD;
                        break;
                    case EventRequest::LEVEL_1_PROGRAM_HEAD:
                        $roleToNotify = User::ROLE_ACADEMIC_HEAD;
                        break;
                    case EventRequest::LEVEL_2_ACADEMIC_HEAD:
                        $roleToNotify = User::ROLE_BUILDING_ADMIN;
                        break;
                    case EventRequest::LEVEL_3_BUILDING_ADMIN:
                        $roleToNotify = User::ROLE_SCHOOL_ADMIN;
                        break;
                    default:
                        $roleToNotify = User::ROLE_SCHOOL_ADMIN;
                        break;
                }
            }

            if ($roleToNotify) {
                $approvers = User::where('role', $roleToNotify)->get();

                // Fallback: if no one at this role exists, escalate to school admin
                if ($approvers->isEmpty()) {
                    $approvers = User::where('role', User::ROLE_SCHOOL_ADMIN)->get();
                }

                foreach ($approvers as $approver) {
                    $approver->notify(new NewEventRequestNotification($eventRequest));
                    ActivityLog::log('notification_sent', 'New event request notification sent to '.$roleToNotify.': '.$approver->email, $approver->id);
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to notify approvers: '.$e->getMessage());
            return false;
        }
    }

    /**
     * Notify building admin(s) and school admin(s) about a faculty-intended facility request.
     * Faculty requests are auto-approved — no approval chain is needed.
     *
     * @param  EventRequest  $eventRequest  The auto-approved facility request
     */
    public function notifyAdminsOfFacultyRequest(EventRequest $eventRequest): bool
    {
        try {
            $adminRoles = [User::ROLE_BUILDING_ADMIN, User::ROLE_SCHOOL_ADMIN];
            $notified = 0;

            foreach ($adminRoles as $role) {
                $admins = User::where('role', $role)->get();

                foreach ($admins as $admin) {
                    $admin->notify(new NewEventRequestNotification($eventRequest));
                    ActivityLog::log(
                        'notification_sent',
                        "Faculty facility request notification sent to {$role}: {$admin->email}",
                        $admin->id
                    );
                    $notified++;
                }
            }

            return $notified > 0;

        } catch (\Exception $e) {
            Log::error('Failed to notify admins of faculty facility request: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Send notification to user about their concern being resolved
     * Uses both database notification and email via Laravel's notification system
     *
     * @param  Concern  $concern  The concern that was resolved
     * @param  string  $resolvedBy  The name of the person who resolved it
     */
    public function notifyConcernResolved(Concern $concern, string $resolvedBy = 'Admin'): bool
    {
        try {
            $requester = $concern->user;

            if (! $requester) {
                Log::warning('Cannot notify concern requester - user not found: '.$concern->user_id);

                return false;
            }

            // Send notification via Laravel's notification system (both email and database)
            $requester->notify(new ConcernResolvedNotification($concern, $resolvedBy));

            // Also log in activity log for reference
            ActivityLog::log(
                'notification_sent',
                "Concern resolved notification sent to {$requester->email} for concern: ".($concern->title ?? $concern->id),
                $concern->id
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Concern resolution notification failed: '.$e->getMessage());

            // Fallback: log the notification attempt
            ActivityLog::log(
                'notification_failed',
                'Failed to send concern resolved notification: '.$e->getMessage(),
                $concern->id
            );

            return false;
        }
    }

    /**
     * Send notification to maintenance staff about a concern being assigned to them
     * Uses both database notification and email via Laravel's notification system
     *
     * @param  Concern  $concern  The concern that was assigned
     * @param  User  $assignedTo  The maintenance user who was assigned the concern
     * @param  string  $assignedBy  The name of the person who assigned the concern
     */
    public function notifyConcernAssigned(Concern $concern, User $assignedTo, string $assignedBy = 'Building Admin'): bool
    {
        try {
            // Send notification via Laravel's notification system (both email and database)
            $assignedTo->notify(new ConcernAssignedNotification($concern, $assignedBy));

            // Also log in activity log for reference
            ActivityLog::log(
                'notification_sent',
                "Concern assigned notification sent to {$assignedTo->email} for concern: ".($concern->title ?? $concern->id),
                $concern->id
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Concern assignment notification failed: '.$e->getMessage());

            // Fallback: log the notification attempt
            ActivityLog::log(
                'notification_failed',
                'Failed to send concern assigned notification: '.$e->getMessage(),
                $concern->id
            );

            return false;
        }
    }

    /**
     * Send notification to user about their report being resolved
     * Uses both database notification and email via Laravel's notification system
     *
     * @param  Report  $report  The report that was resolved
     * @param  string  $resolvedBy  The name of the person who resolved it
     */
    public function notifyReportResolved(Report $report, string $resolvedBy = 'Admin'): bool
    {
        try {
            $requester = $report->user;

            if (! $requester) {
                Log::warning('Cannot notify report requester - user not found: '.$report->user_id);

                return false;
            }

            // Send notification via Laravel's notification system (both email and database)
            $requester->notify(new ReportResolvedNotification($report, $resolvedBy));

            // Also log in activity log for reference
            ActivityLog::log(
                'notification_sent',
                "Report resolved notification sent to {$requester->email} for report: ".($report->title ?? $report->id),
                $report->id,
                'report'
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Report resolution notification failed: '.$e->getMessage());

            // Fallback: log the notification attempt
            ActivityLog::log(
                'notification_failed',
                'Failed to send report resolved notification: '.$e->getMessage(),
                $report->id,
                'report'
            );

            return false;
        }
    }
}
