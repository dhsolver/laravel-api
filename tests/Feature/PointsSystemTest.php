<?php

namespace Tests\Feature\Mobile;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use App\Adventure\AdventureCalculator;
use App\Tour;
use App\TourStop;
use App\Device;
use App\StopChoice;
use App\Exceptions\UntraceableTourException;

class PointsSystemTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $tour;
    protected $user;
    protected $device;
    protected $stop1;
    protected $stop2;
    protected $stop3;
    protected $stop4;
    protected $stop5;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;
        $this->device = $this->user->devices()->create(factory(Device::class)->make()->toArray());

        $this->tour = factory(Tour::class)->states('published')->create(['pricing_type' => 'free']);

        $this->stop1 = factory(TourStop::class)->create(['tour_id' => $this->tour]);
        $this->stop1->location->update([
            'address1' => '77 River St',    // Hoboken Cigars
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.73611847,
            'longitude' => -74.0290305,
        ]);

        $this->stop2 = factory(TourStop::class)->create(['tour_id' => $this->tour]);
        $this->stop2->location->update([
            'id' => 2610,
            'address1' => '500 Grand St',       // Grand Vin
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74331877,
            'longitude' => -74.03518617,
        ]);

        $this->stop3 = factory(TourStop::class)->create(['tour_id' => $this->tour]);
        $this->stop3->location->update([
            'id' => 2611,
            'address1' => '163 14th St',        // Dino's
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.75336903,
            'longitude' => -74.02768135,
        ]);

        $this->stop4 = factory(TourStop::class)->create(['tour_id' => $this->tour]);
        $this->stop4->location->update([
            'id' => 2612,
            'address1' => '11th St',        // Baseball Monument
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74993106,
            'longitude' => -74.02735949,
        ]);

        $this->stop5 = factory(TourStop::class)->create(['tour_id' => $this->tour]);
        $this->stop5->location->update([
            'address1' => '622 Washington St',      // Benny Tunido's
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74423323,
            'longitude' => -74.02915657,
        ]);

        $this->stop1->update(['next_stop_id' => $this->stop2->id]);

        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop2->id, 'next_stop_id' => $this->stop3->id]);
        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop2->id, 'next_stop_id' => $this->stop4->id]);
        $this->stop2->update(['is_multiple_choice' => true]);

        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop3->id, 'next_stop_id' => $this->stop4->id]);
        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop3->id, 'next_stop_id' => $this->stop5->id]);
        $this->stop3->update(['is_multiple_choice' => true]);

        $this->stop4->update(['next_stop_id' => $this->stop5->id]);

        $this->tour->update([
            'start_point_id' => $this->stop1->id,
            'end_point_id' => $this->stop5->id,
        ]);
    }

    /** @test */
    public function it_can_calculate_the_distance_between_two_stops()
    {
        $ac = new AdventureCalculator($this->tour);
        $distance = $ac->getDistanceBetweenStops($this->stop1, $this->stop2);

        $this->assertEquals(0.5927164968112091, $distance);
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
    public function it_can_determine_the_shortest_route_for_a_tour()
    {
        $ac = new AdventureCalculator($this->tour);

        list($route, $distance) = $ac->getShortestRoute();

        $this->assertEquals($distance, 1.6110865039252085);
        $this->assertEquals($route, [1, 2, 4, 5]);
    }
}
