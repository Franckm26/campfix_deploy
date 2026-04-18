<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportAssignedNotification extends Notification
{
    use Queueable;

    protected $report;

    protected $assignedBy;

    protected $assignedAt;

    /**
     * Create a new notification instance.
     */
    public function __construct(Report $report, string $assignedBy, $assignedAt = null)
    {
        $this->report = $report;
        $this->assignedBy = $assignedBy;
        $this->assignedAt = $assignedAt ?? now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $categoryName = $this->report->category->name ?? 'N/A';
        $title = $this->report->title ?? 'Report';
        $location = $this->report->location ?? 'N/A';
        $description = $this->report->description ?? 'No description provided';

        $mail = (new MailMessage)
            ->subject("New Report ASSIGNED to You - {$title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new report has been **ASSIGNED** to you.')
            ->line('**Report Details:**')
            ->line("- Title: {$title}")
            ->line("- Location: {$location}")
            ->line("- Category: {$categoryName}")
            ->line("- Severity: {$this->report->severity}")
            ->line("- Status: {$this->report->status}")
            ->line('- Description: '.substr($description, 0, 200).(strlen($description) > 200 ? '...' : ''));

        if ($this->report->photo_path) {
            $mail->line('- Photo: Attached');
        }

        return $mail
            ->line("Assigned by: {$this->assignedBy}")
            ->line("Assigned at: {$this->assignedAt}")
            ->action('Acknowledge & View', url('/reports/assigned'))
            ->line('Please acknowledge this report and begin working on it.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->report->title ?? 'Report';
        $categoryName = $this->report->category->name ?? 'N/A';

        return [
            'title' => 'New Report Assigned',
            'message' => "A report '{$title}' has been assigned to you.",
            'report_id' => $this->report->id,
            'report_title' => $title,
            'location' => $this->report->location,
            'category' => $categoryName,
            'severity' => $this->report->severity,
            'status' => $this->report->status,
            'assigned_by' => $this->assignedBy,
            'assigned_at' => $this->assignedAt,
            'url' => '/reports/assigned',
        ];
    }
}
