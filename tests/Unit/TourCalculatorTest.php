<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AttachJwtToken;
use Tests\HasTestTour;
use Tests\TestCase;
use App\Points\TourCalculator;
use App\ScoreCard;

class TourCalculatorTest extends TestCase
{
    use RefreshDatabase, HasTestTour, AttachJwtToken;

    /**
     * The test users score card for the test tour.
     *
     * @var \App\ScoreCard
     */
    protected $score;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;

        list($this->tour, $this->stops) = $this->createTestTour();

        $this->score = factory(ScoreCard::class)->create([
            'user_id' => $this->user->id,
            'tour_id' => $this->tour->id,
            'total_stops' => $this->stops->count(),
        ]);
    }

    /** @test */
    public function it_can_calculate_the_total_number_of_stops_on_a_tour()
    {
        $tc = new TourCalculator($this->tour);

        $this->assertEquals(5, $tc->getTotalStops());
    }

    /** @test */
    public function it_can_determine_if_a_score_card_qualifies_for_a_trophy()
    {
        $tc = new TourCalculator($this->tour);

        $trophyRate = config('junket.points.trophy_rate', 70);

        $this->assertEquals(5, $tc->getTotalStops());

        $this->score->stops_visited = 3;
        $this->assertFalse($tc->scoreQualifiesForTrophy($this->score));
        $this->score->stops_visited = 4;
        $this->assertTrue($tc->scoreQualifiesForTrophy($this->score));
        $this->score->stops_visited = 5;
        $this->assertTrue($tc->scoreQualifiesForTrophy($this->score));
    }

    /** @test */
    public function it_can_calculate_the_number_of_points_for_a_score_card()
    {
        $tc = new TourCalculator($this->tour);

        $trophyRate = config('junket.points.trophy_rate', 70);

        $this->score->stops_visited = 3;
        $this->assertEquals(3, $tc->getPoints($this->score));
        $this->score->stops_visited = 4;
        $this->assertEquals(4, $tc->getPoints($this->score));
        $this->score->stops_visited = 5;
        $this->assertEquals(5, $tc->getPoints($this->score));
    }
}
