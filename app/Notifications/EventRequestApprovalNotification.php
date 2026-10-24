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

    protected $approverName;

    protected $nextApproverName;

    protected $totalLevels;

    protected $fullyApproved;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $eventTitle,
        int $approvalLevel,
        string $status,
        ?string $approverName = null,
        ?string $nextApproverName = null,
        int $totalLevels = 4,
        bool $fullyApproved = false
    )
    {
        $this->eventTitle = $eventTitle;
        $this->approvalLevel = $approvalLevel;
        $this->status = $status;
        $this->approverName = $approverName;
        $this->nextApproverName = $nextApproverName;
        $this->totalLevels = $totalLevels;
        $this->fullyApproved = $fullyApproved;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        if ($notifiable->email_notifications ?? true) {
            $channels[] = 'mail';
        }
        
        return $channels;
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

        $levelName = $this->approverName ?: ($levelNames[$this->approvalLevel] ?? 'Approval');

        if ($this->status === 'Expired') {
            return (new MailMessage)
                ->subject("Facility Request AUTO-REJECTED - {$this->eventTitle}")
                ->greeting("Hello {$notifiable->name}!")
                ->line("Your facility request **'{$this->eventTitle}'** has been **AUTOMATICALLY REJECTED**.")
                ->line('The date of use has already passed before the request was fully approved.')
                ->action('View My Requests', url('/my-events?view=rejected'))
                ->line('Please submit a new request if you still need to use the facility.');
        }

        if ($this->status === 'Rejected') {
            return (new MailMessage)
                ->subject("Facility Request REJECTED - {$this->eventTitle}")
                ->greeting("Hello {$notifiable->name}!")
                ->line("Your facility request **'{$this->eventTitle}'** has been **REJECTED** by {$levelName}.")
                ->line('Please contact the approver for more information or submit a new request.')
                ->action('View My Requests', url('/events/my'))
                ->line('Thank you for using CampFix!');
        }

        if ($this->fullyApproved || $this->approvalLevel >= $this->totalLevels) {
            return (new MailMessage)
                ->subject("Facility Request FULLY APPROVED - {$this->eventTitle}")
                ->greeting("Hello {$notifiable->name}!")
                ->line("Your facility request **'{$this->eventTitle}'** has been **FULLY APPROVED**!")
                ->line('All approval levels have been completed. Your facility request is now confirmed.')
                ->action('View My Requests', url('/events/my'))
                ->line('Thank you for using CampFix!');
        }

        $nextLevel = $this->approvalLevel + 1;
        $nextLevelName = $this->nextApproverName ?: ($levelNames[$nextLevel] ?? 'Next Level');

        return (new MailMessage)
            ->subject("Facility Request Update - {$this->eventTitle}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your facility request **'{$this->eventTitle}'** has been approved by {$levelName}.")
            ->line("Current progress: Level {$this->approvalLevel} of {$this->totalLevels} approved.")
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

        $levelName = $this->approverName ?: ($levelNames[$this->approvalLevel] ?? 'Approval');

        if ($this->status === 'Expired') {
            return [
                'title' => 'Facility Request Auto-Rejected',
                'message' => "Your facility request '{$this->eventTitle}' was automatically rejected because the date of use has already passed.",
                'event_title' => $this->eventTitle,
                'approval_level' => $this->approvalLevel,
                'status' => $this->status,
                'url' => '/my-events?view=rejected',
            ];
        }

        if ($this->status === 'Rejected') {
            return [
                'title' => 'Facility Request Rejected',
                'message' => "Your facility request '{$this->eventTitle}' has been REJECTED by {$levelName}.",
                'event_title' => $this->eventTitle,
                'approval_level' => $this->approvalLevel,
                'status' => $this->status,
                'url' => '/events/my',
            ];
        }

        if ($this->fullyApproved || $this->approvalLevel >= $this->totalLevels) {
            return [
                'title' => 'Facility Request Fully Approved',
                'message' => "Your facility request '{$this->eventTitle}' has been FULLY APPROVED!",
                'event_title' => $this->eventTitle,
                'approval_level' => $this->approvalLevel,
                'status' => $this->status,
                'url' => '/events/my',
            ];
        }

        $nextLevel = $this->approvalLevel + 1;
        $nextLevelName = $this->nextApproverName ?: ($levelNames[$nextLevel] ?? 'Next Level');

        return [
            'title' => 'Facility Request Update',
            'message' => "Your facility request '{$this->eventTitle}' was approved by {$levelName}. Waiting for {$nextLevelName} review.",
            'event_title' => $this->eventTitle,
            'approval_level' => $this->approvalLevel,
            'status' => $this->status,
            'url' => '/events/my',
        ];
    }
}
