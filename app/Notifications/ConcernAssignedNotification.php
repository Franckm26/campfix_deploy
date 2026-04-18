<?php

namespace App\Notifications;

use App\Models\Concern;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConcernAssignedNotification extends Notification
{
    use Queueable;

    protected $concern;

    protected $assignedBy;

    protected $assignedAt;

    /**
     * Create a new notification instance.
     */
    public function __construct(Concern $concern, string $assignedBy, $assignedAt = null)
    {
        $this->concern = $concern;
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
        $categoryName = $this->concern->categoryRelation->name ?? 'N/A';
        $title = $this->concern->title ?? 'Concern';
        $location = $this->concern->location ?? 'N/A';
        $description = $this->concern->description ?? 'No description provided';

        $mail = (new MailMessage)
            ->subject("New Concern ASSIGNED to You - {$title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new concern has been **ASSIGNED** to you.')
            ->line('**Concern Details:**')
            ->line("- Title: {$title}")
            ->line("- Location: {$location}")
            ->line("- Category: {$categoryName}")
            ->line("- Priority: {$this->concern->priority}")
            ->line("- Status: {$this->concern->status}")
            ->line('- Description: '.substr($description, 0, 200).(strlen($description) > 200 ? '...' : ''));

        if ($this->concern->image_path) {
            $mail->line('- Image: Attached');
        }

        return $mail
            ->line("Assigned by: {$this->assignedBy}")
            ->line("Assigned at: {$this->assignedAt}")
            ->action('Acknowledge & View', url('/concerns/assigned'))
            ->line('Please acknowledge this concern and begin working on it.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->concern->title ?? 'Concern';
        $categoryName = $this->concern->categoryRelation->name ?? 'N/A';

        return [
            'title' => 'New Concern Assigned',
            'message' => "A concern '{$title}' has been assigned to you.",
            'concern_id' => $this->concern->id,
            'concern_title' => $title,
            'location' => $this->concern->location,
            'category' => $categoryName,
            'priority' => $this->concern->priority,
            'status' => $this->concern->status,
            'assigned_by' => $this->assignedBy,
            'assigned_at' => $this->assignedAt,
            'url' => '/concerns/assigned',
        ];
    }
}
