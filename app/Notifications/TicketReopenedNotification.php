<?php

namespace App\Notifications;

use App\Models\Concern;
use App\Models\Report;
use Illuminate\Notifications\Notification;

class TicketReopenedNotification extends Notification
{
    public function __construct(public Concern|Report $ticket, public string $reopenedBy, public string $reason) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $type = $this->ticket instanceof Concern ? 'concern' : 'report';

        return [
            'title' => 'Ticket Reopened',
            'message' => $this->reopenedBy.' reopened "'.$this->ticket->title.'": '.$this->reason,
            $type.'_id' => $this->ticket->id,
            'status' => $this->ticket->status,
            'reason' => $this->reason,
            'url' => $notifiable->id === $this->ticket->user_id && $notifiable instanceof \App\Models\User
                ? '/'.$type.'s/'.$this->ticket->id : '/admin/reports',
        ];
    }
}
