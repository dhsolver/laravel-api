<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use App\Location;

class CreateToursTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    protected function publishTour($overrides = [])
    {
        $tour = make('App\Tour', $overrides);

        return $this->json('POST', route('cms.tours.store'), $tour->toArray());
    }

    /** @test */
    public function a_client_can_create_a_tour()
    {
        $this->signIn('client');

        $tour = make(Tour::class)->toArray();

        $this->json('POST', route('cms.tours.store'), $tour);

        $this->assertCount(1, Tour::all());
    }

    /** @test */
    public function an_admin_can_create_a_tour()
    {
        $this->signIn('admin');

        $tour = make(Tour::class)->toArray();

        $this->json('POST', route('cms.tours.store'), $tour)
            ->assertStatus(200);

        $this->assertCount(1, Tour::all());
    }

    /** @test */
    public function a_mobile_user_cannot_create_a_tour()
    {
        $this->signIn('user');

        $tour = make(Tour::class)->toArray();

        $this->json('POST', route('cms.tours.store'), $tour)
            ->assertStatus(403);
    }

    /** @test */
    public function when_a_tour_is_created_it_should_automatically_create_a_location_object()
    {
        $this->signIn('client');

        $tour = make(Tour::class)->toArray();

        $this->assertCount(0, Location::all());

        $this->json('POST', route('cms.tours.store'), $tour)
            ->assertStatus(200);

        $this->assertCount(1, Location::all());
    }

    /** @test */
    public function a_tour_must_have_a_title()
    {
        $this->signIn('client');

        $this->publishTour(['title' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');

        $this->assertCount(0, Tour::all());
    }

    /** @test */
    public function a_tour_must_have_a_valid_pricing_type()
    {
        $this->signIn('client');

        $this->publishTour(['pricing_type' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors('pricing_type');

        foreach (Tour::$PRICING_TYPES as $type) {
            $this->publishTour(['pricing_type' => $type])
            ->assertStatus(200);
        }

        $this->assertCount(count(Tour::$PRICING_TYPES), Tour::all());
    }

    /** @test */
    public function a_tour_must_have_a_valid_type()
    {
        $this->signIn('client');

        $this->publishTour(['type' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors('type');

        foreach (\App\TourType::all() as $type) {
            $this->publishTour(['type' => $type])
            ->assertStatus(200);
        }

        $this->assertCount(count(\App\TourType::all()), Tour::all());
    }

    /** @test */
    public function tour_titles_must_be_unique()
    {
        $this->signIn('client');

        $this->publishTour(['title' => 'test'])
            ->assertStatus(200);

        $this->assertCount(1, Tour::all());

        $this->publishTour(['title' => 'test'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertCount(1, Tour::all());
    }
}
