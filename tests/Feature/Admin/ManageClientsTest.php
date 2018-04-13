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

        // $this->admin = createUser('admin');
        // $this->client = createUser('client');

        // $this->tour = create('App\Tour', ['user_id' => $this->client->id]);
    }

    /** @test */
    public function an_admin_can_get_a_list_of_clients()
    {
        // $this->loginAs($this->admin);

        // $data = $this->json('get', route('admin.clients.index'))->getData();

        // dd($data);
        // ->assertStatus(200);
    }
}
