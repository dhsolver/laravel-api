<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\StopChoice;

class StopChoiceTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_determine_the_next_order_for_a_choice()
    {
        $client = createUser('client');

        $tour = create('App\Tour', ['user_id' => $client->id]);
        $stop = create('App\TourStop', ['tour_id' => $tour->id]);

        $this->assertEquals(1, StopChoice::getNextOrder($stop->id));

        create(StopChoice::class, ['tour_stop_id' => $stop->id, 'order' => 1]);

        $this->assertEquals(2, StopChoice::getNextOrder($stop->id));

        create(StopChoice::class, ['tour_stop_id' => $stop->id, 'order' => 5]);

        $this->assertEquals(6, StopChoice::getNextOrder($stop->id));
    }

    /** @test */
    public function it_can_by_sorted_by_order()
    {
        $client = createUser('client');

        $tour = create('App\Tour', ['user_id' => $client->id]);
        $stop = create('App\TourStop', ['tour_id' => $tour->id]);

        create(StopChoice::class, ['tour_stop_id' => $stop->id, 'order' => 2]);
        create(StopChoice::class, ['tour_stop_id' => $stop->id, 'order' => 1]);
        create(StopChoice::class, ['tour_stop_id' => $stop->id, 'order' => 3]);

        $this->assertEquals([1, 2, 3], $stop->fresh()->choices()->pluck('order')->toArray());
    }
}
