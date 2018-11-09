<?php

namespace Tests\Feature\Mobile;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use Tests\HasTestTour;

class TrackTourTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken, HasTestTour;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;

        $this->withoutExceptionHandling();
        list($this->tour, $this->stops) = $this->createTestTour();
    }

    /** @test */
    public function when_a_user_visits_a_stop_it_updates_their_score_card_points()
    {
        $this->startTour()->assertStatus(200);

        $this->assertEquals(0, $this->score->stops_visited);
        $this->assertEquals(0, $this->score->points);

        $this->visitStop($this->tour->stops[0]->id)
            ->assertStatus(200)
            ->assertJsonFragment([
                'stops_visited' => 1,
                'points' => 1,
            ]);

        $this->assertEquals(1, $this->score->fresh()->stops_visited);
        $this->assertEquals(1, $this->score->fresh()->points);
    }

    /** @test */
    public function when_a_user_visits_a_stop_it_immediately_counts_towards_their_total_score()
    {
        $this->startTour()->assertStatus(200);

        $this->assertEquals(0, $this->score->stops_visited);
        $this->assertEquals(0, $this->score->points);

        $this->visitStop($this->tour->stops[0]->id)
            ->assertStatus(200);

        $this->assertEquals(1, $this->user->fresh()->stats->points);

        $this->visitStop($this->tour->stops[1]->id)
            ->assertStatus(200);

        $this->assertEquals(2, $this->user->fresh()->stats->points);

        $this->visitStop($this->tour->stops[0]->id)
            ->assertStatus(200);

        $this->assertEquals(2, $this->user->fresh()->stats->points);
    }

    /** @test */
    public function when_a_user_visits_all_the_stops_on_the_tour_it_is_marked_finished()
    {
        $this->startTour();

        $this->visitStop($this->tour->stops[0]->id);
        $this->visitStop($this->tour->stops[1]->id);
        $this->visitStop($this->tour->stops[2]->id);
        $this->visitStop($this->tour->stops[3]->id);

        $this->assertNull($this->score->fresh()->finished_at);

        $this->visitStop($this->tour->stops[4]->id);

        $this->assertNotNull($this->score->fresh()->finished_at);
        $this->assertEquals(5, $this->score->fresh()->points);
    }

    /** @test */
    public function a_user_only_wins_a_trophy_when_they_visit_the_amount_of_stops_required()
    {
        $this->startTour();

        $this->visitStop($this->tour->stops[0]->id)
            ->assertJsonFragment(['won_trophy' => false]);
        $this->visitStop($this->tour->stops[1]->id)
            ->assertJsonFragment(['won_trophy' => false]);
        $this->visitStop($this->tour->stops[2]->id)
            ->assertJsonFragment(['won_trophy' => false]);
        $this->visitStop($this->tour->stops[3]->id)
            ->assertJsonFragment(['won_trophy' => true]);

        $this->assertTrue($this->score->fresh()->won_trophy);

        $this->visitStop($this->tour->stops[4]->id)
            ->assertJsonFragment(['won_trophy' => true]);
    }

    /** @test */
    public function when_a_user_wins_a_trophy_it_can_include_a_prize()
    {
        $this->startTour();

        $this->visitStop($this->tour->stops[0]->id);
        $this->visitStop($this->tour->stops[1]->id);
        $this->visitStop($this->tour->stops[2]->id);
        $response = $this->visitStop($this->tour->stops[3]->id);

        $this->assertNotNull($this->score->fresh()->prize_expires_at);

        $response->assertJsonFragment(['prize' => [
            'details' => $this->tour->prize_details,
            'instructions' => $this->tour->prize_instructions,
            'expires_at' => $this->score->fresh()->prize_expires_at->toDateTimeString(),
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
    public function a_users_total_points_only_includes_the_best_score_for_a_single_tour()
    {
        $this->startTour();

        $this->visitStop($this->tour->stops[0]->id);
        $this->visitStop($this->tour->stops[1]->id);

        $this->assertEquals(2, $this->user->fresh()->stats->points);

        $this->startTour();

        $this->visitStop($this->tour->stops[0]->id);
        $this->visitStop($this->tour->stops[1]->id);
        $this->visitStop($this->tour->stops[2]->id);
        $this->visitStop($this->tour->stops[3]->id);

        $this->assertEquals(4, $this->user->fresh()->stats->points);
    }

    /** @test */
    public function a_user_can_only_unlock_a_trophy_once_per_regular_tour()
    {
        $this->startTour(strtotime('now'), $this->tour);

        $this->assertEquals(0, $this->user->fresh()->stats->trophies);

        $this->visitStop($this->tour->stops[0]->id);
        $this->visitStop($this->tour->stops[1]->id);
        $this->visitStop($this->tour->stops[2]->id);
        $this->visitStop($this->tour->stops[3]->id);

        $this->assertTrue($this->score->fresh()->won_trophy);
        $this->assertEquals(1, $this->user->fresh()->stats->trophies);

        $this->startTour(strtotime('now'), $this->tour);

        $this->visitStop($this->tour->stops[0]->id);
        $this->visitStop($this->tour->stops[1]->id);
        $this->visitStop($this->tour->stops[2]->id);
        $this->visitStop($this->tour->stops[3]->id);

        $this->assertTrue($this->score->fresh()->won_trophy);
        $this->assertEquals(1, $this->user->fresh()->stats->trophies);
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

}
