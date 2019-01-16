<?php

namespace Tests\Unit;

use App\Exceptions\UntraceableTourException;
use App\Points\AdventureCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AttachJwtToken;
use Tests\HasTestTour;
use Tests\TestCase;

class AdventureCalculatorTest extends TestCase
{
    use RefreshDatabase, HasTestTour, AttachJwtToken;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;

        list($this->tour, $this->stops) = $this->createTestAdventure(false);
    }

    /** @test */
    public function it_cannot_calculate_the_distance_between_two_stops_if_there_is_no_route_data()
    {
        $this->expectException(UntraceableTourException::class);

        $ac = new AdventureCalculator($this->tour);
        $distance = $ac->getDistanceBetweenStops($this->stops[0], $this->stops[1]);

        $this->assertEquals(0, $distance);
    }

    /** @test */
    public function it_can_calculate_the_distance_between_two_stops_when_there_is_route_data()
    {
        $this->insertStopRouteData($this->tour);

        $ac = new AdventureCalculator($this->tour);
        $distance = $ac->getDistanceBetweenStops($this->stops[0], $this->stops[1]);

        $this->assertEquals(0.84, round($distance, 2));
    }

    /** @test */
    public function it_can_determine_the_first_stop_of_a_tour()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->assertEquals($this->stops[0]->id, $ac->getFirstStop()->id);
    }

    /** @test */
    public function it_can_determine_the_last_stop_of_a_tour()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->assertEquals($this->stops[4]->id, $ac->getLastStop()->id);
    }

    /** @test */
    public function if_the_first_stop_of_the_tour_is_missing_it_will_throw_an_exception()
    {
        $this->tour->update(['start_point_id' => null]);
        $ac = new AdventureCalculator($this->tour);

        $this->expectException(UntraceableTourException::class);
        $ac->getFirstStop();
    }

    /** @test */
    public function if_the_last_stop_of_the_tour_is_missing_it_will_throw_an_exception()
    {
        $this->tour->update(['end_point_id' => null]);
        $ac = new AdventureCalculator($this->tour);

        $this->expectException(UntraceableTourException::class);
        $ac->getLastStop();
    }

    /** @test */
    public function it_can_get_the_next_stops_of_a_stop()
    {
        $ac = new AdventureCalculator($this->tour);

        $next = $ac->getNextStops($this->stops[0]);
        $this->assertCount(1, $next);
        $this->assertEquals($next[0]->id, $this->stops[1]->id);

        $next = $ac->getNextStops($this->stops[1]);
        $this->assertCount(2, $next);
        $this->assertEquals($next[0]->id, $this->stops[2]->id);
        $this->assertEquals($next[1]->id, $this->stops[3]->id);
    }

    /** @test */
    public function it_throws_an_error_getting_next_stops_if_the_next_stop_is_empty()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->stops[0]->update(['next_stop_id' => null]);

        $this->expectException(UntraceableTourException::class);
        $ac->getNextStops($this->stops[0]);
    }

    /** @test */
    public function it_throws_an_error_getting_next_stops_if_a_choice_next_stop_is_empty()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->stops[1]->choices()->first()->update(['next_stop_id' => null]);

        $this->expectException(UntraceableTourException::class);
        $ac->getNextStops($this->stops[1]->fresh());
    }

    /** @test */
    public function it_can_get_all_stop_permutations()
    {
        $ac = new AdventureCalculator($this->tour);

        $paths = $ac->getPossiblePaths();

        // stop1 -> stop2 -> stop3 -> stop5
        // stop1 -> stop2 -> stop4 -> stop5
        // stop1 -> stop2 -> stop3 -> stop4 -> stop 5
        $this->assertEquals($paths->toArray(), [
            [$this->stops[0]->id, $this->stops[1]->id, $this->stops[2]->id, $this->stops[4]->id],
            [$this->stops[0]->id, $this->stops[1]->id, $this->stops[3]->id, $this->stops[4]->id],
            [$this->stops[0]->id, $this->stops[1]->id, $this->stops[2]->id, $this->stops[3]->id, $this->stops[4]->id],
        ]);
    }

    /** @test */
    public function it_can_determine_the_shortest_route_for_a_tour_when_the_stops_have_routes()
    {
        $this->insertStopRouteData($this->tour);

        $ac = new AdventureCalculator($this->tour);

        list($route, $distance) = $ac->getShortestRoute();

        $this->assertEquals(2.15, round($distance, 2));
        $this->assertEquals([1, 2, 4, 5], $route);
    }

    /** @test */
    public function it_cannot_determine_the_shortest_route_for_a_tour_when_there_are_no_stop_routes()
    {
        $this->expectException(UntraceableTourException::class);

        $ac = new AdventureCalculator($this->tour);

        list($route, $distance) = $ac->getShortestRoute();

        $this->assertEquals(0, $distance);
        $this->assertEquals([1, 2, 4, 5], $route);
    }

    /** @test */
    public function it_can_calculate_the_clock_par_for_a_tour()
    {
        $this->insertStopRouteData($this->tour);

        $ac = new AdventureCalculator($this->tour);

        $par = $ac->getPar();

        $this->assertEquals(51, $par);
    }

    /** @test */
    public function it_can_calculate_the_points_for_a_users_time()
    {
        $this->insertStopRouteData($this->tour);

        $ac = new AdventureCalculator($this->tour);

        $points = $ac->calculatePoints(55);

        $this->assertEquals(192, $points);

        $points = $ac->calculatePoints(55.3);

        $this->assertEquals(192, $points);

        $points = $ac->calculatePoints(55.6);

        $this->assertEquals(191, $points);
    }

    /** @test */
    public function it_can_calculate_if_a_users_score_qualifies_for_a_trophy()
    {
        $this->insertStopRouteData($this->tour);

        $ac = new AdventureCalculator($this->tour);

        $score = $ac->calculatePoints(55);

        $this->assertEquals(192, $score);

        $this->assertTrue($ac->scoreQualifiesForTrophy($score));

        $score = $ac->calculatePoints(90);

        $this->assertEquals(122, $score);

        $this->assertFalse($ac->scoreQualifiesForTrophy($score));
    }

    /** @test */
    public function it_should_reduce_points_based_on_the_amount_of_skipped_stops()
    {
        $this->insertStopRouteData($this->tour);

        $ac = new AdventureCalculator($this->tour);

        $points = $ac->calculatePoints(55, null, 2);

        $this->assertEquals(172, $points);
    }
}
