<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;

class ManageToursTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    /** @test */
    public function a_user_with_any_role_can_log_in()
    {
        $this->withExceptionHandling();

        $this->assertRoleLogsIn('user');
        $this->assertRoleLogsIn('admin');
        $this->assertRoleLogsIn('superadmin');
        $this->assertRoleLogsIn('business');
    }

    public function assertRoleLogsIn($role)
    {
        $user = createUser($role);

        $this->json('POST', '/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ])->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'role'], 'token']);
    }

    /** @test */
    public function a_user_cannot_access_the_cms()
    {
        $this->withExceptionHandling();

        $this->signIn('user')
            ->json('GET', '/cms/tours')
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_and_business_cannot_access_admin_panel()
    {
        $this->withExceptionHandling();

        $this->signIn('user')->json('GET', '/admin')->assertStatus(403);
        $this->signIn('business')->json('GET', '/admin')->assertStatus(403);
    }
}
