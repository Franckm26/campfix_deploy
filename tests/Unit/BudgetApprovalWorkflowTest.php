<?php

namespace Tests\Unit;

use App\Models\Concern;
use App\Models\Report;
use App\Models\User;
use App\Notifications\BudgetApprovalNotification;
use PHPUnit\Framework\TestCase;

class BudgetApprovalWorkflowTest extends TestCase
{
    public function test_work_cannot_progress_until_the_budget_is_approved(): void
    {
        $report = new Report(['budget_status' => Report::BUDGET_PENDING]);
        $concern = new Concern(['budget_status' => Report::BUDGET_PENDING]);

        $this->assertFalse($report->hasApprovedBudget());
        $this->assertFalse($concern->hasApprovedBudget());

        $report->budget_status = Report::BUDGET_REJECTED;
        $concern->budget_status = Report::BUDGET_REJECTED;
        $this->assertFalse($report->hasApprovedBudget());
        $this->assertFalse($concern->hasApprovedBudget());

        $report->budget_status = Report::BUDGET_APPROVED;
        $concern->budget_status = Report::BUDGET_APPROVED;
        $this->assertTrue($report->hasApprovedBudget());
        $this->assertTrue($concern->hasApprovedBudget());
    }

    public function test_budget_notification_identifies_the_report_and_amount(): void
    {
        $report = new Report([
            'title' => 'Repair laboratory air conditioner',
            'budget_amount' => 12500,
        ]);
        $report->id = 42;
        $report->concern_id = 24;

        $payload = (new BudgetApprovalNotification($report, 'requested'))->toArray(new User);

        $this->assertSame('budget_approval', $payload['type']);
        $this->assertSame('requested', $payload['action']);
        $this->assertSame(42, $payload['report_id']);
        $this->assertSame(24, $payload['concern_id']);
        $this->assertStringContainsString('PHP 12,500.00', $payload['message']);
    }
}
