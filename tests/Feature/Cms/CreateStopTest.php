<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\TourStop;

class CreateStopTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $client;
    public $stop;

    public function setUp()
    {
        parent::setUp();

        $this->client = createUser('client');

        $this->tour = create('App\Tour', ['user_id' => $this->client->id]);
        $this->stop = make(TourStop::class, ['tour_id' => $this->tour->id])->toArray();
    }

    public function publishStop()
    {
        return $this->json('POST', route('cms.stops.store', ['tour' => $this->tour->id]), $this->stop);
    }

    /** @test */
    public function a_stop_can_be_added_to_a_tour()
    {
        $this->loginAs($this->client);

        $this->publishStop()
            ->assertJsonFragment(['title' => $this->stop['title']]);

        $this->assertCount(1, TourStop::all());
    }

    /** @test */
    public function a_stop_can_only_by_added_by_the_tour_creator()
    {
        $this->signIn('client');

        $this->publishStop()->assertStatus(403);
    }

    /** @test */
    public function a_stop_requires_a_title()
    {
        $this->loginAs($this->client);

        unset($this->stop['title']);

        $this->publishStop()
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function indoor_tour_stops_should_not_require_locations()
    {
        $this->loginAs($this->client);

        $this->tour->update(['type' => 'indoor']);

        $data = [
            'title' => 'test',
            'description' => 'new stop',
            'location' => '',
        ];

        $this->json('POST', route('cms.stops.store', ['tour' => $this->tour->id]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->tour->fresh()->stops);
    }
}
