<?php

namespace Tests\Unit;

use App\Models\EventRequest;
use PHPUnit\Framework\TestCase;

class EventRequestApprovalRouteTest extends TestCase
{
    public function test_configured_route_uses_sequential_roles_in_saved_order(): void
    {
        $event = new EventRequest([
            'status' => EventRequest::STATUS_PENDING,
            'approval_route' => ['principal_assistant', 'academic_head', 'school_admin'],
            'approval_history' => [],
        ]);

        $this->assertSame(1, $event->getNextApprovalLevel());
        $this->assertSame('principal_assistant', $event->requiredApprovalRole());

        $event->approval_history = [['level' => 1, 'approver_id' => 10]];

        $this->assertSame(2, $event->getNextApprovalLevel());
        $this->assertSame('academic_head', $event->requiredApprovalRole());
    }

    public function test_configured_route_is_complete_after_every_step_is_recorded(): void
    {
        $event = new EventRequest([
            'status' => EventRequest::STATUS_PENDING,
            'approval_route' => ['building_admin', 'school_admin'],
            'approval_history' => [
                ['level' => 1, 'approver_id' => 10],
                ['level' => 2, 'approver_id' => 11],
            ],
        ]);

        $this->assertNull($event->getNextApprovalLevel());
        $this->assertNull($event->requiredApprovalRole());
        $this->assertTrue($event->isFullyApproved());
    }
}
