<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;

class ManageAdminsTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $admin;

    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function a_superadmin_can_get_a_list_of_admins()
    {
        $this->signIn('superadmin');

        create('App\Admin', [], 3);

        $data = $this->json('get', route('admin.admins.index'))
            ->assertJsonCount(3);
    }

    /** @test */
    public function a_superadmin_get_create_a_admin()
    {
        $this->signIn('superadmin');

        $this->assertCount(0, \App\Admin::all());

        $data = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'zipcode' => '12345',
            'password' => 'password',
        ];

        $this->json('post', route('admin.admins.store'), $data)
            ->assertStatus(200)
            ->assertJsonFragment(['email' => 'test@test.com'])
            ->assertJsonFragment(['role' => 'admin'])
            ->assertJsonFragment(['zipcode' => '12345']);

        $this->assertCount(1, \App\Admin::all());
    }

    /** @test */
    public function a_superadmin_can_delete_a_admin()
    {
        $this->signIn('superadmin');

        $admin = createUser('admin');

        $this->assertCount(1, \App\Admin::all());

        $this->json('delete', route('admin.admins.destroy', ['admin' => $admin->id]))
            ->assertStatus(200);

        $this->assertCount(0, \App\Admin::all());
    }

    /** @test */
    public function a_superadmin_can_update_a_admin()
    {
        $this->signIn('superadmin');

        $admin = createUser('admin');

        $data = [
            'name' => 'New Name',
            'email' => 'newemail@test.com',
            'zipcode' => '12345',
        ];

        $this->json('patch', route('admin.admins.update', ['admin' => $admin->id]), $data)
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function a_superadmin_can_view_a_single_admin()
    {
        $this->signIn('superadmin');

        $admin = createUser('admin');

        $this->json('get', route('admin.admins.update', ['admin' => $admin->id]))
            ->assertStatus(200)
            ->assertJsonFragment($admin->toArray());
    }

    /** @test */
    public function a_normal_admin_cannot_modify_admins()
    {
        $this->signIn('admin');

        $admin = createUser('admin');

        $this->json('patch', route('admin.admins.update', ['admin' => $admin->id]), $admin->toArray())
            ->assertStatus(403);

        $this->json('delete', route('admin.admins.destroy', ['admin' => $admin->id]))
            ->assertStatus(403);
    }

    /** @test */
    public function a_normal_admin_cannot_add_admins()
    {
        $this->signIn('admin');

        $data = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
        ];

        $this->json('post', route('admin.admins.store'), $data)
            ->assertStatus(403);
    }

    /** @test */
    public function an_admin_can_change_an_admins_role_to_client()
    {
        $this->signIn('admin');

        $user = createUser('admin');
        $id = $user->id;

        $this->assertNull(\App\Client::find($id));
        $this->assertEquals('admin', $user->role);

        $this->json('patch', route('admin.change-role', ['user' => $id]), ['role' => 'client'])
            ->assertStatus(200);

        $this->assertEquals('client', \App\User::find($id)->role);
        $this->assertNull(\App\Admin::find($id));
    }

    /** @test */
    public function an_admin_can_change_an_admins_role_to_user()
    {
        $this->signIn('admin');

        $user = createUser('admin');
        $id = $user->id;

        $this->assertNull(\App\MobileUser::find($id));
        $this->assertEquals('admin', $user->role);

        $this->json('patch', route('admin.change-role', ['user' => $id]), ['role' => 'user'])
            ->assertStatus(200);

        $this->assertEquals('user', \App\User::find($id)->role);
        $this->assertNull(\App\Admin::find($id));
    }

    /** @test */
    public function an_admin_can_disable_a_admin()
    {
        $this->signIn('admin');
        $user = createUser('admin');

        $this->assertEquals(1, $user->active);

        $this->json('patch', route('admin.deactivate-user', ['user' => $user->id]))
            ->assertStatus(200);

        $this->assertEquals(0, $user->fresh()->active);
    }

    /** @test */
    public function an_admin_can_reactivate_a_admin()
    {
        $this->signIn('admin');
        $user = createUser('admin');

        $user->user->deactivate();
        $this->assertEquals(0, $user->fresh()->active);

        $this->json('patch', route('admin.reactivate-user', ['user' => $user->id]))
            ->assertStatus(200);

        $this->assertEquals(1, $user->fresh()->active);
    }
}
