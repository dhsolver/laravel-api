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

    /** @test */
    public function it_can_determine_all_image_paths()
    {
        $business = createUser('business');

        $tour = create('App\Tour', ['user_id' => $business->id]);

        $this->assertNull($tour->main_image_path);
        $this->assertNull($tour->image_1);
        $this->assertNull($tour->image_2);
        $this->assertNull($tour->image_3);

        $tour->main_image = 'test.jpg';
        $tour->image_1 = 'test1.jpg';
        $tour->image_2 = 'test2.jpg';
        $tour->image_3 = 'test3.jpg';

        $this->assertContains('http', $tour->main_image_path);
        $this->assertContains('test.jpg', $tour->main_image_path);

        $this->assertContains('http', $tour->image_1_path);
        $this->assertContains('test1.jpg', $tour->image_1_path);

        $this->assertContains('http', $tour->image_2_path);
        $this->assertContains('test2.jpg', $tour->image_2_path);

        $this->assertContains('http', $tour->image_3_path);
        $this->assertContains('test3.jpg', $tour->image_3_path);
    }
}
