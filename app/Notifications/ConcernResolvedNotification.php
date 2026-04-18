<?php

namespace App\Notifications;

use App\Models\Concern;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConcernResolvedNotification extends Notification
{
    use Queueable;

    protected $concern;

    protected $resolvedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Concern $concern, string $resolvedBy = 'Admin')
    {
        $this->concern = $concern;
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
        $categoryName = $this->concern->categoryRelation->name ?? 'N/A';
        $title = $this->concern->title ?? 'Your Concern';

        $mail = (new MailMessage)
            ->subject("Concern RESOLVED - {$title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your concern has been **RESOLVED**.')
            ->line('**Concern Details:**')
            ->line("- Title: {$title}")
            ->line("- Location: {$this->concern->location}")
            ->line("- Category: {$categoryName}")
            ->line("- Priority: {$this->concern->priority}")
            ->line("- Status: {$this->concern->status}");

        if ($this->concern->resolution_notes) {
            $mail->line("- Resolution Notes: {$this->concern->resolution_notes}");
        }

        if ($this->concern->cost) {
            $mail->line("- Cost: ₱{$this->concern->cost}");
        }

        return $mail
            ->line("Resolved by: {$this->resolvedBy}")
            ->action('View My Concerns', url('/concerns/my'))
            ->line('Thank you for using CampFix!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->concern->title ?? 'Your Concern';
        $categoryName = $this->concern->categoryRelation->name ?? 'N/A';

        return [
            'title' => 'Concern Resolved',
            'message' => "Your concern '{$title}' has been resolved.",
            'concern_id' => $this->concern->id,
            'concern_title' => $title,
            'location' => $this->concern->location,
            'category' => $categoryName,
            'priority' => $this->concern->priority,
            'status' => $this->concern->status,
            'resolution_notes' => $this->concern->resolution_notes,
            'cost' => $this->concern->cost,
            'resolved_by' => $this->resolvedBy,
            'url' => '/concerns/my',
        ];
    }
}
