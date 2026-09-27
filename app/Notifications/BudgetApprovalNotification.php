<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BudgetApprovalNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Report $report,
        private readonly string $action,
        private readonly ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $amount = number_format((float) $this->report->budget_amount, 2);
        $title = $this->report->title ?: 'Report #'.$this->report->id;

        $message = match ($this->action) {
            'requested' => "Budget approval requested for {$title}: PHP {$amount}.",
            'approved' => "The PHP {$amount} budget for {$title} was approved.",
            'rejected' => "The PHP {$amount} budget for {$title} was rejected".
                ($this->reason ? ': '.$this->reason : '.'),
            default => "Budget for {$title} was updated.",
        };

        return [
            'type' => 'budget_approval',
            'action' => $this->action,
            'title' => 'Concern Budget '.ucfirst($this->action),
            'message' => $message,
            'report_id' => $this->report->id,
            'concern_id' => $this->report->concern_id,
            'url' => '/admin/reports?open_report='.$this->report->id,
        ];
    }
}
