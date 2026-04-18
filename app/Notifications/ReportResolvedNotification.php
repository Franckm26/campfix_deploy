<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportResolvedNotification extends Notification
{
    use Queueable;

    protected $report;

    protected $resolvedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Report $report, string $resolvedBy = 'Admin')
    {
        $this->report = $report;
        $this->resolvedBy = $resolvedBy;
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
        $title = $this->report->title ?? 'Your Report';

        $mail = (new MailMessage)
            ->subject("Report RESOLVED - {$title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your report has been **RESOLVED**.')
            ->line('**Report Details:**')
            ->line("- Title: {$title}")
            ->line("- Location: {$this->report->location}")
            ->line("- Category: {$categoryName}")
            ->line("- Severity: {$this->report->severity}")
            ->line("- Status: {$this->report->status}");

        if ($this->report->resolution_notes) {
            $mail->line("- Resolution Notes: {$this->report->resolution_notes}");
        }

        return $mail
            ->line("Resolved by: {$this->resolvedBy}")
            ->action('View My Reports', url('/reports/my'))
            ->line('Thank you for using CampFix!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->report->title ?? 'Your Report';
        $categoryName = $this->report->category->name ?? 'N/A';

        return [
            'title' => 'Report Resolved',
            'message' => "Your report '{$title}' has been resolved.",
            'report_id' => $this->report->id,
            'report_title' => $title,
            'location' => $this->report->location,
            'category' => $categoryName,
            'severity' => $this->report->severity,
            'status' => $this->report->status,
            'resolution_notes' => $this->report->resolution_notes,
            'resolved_by' => $this->resolvedBy,
            'url' => '/reports/my',
        ];
    }
}
