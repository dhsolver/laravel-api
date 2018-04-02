<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\TourStop;

class TourTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_determine_the_next_order_for_its_stops()
    {
        $business = createUser('business');

        $tour = create('App\Tour', ['user_id' => $business->id]);

        $this->assertEquals(1, $tour->getNextStopOrder());

        create(TourStop::class, ['tour_id' => $tour->id, 'order' => 1]);

        $this->assertEquals(2, $tour->getNextStopOrder());

        create(TourStop::class, ['tour_id' => $tour->id, 'order' => 5]);

        $this->assertEquals(6, $tour->getNextStopOrder());
    }
}
