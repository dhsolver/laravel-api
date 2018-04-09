<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\StopChoice;
use App\TourStop;

class StopTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_have_multiple_choices()
    {
        $business = createUser('business');

        $tour = create('App\Tour', ['user_id' => $business->id]);

        $stop = create(TourStop::class, ['tour_id' => $tour->id, 'order' => 1]);

        create(StopChoice::class, ['tour_stop_id' => $stop->id]);
        create(StopChoice::class, ['tour_stop_id' => $stop->id]);

        $this->assertCount(2, $stop->choices);
    }
}
