<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use App\Tour;
use App\Device;
use App\Action;
use App\Events\TourJoined;
use Event;

class JoinToursTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $tour;
    protected $otherTour;
    protected $user;
    protected $device;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;
        $this->device = $this->user->devices()->create(factory(Device::class)->make()->toArray());

        $this->tour = factory(Tour::class)->create(['pricing_type' => 'free']);
        $this->otherTour = factory(Tour::class)->create();
    }

    /** @test */
    public function a_user_can_join_a_free_tour()
    {
        $this->withoutExceptionHandling();

        $this->assertCount(0, $this->user->fresh()->joinedTours);

        $this->postJson("/mobile/tours/{$this->tour->id}/purchase", ['device_id' => $this->device->id])
            ->assertStatus(200);

        $this->assertCount(1, $this->user->fresh()->joinedTours);

        $this->assertCount(1, $this->tour->fresh()->participants);
    }

    /** @test */
    public function when_a_user_joins_a_tour_it_should_get_marked_as_downloaded()
    {
        $this->withoutExceptionHandling();

        $this->assertCount(0, $this->tour->activity);

        $this->postJson("/mobile/tours/{$this->tour->id}/purchase", ['device_id' => $this->device->id])
            ->assertStatus(200);

        $this->assertCount(1, $this->tour->activity()->where('action', Action::DOWNLOAD)->get());
    }

    /** @test */
    public function when_a_user_joins_a_tour_it_should_fire_an_event()
    {
        Event::fake();

        $this->postJson("/mobile/tours/{$this->tour->id}/purchase", ['device_id' => $this->device->id])
            ->assertStatus(200);

        Event::assertDispatched(TourJoined::class);
    }

    /** @test */
    public function a_user_can_get_a_list_of_all_their_joined_tours()
    {
        factory(Tour::class)->create(['pricing_type' => 'free'])->each(function ($tour) {
            $this->user->joinTour($tour);
        });

        $this->getJson('/mobile/tours/mine')
            ->assertStatus(200)
            ->assertJsonFragment(['data' => $this->user->joinedTours->pluck('id')]);
    }
}
