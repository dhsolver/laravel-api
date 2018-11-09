<?php

namespace Tests\Feature\Mobile;

use App\TourType;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use App\Tour;
use App\ScoreCard;
use App\MobileUser;

class LeaderboardTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $tour;
    protected $user;
    protected $user2;
    protected $user3;

    public function setUp()
    {
        parent::setUp();

        $this->tour = factory(Tour::class)->create(['type' => TourType::ADVENTURE]);

        $this->signIn('user');
        $this->user = $this->signInUser->user;
        factory(ScoreCard::class)->create([
            'user_id' => $this->user->id,
            'tour_id' => $this->tour->id,
            'points' => 150
        ]);

        $this->user2 = factory(MobileUser::class)->create();
        factory(ScoreCard::class)->create([
            'user_id' => $this->user2->id,
            'tour_id' => $this->tour->id,
            'points' => 200
        ]);

        $this->user3 = factory(MobileUser::class)->create();
        factory(ScoreCard::class)->create([
            'user_id' => $this->user3->id,
            'tour_id' => $this->tour->id,
            'points' => 187
        ]);
    }

    /** @test */
    public function a_user_can_view_the_leaderboard_of_a_tour()
    {
        $this->withoutExceptionHandling();

        $this->assertCount(3, ScoreCard::all());

        $this->getJson(route('mobile.leaderboard', ['tour' => $this->tour]))
            ->assertSuccessful()
            ->assertJsonCount(3, 'leaders')
            ->assertSeeInOrder([$this->user2->name, $this->user3->name, $this->user->name]);
    }

    /** @test */
    public function leaderboard_should_only_show_scores_for_that_tour()
    {
        $otherTour = factory(Tour::class)->create(['type' => TourType::ADVENTURE]);
        factory(ScoreCard::class)->create([
            'user_id' => $this->user->id,
            'tour_id' => $otherTour->id,
            'points' => 1
        ]);

        $this->getJson(route('mobile.leaderboard', ['tour' => $this->tour]))
            ->assertSuccessful()
            ->assertJsonCount(3, 'leaders')
            ->assertSeeInOrder([$this->user2->name, $this->user3->name, $this->user->name])
            ->assertJsonMissing(['points' => '1']);
    }

    /** @test */
    public function a_leaderboard_only_lists_the_top_100()
    {
        $tour = $this->tour;
        factory(MobileUser::class, 100)->create()->each(function ($user) use ($tour) {
            factory(ScoreCard::class)->create([
                'user_id' => $this->user->id,
                'tour_id' => $tour->id,
                'points' => 200,
            ]);
        });

        $this->getJson(route('mobile.leaderboard', ['tour' => $this->tour]))
            ->assertSuccessful()
            ->assertJsonCount(100, 'leaders');
    }
}
