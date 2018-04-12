<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;

class AuthTest extends TestCase
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
            ->assertJson([
                'user' => [
                    'email' => $user->email,
                    'role' => $role
                ],
            ]);
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
    public function a_superadmin_can_access_all_sections()
    {
        $this->withExceptionHandling();

        $this->signIn('superadmin')->json('GET', '/admin/session')->assertStatus(200);
        $this->signIn('superadmin')->json('GET', '/cms/session')->assertStatus(200);
        $this->signIn('superadmin')->json('GET', '/mobile/session')->assertStatus(200);
    }

    /** @test */
    public function an_admin_can_access_all_sections()
    {
        $this->withExceptionHandling();

        $this->signIn('admin')->json('GET', '/admin/session')->assertStatus(200);
        $this->signIn('admin')->json('GET', '/cms/session')->assertStatus(200);
        $this->signIn('admin')->json('GET', '/mobile/session')->assertStatus(200);
    }

    /** @test */
    public function a_user_can_only_access_the_mobile_api()
    {
        $this->withExceptionHandling();

        $this->signIn('user')->json('GET', '/admin/session')->assertStatus(403);
        $this->signIn('user')->json('GET', '/cms/session')->assertStatus(403);
        $this->signIn('user')->json('GET', '/mobile/session')->assertStatus(200);
    }

    /** @test */
    public function a_business_can_access_the_cms_and_the_mobile_api()
    {
        $this->withExceptionHandling();

        $this->signIn('business')->json('GET', '/admin/session')->assertStatus(403);
        $this->signIn('business')->json('GET', '/cms/session')->assertStatus(200);
        $this->signIn('business')->json('GET', '/mobile/session')->assertStatus(200);
    }

    /** @test */
    public function a_guest_cant_access_shit()
    {
        $this->withExceptionHandling();

        $this->json('GET', '/admin/session')->assertStatus(400);
        $this->json('GET', '/cms/session')->assertStatus(400);
        $this->json('GET', '/mobile/session')->assertStatus(400);
    }
}
