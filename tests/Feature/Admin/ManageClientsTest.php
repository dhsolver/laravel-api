<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;

class ManageClientsTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $business;
    public $admin;

    public function setUp()
    {
        parent::setUp();

        $this->admin = createUser('admin');
        $this->business = createUser('business');

        $this->tour = create('App\Tour', ['user_id' => $this->business->id]);
    }

    /**
     * Helper to provide route to the class tour based on named routes.
     *
     * @param String $name
     * @return void
     */
    public function tourRoute($name)
    {
        return route("cms.tours.$name", $this->tour->id);
    }

    /** @test */
    public function an_admin_can_get_a_list_of_clients()
    {
        $this->loginAs($this->admin);

        $data = $this->json('get', route('admin.clients.index'))->getData();

        dd($data);
        // ->assertStatus(200);
    }
}
