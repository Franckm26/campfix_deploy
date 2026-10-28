<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Notifications\Notification;

class PriorityReviewNotification extends Notification
{
    public function __construct(public Report $report) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $priority = $this->report->is_safety_hazard ? 'Safety hazard' : ucfirst($this->report->severity);

        return [
            'title' => $priority.' report needs review',
            'message' => $priority.': '.$this->report->title,
            'report_id' => $this->report->id,
            'concern_id' => $this->report->concern_id,
            'url' => route($notifiable->isSystemAdministrator() ? 'superadmin.reports' : 'school-admin.reports', ['open_report' => $this->report->id]),
        ];
    }
}
