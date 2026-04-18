<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRequestApprovalNotification extends Notification
{
    use Queueable;

    protected $eventTitle;

    protected $approvalLevel;

    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $eventTitle, int $approvalLevel, string $status)
    {
        $this->eventTitle = $eventTitle;
        $this->approvalLevel = $approvalLevel;
        $this->status = $status;
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
        $levelNames = [
            1 => 'Program Head',
            2 => 'Academic Head',
            3 => 'Building Admin',
            4 => 'School Admin',
        ];

        $levelName = $levelNames[$this->approvalLevel] ?? 'Approval';

        if ($this->status === 'Rejected') {
            return (new MailMessage)
                ->subject("Event Request REJECTED - {$this->eventTitle}")
                ->greeting("Hello {$notifiable->name}!")
                ->line("Your event request **'{$this->eventTitle}'** has been **REJECTED** by {$levelName}.")
                ->line('Please contact the approver for more information or submit a new request.')
                ->action('View My Requests', url('/events/my'))
                ->line('Thank you for using CampFix!');
        }

        if ($this->approvalLevel === 4) {
            return (new MailMessage)
                ->subject("Event Request FULLY APPROVED - {$this->eventTitle}")
                ->greeting("Congratulations {$notifiable->name}!")
                ->line("Your event request **'{$this->eventTitle}'** has been **FULLY APPROVED**!")
                ->line('All approval levels have been completed. Your event is now confirmed.')
                ->action('View My Requests', url('/events/my'))
                ->line('Thank you for using CampFix!');
        }

        $nextLevel = $this->approvalLevel + 1;
        $nextLevelName = $levelNames[$nextLevel] ?? 'Next Level';

        return (new MailMessage)
            ->subject("Event Request Update - {$this->eventTitle}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your event request **'{$this->eventTitle}'** has been approved by {$levelName}.")
            ->line("Current progress: Level {$this->approvalLevel} of 4 approved.")
            ->line("Waiting for {$nextLevelName} review.")
            ->action('View My Requests', url('/events/my'))
            ->line('Thank you for using CampFix!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $levelNames = [
            1 => 'Program Head',
            2 => 'Academic Head',
            3 => 'Building Admin',
            4 => 'School Admin',
        ];

        $levelName = $levelNames[$this->approvalLevel] ?? 'Approval';

        if ($this->status === 'Rejected') {
            return [
                'title' => 'Event Request Rejected',
                'message' => "Your event request '{$this->eventTitle}' has been REJECTED by {$levelName}.",
                'event_title' => $this->eventTitle,
                'approval_level' => $this->approvalLevel,
                'status' => $this->status,
                'url' => '/events/my',
            ];
        }

        if ($this->approvalLevel === 4) {
            return [
                'title' => 'Event Request Fully Approved',
                'message' => "Your event request '{$this->eventTitle}' has been FULLY APPROVED!",
                'event_title' => $this->eventTitle,
                'approval_level' => $this->approvalLevel,
                'status' => $this->status,
                'url' => '/events/my',
            ];
        }

        $nextLevel = $this->approvalLevel + 1;
        $nextLevelName = $levelNames[$nextLevel] ?? 'Next Level';

        return [
            'title' => 'Event Request Update',
            'message' => "Your event request '{$this->eventTitle}' was approved by {$levelName}. Waiting for {$nextLevelName} review.",
            'event_title' => $this->eventTitle,
            'approval_level' => $this->approvalLevel,
            'status' => $this->status,
            'url' => '/events/my',
        ];
    }
}
