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

        factory(Tour::class, 10)->create();

        foreach (Tour::all() as $tour) {
            if (empty($this->tour)) {
                $this->tour = $tour;
            }
            factory(\App\TourStop::class, 3)->create(['tour_id' => $tour->id]);
            factory(\App\UserScore::class)->create([
                'user_id' => $this->user->id,
                'tour_id' => $tour->id,
            ]);
        }
    }

    /** @test */
    public function a_user_should_see_all_their_stats_in_their_profile()
    {
        $this->assertCount(10, $this->user->scores()->get());

        $this->getJson(route('mobile.profile.show', ['user' => $this->user]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'stats' => [
                    'completed_tours',
                    'points',
                    'stops_visited',
                    'trophies',
                ]
            ]);
    }

    /** @test */
    public function a_user_can_get_a_list_of_all_their_complete_tours_scores()
    {
        $this->withoutExceptionHandling();

        $this->assertCount(10, $this->user->scores()->get());

        $this->getJson(route('mobile.scores.index'))
            ->assertStatus(200)
            ->assertJsonCount(10);
    }

    /** @test */
    public function a_user_can_get_their_score_for_a_specific_tour()
    {
        $score = $this->user->scores()->forTour($this->tour)->first();

        $this->assertNotNull($score);

        $this->getJson(route('mobile.scores.show', ['tour' => $this->tour->id]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'tour_id' => $score->tour_id,
                'points' => (int) $score->points,
                'won_trophy' => $score->won_trophy,
            ]);
    }
}
