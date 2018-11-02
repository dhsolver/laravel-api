<?php

namespace Tests\Feature\Mobile;

use App\TourStop;
use App\TourType;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use App\Tour;
use App\ScoreCard;

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

        factory(Tour::class, 10)->create(['type' => TourType::ADVENTURE]);

        foreach (Tour::all() as $tour) {
            if (empty($this->tour)) {
                $this->tour = $tour;
            }
            factory(TourStop::class, 3)->create(['tour_id' => $tour->id]);
            factory(ScoreCard::class)->create([
                'user_id' => $this->user->id,
                'tour_id' => $tour->id,
            ]);
        }
    }

    /** @test */
    public function a_user_should_see_all_their_stats_in_their_profile()
    {
        $this->assertCount(10, $this->user->scoreCards()->get());

        $this->getJson(route('mobile.profile.show', ['user' => $this->user]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'stats' => [
                    'tours_completed',
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

        $this->assertCount(10, $this->user->scoreCards()->get());

        $this->getJson(route('mobile.scores.index'))
            ->assertStatus(200)
            ->assertJsonCount(10);
    }

    /** @test */
    public function a_user_can_get_their_score_for_a_specific_tour()
    {
        $score = ScoreCard::for($this->tour, $this->user);

        $this->assertNotNull($score);

        $this->getJson(route('mobile.scores.show', ['tour' => $this->tour->id]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'tour_id' => $score->tour_id,
                'points' => (int) $score->points,
                'won_trophy' => $score->won_trophy,
            ]);
    }

    /** @test */
    function a_users_score_list_should_include_unfinished_regular_tours()
    {
        $tour = factory(Tour::class)->create(['type' => TourType::OUTDOOR]);
        factory(TourStop::class, 3)->create(['tour_id' => $tour->id]);
        factory(ScoreCard::class)->create([
            'user_id' => $this->user->id,
            'tour_id' => $tour->id,
            'stops_visited' => 2,
            'points' => 2,
        ]);

        $this->getJson(route('mobile.scores.index'))
            ->assertStatus(200)
            ->assertJsonCount(11)
            ->assertJsonFragment([
                'tour_id' => (string) $tour->id,
                'points' => 2,
            ]);
    }
}
