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
}
