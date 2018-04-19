<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;

class ManageMobileUsersTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $user;
    public $admin;

    public function setUp()
    {
        parent::setUp();

        $this->admin = createUser('admin');
        $this->loginAs($this->admin);
    }

    /** @test */
    public function an_admin_can_get_a_list_of_users()
    {
        create('App\MobileUser', [], 3);

        $data = $this->json('get', route('admin.users.index'))
            ->assertJsonCount(3);
    }

    /** @test */
    public function an_admin_get_create_a_user()
    {
        $this->assertCount(0, \App\MobileUser::all());

        $data = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
        ];

        $this->json('post', route('admin.users.store'), $data)
            ->assertStatus(200)
            ->assertJsonFragment(['email' => 'test@test.com'])
            ->assertJsonFragment(['role' => 'user']);

        $this->assertCount(1, \App\MobileUser::all());
    }

    /** @test */
    public function an_admin_can_delete_a_user()
    {
        $user = createUser('user');

        $this->assertCount(1, \App\MobileUser::all());

        $this->json('delete', route('admin.users.destroy', ['user' => $user->id]))
            ->assertStatus(200);

        $this->assertCount(0, \App\MobileUser::all());
    }

    /** @test */
    public function an_admin_can_update_a_user()
    {
        $user = createUser('user');

        $data = [
            'name' => 'New Name',
            'email' => 'newemail@test.com',
        ];

        $this->json('patch', route('admin.users.update', ['user' => $user->id]), $data)
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function an_admin_can_view_a_single_user()
    {
        $user = createUser('user');

        $this->json('get', route('admin.users.update', ['user' => $user->id]))
            ->assertStatus(200)
            ->assertJsonFragment($user->toArray());
    }
}
