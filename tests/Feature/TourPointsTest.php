<?php

namespace Tests\Feature\Mobile;

use App\TourType;
use App\ScoreCard;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use App\Tour;
use App\TourStop;
use App\Device;

class TourPointsTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    /**
     * @var \App\Tour
     */
    protected $tour;

    /**
     * @var \App\User
     */
    protected $user;

    /**
     * @var \App\Device
     */
    protected $device;

    /**
     * @var Collection
     */
    protected $stops;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;
        $this->device = $this->user->devices()->create(factory(Device::class)->make()->toArray());

        $this->tour = factory(Tour::class)->states('published')->create([
            'pricing_type' => 'free',
            'type' => TourType::OUTDOOR
        ]);

        factory(TourStop::class, 5)->create([
            'tour_id' => $this->tour->id,
        ]);

        $this->stops = $this->tour->stops()->ordered()->get();
    }

    public function sendAnalytics($model, $action = 'start', $time = null)
    {
        if ($model instanceof Tour) {
            return $this->postJson("/mobile/tours/{$model->id}/track", [
                'activity' => [
                    [
                        'action' => $action,
                        'device_id' => $this->device->id,
                        'timestamp' => $time ?: strtotime('now'),
                    ],
                ],
            ])->assertStatus(200);
        } elseif ($model instanceof TourStop) {
            return $this->postJson("/mobile/stops/{$model->id}/track", [
                'activity' => [
                    [
                        'action' => $action,
                        'device_id' => $this->device->id,
                        'timestamp' => $time ?: strtotime('now'),
                    ],
                ],
            ])->assertStatus(200);
        }
    }

    /** @test */
    public function when_a_tour_is_started_it_should_set_the_total_number_of_stops()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start');

        $this->assertCount(1, $this->user->scoreCards()->get());

        $score = ScoreCard::for($this->tour, $this->user);

        $this->assertEquals($this->tour->stops()->count(), $score->total_stops);
    }

    /** @test */
    public function tours_started_twice_keep_the_same_score_card()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start');

        $this->assertCount(1, $this->user->scoreCards()->get());

        $score = ScoreCard::for($this->tour, $this->user);
        $score->update(['points' => 5]);

        $this->sendAnalytics($this->tour, 'start');

        $this->assertCount(1, $this->user->scoreCards()->get());
        $this->assertEquals(5, $score->fresh()->points);
    }

    /** @test */
    function tours_started_twice_should_always_update_the_total_number_of_stops()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start');

        $score = ScoreCard::for($this->tour, $this->user);

        $this->assertEquals(5, $score->total_stops);

        factory(TourStop::class)->create(['tour_id' => $this->tour->id]);

        $this->sendAnalytics($this->tour, 'start');

        $this->assertEquals(6, $score->fresh()->total_stops);
    }

    /** @test */
    function when_a_regular_tour_stop_is_visited_it_increases_and_returns_the_users_score()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start');

        $score = ScoreCard::for($this->tour, $this->user);

        $this->sendAnalytics($this->stops[0], 'stop');
        $this->assertEquals(1, $score->fresh()->stops_visited);

        $this->sendAnalytics($this->stops[1], 'stop')
            ->assertJsonFragment(['stops_visited' => 2]);

        $this->assertEquals(2, $score->fresh()->stops_visited);
    }

    /** @test */
    function when_a_user_visits_the_same_stop_twice_it_doesnt_increase_their_score()
    {
        $this->sendAnalytics($this->tour, 'start');
        $score = ScoreCard::for($this->tour, $this->user);

        $this->sendAnalytics($this->stops[0], 'stop');
        $this->assertEquals(1, $score->fresh()->stops_visited);

        $this->sendAnalytics($this->stops[0], 'stop')
            ->assertJsonFragment(['stops_visited' => 1]);

        $this->assertEquals(1, $score->fresh()->stops_visited);
    }

    /** @test */
    function when_a_user_visits_a_stop_it_calculates_if_they_get_a_trophy()
    {
        $this->sendAnalytics($this->tour, 'start');

        $this->sendAnalytics($this->stops[0], 'stop')
            ->assertJsonFragment(['won_trophy' => false]);

        $this->sendAnalytics($this->stops[1], 'stop')
            ->assertJsonFragment(['won_trophy' => false]);

        $this->sendAnalytics($this->stops[2], 'stop')
            ->assertJsonFragment(['won_trophy' => false]);

        $this->sendAnalytics($this->stops[3], 'stop')
            ->assertJsonFragment(['won_trophy' => true]);

        $this->sendAnalytics($this->stops[4], 'stop')
            ->assertJsonFragment(['won_trophy' => true]);
    }

    /** @test */
    function when_a_user_visits_a_stop_it_immediately_counts_towards_their_total_score()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start');

        $this->sendAnalytics($this->stops[0], 'stop')
            ->assertJsonFragment(['points' => 1]);

        $this->sendAnalytics($this->stops[0], 'stop')
            ->assertJsonFragment(['points' => 1]);

        $this->assertEquals(1, $this->user->fresh()->stats->points);

        $this->sendAnalytics($this->stops[1], 'stop')
            ->assertJsonFragment(['points' => 2]);

        $this->assertEquals(2, $this->user->fresh()->stats->points);
    }

    /** @test */
    function users_stats_should_be_updated_with_every_completed_stop()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start');

        $this->sendAnalytics($this->stops[0], 'stop')
            ->assertJsonFragment(['stops_visited' => 1]);

        $this->sendAnalytics($this->stops[0], 'stop')
            ->assertJsonFragment(['stops_visited' => 1]);

        $this->assertEquals(1, $this->user->fresh()->stats->stops_visited);

        $this->sendAnalytics($this->stops[1], 'stop')
            ->assertJsonFragment(['stops_visited' => 2]);

        $this->assertEquals(2, $this->user->fresh()->stats->stops_visited);
    }

    /** @test */
    function users_stats_should_be_updated_with_every_completed_tour()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start');

        $this->sendAnalytics($this->tour, 'stop');

        $this->assertEquals(1, $this->user->fresh()->stats->tours_completed);
    }

    /** @test */
    function users_completed_tour_stat_should_only_increase_on_unique_tours()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start');
        $this->sendAnalytics($this->tour, 'stop');

        $this->assertEquals(1, $this->user->fresh()->stats->tours_completed);

        $this->sendAnalytics($this->tour, 'start');
        $this->sendAnalytics($this->tour, 'stop');

        $this->assertEquals(1, $this->user->fresh()->stats->tours_completed);

        $otherTour = factory(Tour::class)->states('published')->create([
            'pricing_type' => 'free',
            'type' => TourType::OUTDOOR
        ]);


        $this->sendAnalytics($otherTour, 'start');
        $this->sendAnalytics($otherTour, 'stop');

        $this->assertEquals(2, $this->user->fresh()->stats->tours_completed);
    }
}
