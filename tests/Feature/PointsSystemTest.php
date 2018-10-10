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
        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop3->id, 'next_stop_id' => $this->stop4->id]);
        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop3->id, 'next_stop_id' => $this->stop5->id]);
        $this->stop4->update(['next_stop_id' => $this->stop5->id]);
    }

    /** @test */
    public function it_can_calculate_the_distance_between_two_stops()
    {
        $ac = new AdventureCalculator($this->tour);
        $distance = $ac->getDistanceBetweenStops($this->stop1, $this->stop2);

        $this->assertEquals(0.5927164968112091, $distance);
    }

    /** @test */
    public function it_can_get_all_stop_permutations()
    {
    }

    /** @test */
    public function it_can_determine_the_shortest_route_for_a_tour()
    {
        $ac = new AdventureCalculator($this->tour);

        dd($ac->getShortestRoute());
    }
}
