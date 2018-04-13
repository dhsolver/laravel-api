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

        // $this->tour = create('App\Tour', ['user_id' => $this->client->id]);
    }

    /** @test */
    public function an_admin_can_get_a_list_of_clients()
    {
        $this->loginAs($this->admin);

        create('App\Client', [], 3);

        $data = $this->json('get', route('admin.clients.index'))
            ->assertJsonCount(3);
    }
}
