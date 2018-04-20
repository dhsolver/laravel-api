<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;

class ManageStopsTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $client;
    public $stop;

    public function setUp()
    {
        parent::setUp();

        $this->tour = create('App\Tour');
        $this->stop = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 1]);
    }

    public function stopRoute($name, $withStop = false)
    {
        $id = $this->stop->id;
        if (is_object($withStop)) {
            $id = $withStop->id;
        } elseif ($withStop === false) {
            $id = null;
        }

        return route("admin.stops.$name", [
            'tour' => $this->tour->id,
            'stop' => $id
        ]);
    }

    protected function updateStop($overrides = [])
    {
        $data = array_merge($this->stop->toArray(), $overrides);

        return $this->json('PATCH', $this->stopRoute('update', true), $data);
    }

    /** @test */
    public function an_admin_can_update_a_stop()
    {
        $this->signIn('admin');

        $data = [
            'title' => 'new title',
            'description' => 'new description',
        ];

        $data = $this->updateStop($data)
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function an_admin_can_add_a_stop_to_a_tour()
    {
        $this->signIn('admin');

        $stop = make('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 2]);

        $this->assertCount(1, $this->tour->stops);

        $this->json('POST', route('admin.stops.store', ['tour' => $this->tour->id]), $stop->toArray())
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $stop['title']]);

        $this->assertCount(2, $this->tour->fresh()->stops);
    }
}
