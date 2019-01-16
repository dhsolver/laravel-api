<?php

namespace Tests\Feature\Mobile;

use App\TourStop;
use App\TourType;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use App\Tour;
use App\ScoreCard;
use Tests\HasTestTour;

class UserScoresTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken, HasTestTour;

    protected $tour;
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;

        factory(Tour::class, 10)->create(['type' => TourType::ADVENTURE]);
        foreach (Tour::all() as $tour) {
            factory(TourStop::class, 3)->create(['tour_id' => $tour->id]);
            factory(ScoreCard::class)->create([
                'user_id' => $this->user->id,
                'tour_id' => $tour->id,
            ]);
        }

        list($this->tour, $this->stops) = $this->createTestAdventure(false);
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
    public function a_users_score_list_should_include_unfinished_regular_tours()
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

    /** @test */
    public function a_user_can_fetch_all_their_scorecards_for_a_tour()
    {
        $this->assertCount(10, $this->user->fresh()->scoreCards);

        $this->insertStopRouteData($this->tour);

        $this->startTour(strtotime('100 minutes ago'));

        $this->visitStop($this->tour->end_point_id);

        $this->startTour(strtotime('30 minutes ago'));
        $bestScore = $this->score;
        $this->visitStop($this->tour->end_point_id);

        $this->startTour();
        $this->assertCount(13, $this->user->fresh()->scoreCards);

        $response = $this->getJson(route('mobile.scores.find', ['tour' => $this->tour]))
            ->assertStatus(200)
            ->assertJsonStructure(['best', 'finished', 'in_progress'])
            ->assertJsonCount(2, 'finished')
            ->assertJsonCount(1, 'in_progress');

        $this->assertEquals($bestScore->fresh()->id, $response->decodeResponseJson()['best']['id']);
    }
}
