<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_has_role_returns_true_when_role_matches()
    {
        $user = new User();
        $user->role = 'admin';

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_has_role_returns_false_when_role_does_not_match()
    {
        $user = new User();
        $user->role = 'user';

        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_is_sso_user_returns_true_when_sso_id_is_present()
    {
        $user = new User();
        $user->sso_id = '12345';

        $this->assertTrue($user->isSSOUser());
    }

    public function test_is_sso_user_returns_false_when_sso_id_is_empty()
    {
        $user = new User();
        $user->sso_id = null;

        $this->assertFalse($user->isSSOUser());
    }
}
