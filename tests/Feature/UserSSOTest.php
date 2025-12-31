<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSSOTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_or_create_from_sso_creates_new_user_if_not_exists()
    {
        $ssoUser = (object) [
            'sub' => 'sso-123',
            'name' => 'SSO User',
            'email' => 'sso@example.com',
            'avatar_url' => 'https://example.com/avatar.png'
        ];

        $user = User::findOrCreateFromSSO($ssoUser);

        $this->assertDatabaseHas('users', [
            'sso_id' => 'sso-123',
            'email' => 'sso@example.com',
            'name' => 'SSO User'
        ]);
        $this->assertEquals('sso-123', $user->sso_id);
    }

    public function test_find_or_create_from_sso_updates_existing_user_by_sso_id()
    {
        $user = User::factory()->create([
            'sso_id' => 'sso-123',
            'email' => 'old@example.com',
            'name' => 'Old Name'
        ]);

        $ssoUser = (object) [
            'sub' => 'sso-123',
            'name' => 'New Name',
            'email' => 'new@example.com'
        ];

        User::findOrCreateFromSSO($ssoUser);

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
    }

    public function test_find_or_create_from_sso_updates_existing_user_by_email()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'name' => 'Initial Name',
            'sso_id' => null
        ]);

        $ssoUser = (object) [
            'id' => 'sso-456',
            'name' => 'Updated Name',
            'email' => 'user@example.com'
        ];

        User::findOrCreateFromSSO($ssoUser);

        $user->refresh();
        $this->assertEquals('sso-456', $user->sso_id);
        $this->assertEquals('Updated Name', $user->name);
    }
}
