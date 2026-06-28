<?php

namespace App\Notifications;

use App\Models\Concern;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConcernUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Concern $concern,
        protected string $updateTitle,
        protected string $updateMessage,
        protected ?string $updatedBy = null
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->concern->title ?? 'Concern';
        $categoryName = $this->concern->categoryRelation->name ?? 'N/A';

        $mail = (new MailMessage)
            ->subject("CampFix Concern Update - {$title}")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->updateMessage)
            ->line('Concern details:')
            ->line("- Title: {$title}")
            ->line("- Location: {$this->concern->location}")
            ->line("- Category: {$categoryName}")
            ->line("- Report Count: ".($this->concern->report_count ?? 1))
            ->line("- Status: {$this->concern->status}");

        if ($this->concern->priority) {
            $mail->line("- Priority: {$this->concern->priority}");
        }

        if ($this->concern->resolution_notes) {
            $mail->line("- Resolution notes: {$this->concern->resolution_notes}");
        }

        if ($this->updatedBy) {
            $mail->line("Updated by: {$this->updatedBy}");
        }

        return $mail
            ->action('View Concern', url('/concerns/'.$this->concern->id))
            ->line('Thank you for using CampFix.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->updateTitle,
            'message' => $this->updateMessage,
            'concern_id' => $this->concern->id,
            'concern_title' => $this->concern->title,
            'location' => $this->concern->location,
            'report_count' => $this->concern->report_count ?? 1,
            'status' => $this->concern->status,
            'priority' => $this->concern->priority,
            'updated_by' => $this->updatedBy,
            'url' => '/concerns/'.$this->concern->id,
        ];
    }
}
