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
        $this->createTestAdventure(false);
    }

    /** @test */
    public function it_can_calculate_the_distance_between_two_stops_if_there_is_no_route_data()
    {
        $ac = new AdventureCalculator($this->tour);
        $distance = $ac->getDistanceBetweenStops($this->stop1, $this->stop2);

        $this->assertEquals(0.5927164968112091, $distance);
    }

    /** @test */
    public function it_can_calculate_the_distance_between_two_stops_when_there_is_route_data()
    {
        $this->insertStopRouteData();

        $ac = new AdventureCalculator($this->tour);
        $distance = $ac->getDistanceBetweenStops($this->stop1, $this->stop2);

        $this->assertEquals(0.84, round($distance, 2));
    }

    /** @test */
    public function it_can_determine_the_first_stop_of_a_tour()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->assertEquals($this->stop1->id, $ac->getFirstStop()->id);
    }

    /** @test */
    public function it_can_determine_the_last_stop_of_a_tour()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->assertEquals($this->stop5->id, $ac->getLastStop()->id);
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

        $next = $ac->getNextStops($this->stop1);
        $this->assertCount(1, $next);
        $this->assertEquals($next[0]->id, $this->stop2->id);

        $next = $ac->getNextStops($this->stop2);
        $this->assertCount(2, $next);
        $this->assertEquals($next[0]->id, $this->stop3->id);
        $this->assertEquals($next[1]->id, $this->stop4->id);
    }

    /** @test */
    public function it_throws_an_error_getting_next_stops_if_the_next_stop_is_empty()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->stop1->update(['next_stop_id' => null]);

        $this->expectException(UntraceableTourException::class);
        $next = $ac->getNextStops($this->stop1);
    }

    /** @test */
    public function it_throws_an_error_getting_next_stops_if_a_choice_next_stop_is_empty()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->stop2->choices()->first()->update(['next_stop_id' => null]);

        $this->expectException(UntraceableTourException::class);
        $next = $ac->getNextStops($this->stop2);
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
            [$this->stop1->id, $this->stop2->id, $this->stop3->id, $this->stop5->id],
            [$this->stop1->id, $this->stop2->id, $this->stop4->id, $this->stop5->id],
            [$this->stop1->id, $this->stop2->id, $this->stop3->id, $this->stop4->id, $this->stop5->id],
        ]);
    }

    /** @test */
    public function it_can_determine_the_shortest_route_for_a_tour_when_the_stops_have_routes()
    {
        $this->insertStopRouteData();

        $ac = new AdventureCalculator($this->tour);

        list($route, $distance) = $ac->getShortestRoute();

        $this->assertEquals(2.15, round($distance, 2));
        $this->assertEquals([1, 2, 4, 5], $route);
    }

    /** @test */
    public function it_can_determine_the_shortest_route_for_a_tour_when_there_are_no_stop_routes()
    {
        $ac = new AdventureCalculator($this->tour);

        list($route, $distance) = $ac->getShortestRoute();

        $this->assertEquals(1.6110865039252085, $distance);
        $this->assertEquals([1, 2, 4, 5], $route);
    }

    /** @test */
    public function it_can_calculate_the_clock_par_for_a_tour()
    {
        $this->insertStopRouteData();

        $ac = new AdventureCalculator($this->tour);

        $par = $ac->getPar();

        $this->assertEquals(51, $par);
    }

    /** @test */
    public function it_can_calculate_the_points_for_a_users_time()
    {
        $this->insertStopRouteData();

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
        $this->insertStopRouteData();

        $ac = new AdventureCalculator($this->tour);

        $score = $ac->calculatePoints(55);

        $this->assertEquals(192, $score);

        $this->assertTrue($ac->scoreQualifiesForTrophy($score));

        $score = $ac->calculatePoints(90);

        $this->assertEquals(122, $score);

        $this->assertFalse($ac->scoreQualifiesForTrophy($score));
    }
}
