<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\TourStop;
use App\Tour;

class CreateStopTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $business;
    public $stop;

    public function setUp()
    {
        parent::setUp();

        $this->business = createUser('business');

        $this->tour = create('App\Tour', ['user_id' => $this->business->id]);
        $this->stop = make(TourStop::class, ['tour_id' => $this->tour->id])->toArray();
    }

    public function publishStop()
    {
        return $this->json('POST', route('cms.stop.store', ['tour' => $this->tour->id]), $this->stop);
    }

    /** @test */
    public function a_stop_can_be_added_to_a_tour()
    {
        $this->loginAs($this->business);

        $this->publishStop()->assertSee($this->stop['title']);

        $this->assertCount(1, TourStop::all());
    }

    /** @test */
    public function only_the_tour_owner_can_add_a_stop()
    {
        $this->signIn('business');

        $this->publishStop()->assertStatus(403);
    }

    /** @test */
    public function a_stop_requires_a_title_description_and_type()
    {
        $this->loginAs($this->business);

        unset($this->stop['title'], $this->stop['description'], $this->stop['location_type']);

        $this->publishStop()
            ->assertStatus(422)
            ->assertSee('title')
            ->assertSee('description')
            ->assertSee('location_type');
    }
}
