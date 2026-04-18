<?php

namespace App\Notifications;

use App\Models\Concern;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConcernFollowUpNotification extends Notification
{
    use Queueable;

    protected $concern;
    protected $daysWaiting;

    /**
     * Create a new notification instance.
     */
    public function __construct(Concern $concern, int $daysWaiting)
    {
        $this->concern = $concern;
        $this->daysWaiting = $daysWaiting;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Follow-up: Concern Still Pending Assignment')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a follow-up regarding your concern that has been pending for ' . $this->daysWaiting . ' days without assignment.')
            ->line('**Concern Details:**')
            ->line('Location: ' . $this->concern->location)
            ->line('Category: ' . ($this->concern->categoryRelation ? $this->concern->categoryRelation->name : 'N/A'))
            ->line('Description: ' . $this->concern->description)
            ->line('Priority: ' . ucfirst($this->concern->priority))
            ->line('Submitted: ' . $this->concern->created_at->format('M d, Y h:i A'))
            ->line('We apologize for the delay. Your concern is important to us and we are working to assign it to the appropriate personnel.')
            ->action('View Concern', url('/concerns/' . $this->concern->id))
            ->line('Thank you for your patience.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'concern_id' => $this->concern->id,
            'title' => 'Follow-up: Concern Still Pending',
            'message' => 'Your concern at ' . $this->concern->location . ' has been pending for ' . $this->daysWaiting . ' days without assignment.',
            'type' => 'concern_follow_up',
            'days_waiting' => $this->daysWaiting,
            'url' => '/concerns/' . $this->concern->id,
        ];
    }
}
