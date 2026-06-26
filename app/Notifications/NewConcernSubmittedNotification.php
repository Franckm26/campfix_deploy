<?php

namespace App\Notifications;

use App\Models\Concern;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewConcernSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Concern $concern)
    {
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
        $requester = $this->concern->user;
        $categoryName = $this->concern->categoryRelation->name ?? 'N/A';
        $title = $this->concern->title ?? 'New Concern';

        return (new MailMessage)
            ->subject("New Concern Submitted - {$title}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A new concern has been submitted and is pending review.')
            ->line('Concern details:')
            ->line("- Title: {$title}")
            ->line('- Submitted by: '.($requester?->name ?? 'Unknown user'))
            ->line("- Location: {$this->concern->location}")
            ->line("- Category: {$categoryName}")
            ->line("- Status: {$this->concern->status}")
            ->line('- Description: '.str($this->concern->description ?? 'No description provided')->limit(200))
            ->action('Review Concern', url('/admin'))
            ->line('Please review and assign this concern when ready.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $requester = $this->concern->user;

        return [
            'title' => 'New Concern Submitted',
            'message' => "New concern '{$this->concern->title}' was submitted by ".($requester?->name ?? 'Unknown user').'.',
            'concern_id' => $this->concern->id,
            'concern_title' => $this->concern->title,
            'requester_name' => $requester?->name,
            'location' => $this->concern->location,
            'status' => $this->concern->status,
            'url' => '/admin',
        ];
    }
}
