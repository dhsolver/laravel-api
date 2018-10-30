<?php

namespace Tests\Feature\Mobile;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use App\Tour;

class UserScoresTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $tour;
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;

        factory(\App\Tour::class, 10)->create();

        foreach (Tour::all() as $tour) {
            factory(\App\TourStop::class, 3)->create(['tour_id' => $tour->id]);
            factory(\App\UserScore::class)->create([
                'user_id' => $this->user->id,
                'tour_id' => $tour->id,
            ]);
        }
    }

    /** @test */
    public function a_user_can_should_see_all_thier_scores_in_their_profile()
    {
        $this->assertCount(10, $this->user->scores()->get());

        $this->getJson(route('mobile.profile.show', ['user' => $this->user]))
            ->assertStatus(200)
            ->assertJsonCount(10, 'scores')
            ->assertJsonFragment([
                'stats' => [
                    'completed_tours' => 10,
                    'points' => '2000',
                    'stops_visited' => 0,
                    'trophies' => 10,
                ]
            ]);
    }

    /** @test */
    public function a_user_should_see_all_their_stats_in_their_profile()
    {
        $this->assertCount(10, $this->user->scores()->get());

        $this->getJson(route('mobile.profile.show', ['user' => $this->user]))
            ->assertStatus(200)
            ->assertJsonCount(10, 'scores')
            ->assertJsonFragment([
                'stats' => [
                    'completed_tours' => 10,
                    'points' => '2000',
                    'stops_visited' => 0,
                    'trophies' => 10,
                ]
            ]);
    }
}
