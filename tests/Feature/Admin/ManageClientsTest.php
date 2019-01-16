<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;

class ManageClientsTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $client;
    public $admin;

    public function setUp()
    {
        parent::setUp();

        $this->admin = createUser('admin');
        $this->loginAs($this->admin);

        // $this->tour = create('App\Tour', ['user_id' => $this->client->id]);
    }

    /** @test */
    public function an_admin_can_get_a_list_of_clients()
    {
        create('App\Client', [], 3);

        $data = $this->json('get', route('admin.clients.index'))
            ->assertJsonCount(3);
    }

    /** @test */
    public function an_admin_get_create_a_client()
    {
        $this->assertCount(0, \App\Client::all());

        $data = [
            'name' => 'Test Client',
            'email' => 'test@test.com',
            'zipcode' => '12345',
            'password' => 'password',
            'tour_limit' => 5,
        ];

        $this->json('post', route('admin.clients.store'), $data)
            ->assertStatus(200)
            ->assertJsonFragment(['email' => 'test@test.com'])
            ->assertJsonFragment(['role' => 'client'])
            ->assertJsonFragment(['zipcode' => '12345'])
            ->assertJsonFragment(['tour_limit' => 5]);

        $this->assertCount(1, \App\Client::all());
    }

    /** @test */
    public function an_admin_can_delete_a_client()
    {
        $client = createUser('client');

        $this->assertCount(1, \App\Client::all());

        $this->json('delete', route('admin.clients.destroy', ['client' => $client->id]))
            ->assertStatus(200);

        $this->assertCount(0, \App\Client::all());
    }

    /** @test */
    public function an_admin_can_update_a_client()
    {
        $client = createUser('client');

        $data = [
            'name' => 'New Name',
            'email' => 'newemail@test.com',
            'zipcode' => '12345',
            'tour_limit' => 5,
        ];

        $this->json('patch', route('admin.clients.update', ['client' => $client->id]), $data)
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function an_admin_can_view_a_single_client()
    {
        $client = createUser('client');

        $this->json('get', route('admin.clients.update', ['client' => $client->id]))
            ->assertStatus(200)
            ->assertJsonFragment($client->toArray());
    }

    /** @test */
    public function a_client_must_have_a_tour_limit()
    {
        $client = createUser('client');

        $data = [
            'name' => 'New Name',
            'email' => 'newemail@test.com',
            'zipcode' => '12345',
        ];

        $this->json('patch', route('admin.clients.update', ['client' => $client->id]), $data)
            ->assertStatus(422);
    }

    /** @test */
    public function an_admin_can_change_a_clients_role_to_user()
    {
        $user = createUser('client');
        $id = $user->id;

        $this->assertNull(\App\MobileUser::find($id));
        $this->assertEquals('client', $user->role);

        $this->json('patch', route('admin.change-role', ['user' => $id]), ['role' => 'user'])
            ->assertStatus(200);

        $this->assertEquals('user', \App\User::find($id)->role);
        $this->assertNull(\App\Client::find($id));
    }

    /** @test */
    public function an_admin_can_change_a_clients_role_to_admin()
    {
        $user = createUser('client');
        $id = $user->id;

        $this->assertNull(\App\Admin::find($id));
        $this->assertEquals('client', $user->role);

        $this->json('patch', route('admin.change-role', ['user' => $id]), ['role' => 'admin'])
            ->assertStatus(200);

        $this->assertEquals('admin', \App\User::find($id)->role);
        $this->assertNull(\App\Client::find($id));
    }

    /** @test */
    public function an_admin_can_disable_a_client()
    {
        $user = createUser('client');

        $this->assertEquals(1, $user->active);

        $this->json('patch', route('admin.deactivate-user', ['user' => $user->id]))
            ->assertStatus(200);

        $this->assertEquals(0, $user->fresh()->active);
    }

    /** @test */
    public function an_admin_can_reactivate_a_client()
    {
        $user = createUser('client');

        $user->user->deactivate();
        $this->assertEquals(0, $user->fresh()->active);

        $this->json('patch', route('admin.reactivate-user', ['user' => $user->id]))
            ->assertStatus(200);

        $this->assertEquals(1, $user->fresh()->active);
    }
}
