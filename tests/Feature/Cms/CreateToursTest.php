<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;

class CreateToursTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    /** @test */
    public function a_user_can_create_a_tour()
    {
        $this->signIn('client');

        $tour = make(Tour::class)->toArray();

        $this->json('POST', route('cms.tours.store'), $tour);

        $this->assertCount(1, Tour::all());
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
    public function a_tour_must_have_a_description()
    {
        $this->signIn('client');

        $this->publishTour(['description' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors('description');

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
            ->assertStatus(201);
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

        foreach (Tour::$TOUR_TYPES as $type) {
            $this->publishTour(['type' => $type])
            ->assertStatus(201);
        }

        $this->assertCount(count(Tour::$TOUR_TYPES), Tour::all());
    }

    protected function publishTour($overrides = [])
    {
        $tour = make('App\Tour', $overrides);

        return $this->json('POST', route('cms.tours.store'), $tour->toArray());
    }
}
