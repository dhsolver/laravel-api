<?php

namespace Tests\Feature\Mobile;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use Tests\HasTestTour;
use App\ScoreCard;
use App\Points\AdventureCalculator;

class TrackAdventuresTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken, HasTestTour;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;

        $this->withoutExceptionHandling();
        list($this->tour, $this->stops) = $this->createTestAdventure(true);
    }

    /** @test */
    public function when_a_user_starts_a_tour_it_creates_a_fresh_score_card()
    {
        $timestamp = strtotime('yesterday');
        $createdAt = Carbon::createFromTimestampUTC($timestamp);

        $this->assertCount(0, $this->user->scoreCards);

        $data = [
            'tour_id' => $this->tour->id,
            'timestamp' => $timestamp,
        ];

        $this->postJson(route('mobile.scores.start'), $data)
            ->assertJsonFragment([
                'tour_id' => $this->tour->id,
                'is_adventure' => true,
                'par' => $this->tour->calculator()->getPar(),
                'total_stops' => $this->tour->calculator()->getTotalStops(),
                'stops_visited' => 0,
                'started_at' => $createdAt->toDateTimeString(),
            ])->assertStatus(200);

        $this->assertCount(1, $this->user->fresh()->scoreCards);
    }

    /** @test */
    public function a_user_can_back_date_the_start_time_for_a_tour()
    {
        $timestamp = strtotime('yesterday');
        $this->startTour($timestamp)
            ->assertStatus(200);

        $this->assertEquals(Carbon::createFromTimestampUTC($timestamp), $this->score->fresh()->started_at);
    }

    /** @test */
    public function a_user_cannot_start_a_tour_with_a_future_timestamp()
    {
        $this->startTour(strtotime('tomorrow'))
            ->assertStatus(200);

        $this->assertLessThan(Carbon::now(), $this->score->fresh()->started_at);
    }

    /** @test */
    public function a_user_can_back_date_the_visit_time_of_a_stop()
    {
        $timestamp = strtotime('yesterday');
        $this->startTour()->assertStatus(200);

        $this->visitStop($this->tour->start_point_id, $timestamp)
            ->assertStatus(200);

        $date = Carbon::createFromTimestampUTC($timestamp)->toDateTimeString();
        $this->assertEquals($date, $this->score->fresh()->stops->first()->pivot->visited_at);
    }

    /** @test */
    public function a_user_cannot_visit_a_stop_in_the_future()
    {
        $timestamp = strtotime('tomorrow');
        $this->startTour()->assertStatus(200);

        $this->visitStop($this->tour->start_point_id, $timestamp)
            ->assertStatus(200);

        $this->assertLessThan(Carbon::now(), $this->score->fresh()->stops->first()->pivot->visited_at);
    }

    /** @test */
    public function when_a_user_visits_a_stop_it_should_reflect_on_their_score_card()
    {
        $startTime = strtotime('30 minutes ago');

        $this->startTour($startTime)->assertStatus(200);

        $this->assertEquals(0, $this->score->stops_visited);

        $this->visitStop($this->tour->start_point_id)
            ->assertStatus(200)
            ->assertJsonFragment(['stops_visited' => 1]);

        $this->assertEquals(1, $this->score->fresh()->stops_visited);
    }

    /** @test */
    public function a_user_cannot_progress_another_users_score_card()
    {
        $this->startTour()->assertStatus(200);

        $otherUser = create(\App\User::class);
        $otherTour = create(\App\Tour::class);
        $card = factory(ScoreCard::class)->create(['user_id' => $otherUser->id, 'tour_id' => $otherTour->id, 'stops_visited' => 0]);

        $this->assertCount(2, ScoreCard::all());

        $this->visitStop($this->tour->start_point_id, strtotime('now'), $card)
            ->assertStatus(401);

        $this->assertEquals(0, $card->fresh()->stops_visited);
        $this->assertEquals(0, $card->fresh()->stops_visited);
    }

    /** @test */
    public function a_user_cannot_progress_a_score_card_with_a_stop_from_another_tour()
    {
        $this->startTour()->assertStatus(200);

        $otherUser = create(\App\User::class);
        $otherTour = create(\App\Tour::class);
        $stop = create(\App\TourStop::class, ['tour_id' => $otherTour]);

        $this->visitStop($stop)
            ->assertStatus(422);

        $this->assertEquals(0, $this->score->fresh()->stops_visited);
    }

    /** @test */
    public function when_a_user_visits_the_last_stop_on_the_tour_it_should_calculate_their_score()
    {
        $startTime = strtotime('30 minutes ago');

        $this->startTour($startTime);
        $score = $this->user->scoreCards()->first();

        $stopTime = strtotime('1 minute ago');

        $this->visitStop($this->tour->end_point_id, $stopTime)
            ->assertStatus(200)
            ->assertJsonFragment(['won_trophy' => true])
            ->assertJsonFragment(['points' => 200]);

        $this->assertEquals(Carbon::createFromTimestampUTC($stopTime), $this->score->fresh()->finished_at);

        $this->assertEquals(29, $score->fresh()->duration);

        $ac = new AdventureCalculator($this->tour);
        $this->assertEquals($ac->calculatePoints(29), $score->fresh()->points);
    }

    /** @test */
    public function if_a_tour_par_changes_since_the_user_started_it_would_not_affect_their_score()
    {
        $startTime = strtotime('30 minutes ago');

        $this->startTour($startTime);
        $score = $this->user->scoreCards()->first();
        $score->update(['par' => 15]);
        $score = $score->fresh();

        $stopTime = strtotime('1 minute ago');

        $this->visitStop($this->tour->end_point_id, $stopTime)
            ->assertStatus(200)
            ->assertJsonFragment(['won_trophy' => true]);

        $this->assertEquals(Carbon::createFromTimestampUTC($stopTime), $this->score->fresh()->finished_at);

        $this->assertEquals(29, $score->fresh()->duration);

        $ac = new AdventureCalculator($this->tour);
        $this->assertEquals(170, $score->fresh()->points);
    }

    /** @test */
    public function when_a_user_gets_a_high_enough_score_they_are_awarded_a_trophy()
    {
        $startTime = strtotime('30 minutes ago');
        $stopTime = strtotime('now');

        $this->startTour($startTime);
        $score = $this->user->scoreCards()->first();
        $this->visitStop($this->tour->end_point_id, $stopTime)
            ->assertStatus(200)
            ->assertJsonFragment(['won_trophy' => true]);

        $this->assertTrue($score->fresh()->won_trophy);
    }

    /** @test */
    public function when_a_users_score_doesnt_reach_the_threshold_they_do_not_win_a_trophy()
    {
        $startTime = strtotime('100 minutes ago');
        $stopTime = strtotime('now');

        $this->startTour($startTime);
        $score = $this->user->scoreCards()->first();
        $this->visitStop($this->tour->end_point_id, $stopTime)
            ->assertStatus(200)
            ->assertJsonFragment(['won_trophy' => false]);

        $this->assertFalse($score->fresh()->won_trophy);
    }

    /** @test */
    public function when_a_user_wins_a_trophy_it_can_include_a_prize()
    {
        $startTime = strtotime('30 minutes ago');
        $stopTime = strtotime('now');

        $this->startTour($startTime);
        $response = $this->visitStop($this->tour->end_point_id, $stopTime)
            ->assertStatus(200);
        $score = $this->user->scoreCards()->first();

        $this->assertNotNull($score->prize_expires_at);

        $response->assertJsonFragment(['prize' => [
            'details' => $this->tour->prize_details,
            'instructions' => $this->tour->prize_instructions,
            'expires_at' => $score->prize_expires_at->toDateTimeString(),
            'time_limit' => $this->tour->prize_time_limit,
        ]]);
    }

    /** @test */
    public function when_a_tour_is_started_it_should_set_the_total_number_of_stops()
    {
        $this->startTour();

        $this->assertCount(1, $this->signInUser->user->scoreCards);

        $score = $this->user->scoreCards()->first();

        $this->assertEquals($this->tour->stops()->count(), $score->total_stops);
    }

    /** @test */
    public function multiple_score_cards_can_be_created_without_finishing_a_tour()
    {
        $this->startTour();

        $this->assertCount(1, $this->signInUser->user->scoreCards);

        $this->startTour();

        $this->assertCount(2, $this->signInUser->user->fresh()->scoreCards);
    }

    /** @test */
    public function a_users_total_points_includes_all_finished_tours()
    {
        $this->assertEquals(0, $this->user->fresh()->stats->points);
        $this->startTour();
        $this->visitStop($this->tour->end_point_id);
        $this->assertEquals(200, $this->user->fresh()->stats->points);

        list($otherTour, $stops) = $this->createTestAdventure(true);
        $this->startTour(strtotime('100 minutes ago'), $otherTour);
        $this->visitStop($otherTour->end_point_id);

        $this->assertEquals(302, $this->user->fresh()->stats->points);
    }

    /** @test */
    public function a_users_total_points_doesnt_include_unfinished_tours()
    {
        $this->assertEquals(0, $this->user->fresh()->stats->points);
        $this->startTour();
        $this->visitStop($this->tour->end_point_id);
        $this->assertEquals(200, $this->user->fresh()->stats->points);

        list($otherTour, $stops) = $this->createTestAdventure(true);
        $this->startTour(strtotime('100 minutes ago'), $otherTour);

        $this->assertEquals(200, $this->user->fresh()->stats->points);
    }

    /** @test */
    public function a_users_total_points_only_includes_the_best_score_for_a_single_tour()
    {
        $this->assertEquals(0, $this->user->fresh()->stats->points);

        $this->startTour(strtotime('100 minutes ago'));
        $this->visitStop($this->tour->end_point_id);
        $this->assertEquals(102, $this->user->fresh()->stats->points);

        $this->startTour(strtotime('30 minutes ago'));
        $this->visitStop($this->tour->end_point_id);
        $this->assertEquals(200, $this->user->fresh()->stats->points);
    }

    /** @test */
    public function when_a_user_unlocks_an_adventure_trophy_their_stats_automatically_update()
    {
        $this->startTour(strtotime('30 minutes ago'));

        $this->assertEquals(0, $this->user->fresh()->stats->trophies);

        $this->visitStop($this->tour->end_point_id);

        $this->assertTrue($this->score->fresh()->won_trophy);

        $this->assertEquals(1, $this->user->fresh()->stats->trophies);
    }

    /** @test */
    public function a_user_can_only_unlock_a_trophy_once_per_tour()
    {
        $this->startTour(strtotime('30 minutes ago'));
        $this->assertEquals(0, $this->user->fresh()->stats->trophies);
        $this->visitStop($this->tour->end_point_id);
        $this->assertTrue($this->score->fresh()->won_trophy);
        $this->assertEquals(1, $this->user->fresh()->stats->trophies);

        $this->startTour(strtotime('30 minutes ago'));
        $this->visitStop($this->tour->end_point_id);
        $this->assertTrue($this->score->fresh()->won_trophy);
        $this->assertCount(2, $this->user->fresh()->scoreCards);
        $this->assertEquals(1, $this->user->fresh()->stats->trophies);
    }

    /** @test */
    public function once_a_score_card_is_complete_it_cant_be_updated()
    {
        $this->startTour(strtotime('30 minutes ago'));
        $this->visitStop($this->tour->end_point_id)
            ->assertStatus(200);
        $this->assertNotNull($this->score->fresh()->finished_at);

        $this->visitStop($this->tour->end_point_id)
            ->assertStatus(403);
    }

    /** @test */
    public function when_a_user_visits_a_stop_their_stats_should_increase()
    {
        $this->startTour();
        $this->assertEquals(0, $this->user->fresh()->stats->stops_visited);

        $this->visitStop($this->stops[0])
            ->assertStatus(200);
        $this->assertEquals(1, $this->user->fresh()->stats->stops_visited);

        $this->visitStop($this->stops[1])
            ->assertStatus(200);

        $this->assertEquals(2, $this->user->fresh()->stats->stops_visited);

        $this->visitStop($this->stops[2])
            ->assertStatus(200);
        $this->assertEquals(3, $this->user->fresh()->stats->stops_visited);
    }

    /** @test */
    public function a_user_can_only_visit_a_stop_once_for_each_score_card()
    {
        $this->startTour();
        $this->assertEquals(0, $this->user->fresh()->stats->stops_visited);

        $this->visitStop($this->stops[0])
            ->assertStatus(200);

        $this->assertEquals(1, $this->score->fresh()->stops_visited);

        $this->visitStop($this->stops[1])
            ->assertStatus(200);

        $this->assertEquals(2, $this->score->fresh()->stops_visited);

        $this->visitStop($this->stops[0])
            ->assertStatus(200);

        $this->assertEquals(2, $this->score->fresh()->stops_visited);
    }

    /** @test */
    public function when_an_adventure_is_taken_twice_it_should_only_count_their_total_stops_once()
    {
        $this->startTour();
        $this->assertEquals(0, $this->user->fresh()->stats->stops_visited);

        $this->visitStop($this->stops[0])
            ->assertStatus(200);
        $this->assertEquals(1, $this->user->fresh()->stats->stops_visited);

        $this->visitStop($this->stops[1])
            ->assertStatus(200);

        $this->assertEquals(2, $this->user->fresh()->stats->stops_visited);

        $this->startTour(strtotime('now'));

        $this->visitStop($this->stops[3])
            ->assertStatus(200);

        $this->assertEquals(3, $this->user->fresh()->stats->stops_visited);

        $this->visitStop($this->stops[1])
            ->assertStatus(200);

        $this->assertEquals(3, $this->user->fresh()->stats->stops_visited);
    }

    /** @test */
    public function a_users_progress_should_keep_track_if_they_skip_a_question()
    {
        $this->startTour()->assertStatus(200);

        $this->assertEquals(0, $this->score->stops_visited);
        $this->assertEquals(0, $this->score->points);

        $this->visitStop($this->tour->stops[0]->id, null, null, true)
            ->assertStatus(200);

        $this->assertCount(1, $this->score->fresh()->stops()->where('skipped_question', true)->get());
    }

    /** @test */
    public function a_user_should_be_penalized_for_skipping_questions()
    {
        $this->startTour(strtotime('30 minutes ago'));

        $this->assertEquals(0, $this->user->fresh()->stats->trophies);

        // skip a question
        $this->visitStop($this->tour->start_point_id, null, null, true);

        // set finished
        $this->visitStop($this->tour->end_point_id);

        $this->assertEquals(190, $this->score->fresh()->points);
    }
}
