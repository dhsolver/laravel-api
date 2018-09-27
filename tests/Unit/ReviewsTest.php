<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Tour;
use App\TourStop;

class ReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected $tour;
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = create('App\User');

        factory(Tour::class, 3)->create();
        $this->tour = factory(Tour::class)->create();

        factory(TourStop::class, 10)->create([
            'tour_id' => $this->tour->id,
        ]);

        $this->tour = $this->tour->fresh();
    }

    /** @test */
    public function it_belongs_to_a_tour()
    {
        $review = factory(\App\Review::class)->create(['tour_id' => $this->tour->id]);

        $this->assertInstanceOf(Tour::class, $review->tour);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $review = factory(\App\Review::class)->create(['tour_id' => $this->tour->id]);

        $this->assertInstanceOf(\App\User::class, $review->user);
    }

    /** @test */
    public function it_can_have_a_text_review()
    {
        $review = factory(\App\Review::class)->create(['review' => 'test']);

        $this->assertEquals('test', $review->review);
    }

    /** @test */
    public function it_can_have_an_empty_review()
    {
        $review = factory(\App\Review::class)->create(['review' => null]);

        $this->assertNull($review->review);
    }

    /** @test */
    public function it_must_have_a_rating()
    {
        $review = factory(\App\Review::class)->create(['rating' => 35]);

        $this->assertEquals(35, $review->rating);
    }
}
