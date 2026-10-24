<?php

namespace Tests\Feature;

use App\Models\EventRequest;
use App\Models\User;
use App\Notifications\EventRequestApprovalNotification;
use App\Notifications\NewEventRequestNotification;
use Tests\TestCase;

class FacilityNotificationWordingTest extends TestCase
{
    public function test_each_approval_email_and_notification_uses_facility_wording_without_congratulations(): void
    {
        $user = new User(['name' => 'Franck Mercurio', 'email' => 'franck@example.com']);
        foreach ([['Expired', 1, false], ['Rejected', 2, false], ['Approved', 1, false], ['Approved', 4, true]] as [$status, $level, $complete]) {
            $notification = new EventRequestApprovalNotification('Conference Room - Oct 24, 2026', $level, $status,
                'Building Admin', 'School Administrator', 4, $complete);
            $mail = $notification->toMail($user);
            $text = implode(' ', [$mail->subject, $mail->greeting, ...$mail->introLines, ...$mail->outroLines]);
            $this->assertStringStartsWith('Facility Request', $mail->subject);
            $this->assertStringContainsString('facility request', $text);
            $this->assertDoesNotMatchRegularExpression('/\bevents?\b|Congratulations/i', $text);
            $this->assertSame('Hello Franck Mercurio!', $mail->greeting);
            $data = $notification->toArray($user);
            $this->assertStringStartsWith('Facility Request', $data['title']);
            $this->assertDoesNotMatchRegularExpression('/\bevents?\b/i', $data['message']);
            $this->assertSame('Conference Room - Oct 24, 2026', $data['event_title']);
            $this->assertStringContainsString($status === 'Expired' ? '/my-events' : '/events/my', $mail->actionUrl);
        }
    }

    public function test_approver_email_and_notification_use_facility_request_wording(): void
    {
        $requester = new User(['name' => 'Requester']);
        $request = new EventRequest(['location' => 'Conference Room', 'event_date' => '2026-10-24',
            'start_time' => '10:00', 'end_time' => '12:00', 'description' => 'Research workshop']);
        $request->setRelation('user', $requester);
        $notification = new NewEventRequestNotification($request);
        $recipient = new User(['name' => 'School Administrator']);
        $mail = $notification->toMail($recipient);
        $text = implode(' ', [$mail->subject, $mail->actionText, ...$mail->introLines, ...$mail->outroLines]);
        $this->assertStringStartsWith('New Facility Request', $mail->subject);
        $this->assertSame('Review Facility Request', $mail->actionText);
        $this->assertDoesNotMatchRegularExpression('/\bevents?\b/i', $text);
        $data = $notification->toArray($recipient);
        $this->assertSame('New Facility Request Pending', $data['title']);
        $this->assertDoesNotMatchRegularExpression('/\bevents?\b/i', $data['message']);
        $this->assertSame('/admin/events', $data['url']);
    }
}
