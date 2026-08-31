<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class MisUserManagementAccessTest extends TestCase
{
    public function test_mis_user_can_manage_the_account_that_created_them(): void
    {
        $creator = new User(['role' => 'mis']);
        $creator->id = 10;

        $misUser = new User(['role' => 'mis', 'created_by' => $creator->id]);
        $misUser->id = 20;

        $this->assertFalse($creator->isProtectedFrom($misUser));
        $this->assertFalse($misUser->isProtectedFrom($creator));
    }

    public function test_user_role_has_a_readable_display_name(): void
    {
        $mis = new User(['role' => 'mis']);
        $programHead = new User(['role' => 'program_head']);
        $principal = new User(['role' => 'principal_assistant']);

        $this->assertSame('MIS', $mis->role_display_name);
        $this->assertSame('Program Head', $programHead->role_display_name);
        $this->assertSame('SHS Principal', $principal->role_display_name);
    }
}
