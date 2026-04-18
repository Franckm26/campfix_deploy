<?php

namespace App\Notifications;

use App\Models\EventRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEventRequestNotification extends Notification
{
    use Queueable;

    protected $eventRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(EventRequest $eventRequest)
    {
        $this->eventRequest = $eventRequest;
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
        $requester = $this->eventRequest->user;

        return (new MailMessage)
            ->subject("New Event Request Pending Approval - {$this->eventRequest->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new event request requires your approval.')
            ->line('**Event Details:**')
            ->line("- Title: {$this->eventRequest->title}")
            ->line("- Requested by: {$requester->name}")
            ->line("- Date: {$this->eventRequest->event_date->format('F j, Y')}")
            ->line("- Time: {$this->eventRequest->start_time} - {$this->eventRequest->end_time}")
            ->line("- Location: {$this->eventRequest->location}")
            ->line("- Category: {$this->eventRequest->category}")
            ->line("- Priority: {$this->eventRequest->priority}")
            ->line("- Description: {$this->eventRequest->description}")
            ->action('Review Event Request', url('/events/pending'))
            ->line('Thank you for using CampFix!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $requester = $this->eventRequest->user;

        return [
            'title' => 'New Event Request Pending',
            'message' => "New event request '{$this->eventRequest->title}' from {$requester->name} requires your approval.",
            'event_title' => $this->eventRequest->title,
            'event_id' => $this->eventRequest->id,
            'requester_name' => $requester->name,
            'event_date' => $this->eventRequest->event_date->toDateString(),
            'url' => '/events/pending',
        ];
    }
}
