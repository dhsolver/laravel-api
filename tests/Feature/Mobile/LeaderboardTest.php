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

        $this->getJson(route('mobile.leaderboard.tour', ['tour' => $this->tour]))
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

        $this->getJson(route('mobile.leaderboard.tour', ['tour' => $this->tour]))
            ->assertSuccessful()
            ->assertJsonCount(3, 'leaders')
            ->assertSeeInOrder([$this->user2->name, $this->user3->name, $this->user->name])
            ->assertJsonMissing(['points' => 1]);
    }

    /** @test */
    public function a_leaderboard_only_lists_the_top_100()
    {
        $tour = $this->tour;
        factory(MobileUser::class, 101)->create()->each(function ($user) use ($tour) {
            factory(ScoreCard::class)->create([
                'user_id' => $user->id,
                'tour_id' => $tour->id,
                'points' => 200,
            ]);
        });

        $this->getJson(route('mobile.leaderboard.tour', ['tour' => $this->tour]))
            ->assertSuccessful()
            ->assertJsonCount(100, 'leaders');
    }

    /** @test */
    public function a_leaderboard_should_only_show_one_entry_per_user()
    {
        $tour = $this->tour;
        factory(ScoreCard::class)->create([
            'user_id' => $this->user->id,
            'tour_id' => $tour->id,
            'points' => 5,
        ]);

        $this->getJson(route('mobile.leaderboard.tour', ['tour' => $tour]))
            ->assertSuccessful()
            ->assertJsonCount(3, 'leaders')
            ->assertJsonMissing(['points' => 5])
            ->assertJsonFragment(['points' => 150])
            ->assertJsonFragment(['points' => 200])
            ->assertJsonFragment(['points' => 187]);
    }

    /** @test */
    public function fetching_an_empty_leader_board_should_show_no_results()
    {
        ScoreCard::where('id', '>', 0)->delete();

        $this->getJson(route('mobile.leaderboard.tour', ['tour' => $this->tour]))
            ->assertSuccessful()
            ->assertJsonCount(0, 'leaders');
    }

    /** @test */
    public function a_leaderboard_should_order_the_entries_by_points()
    {
        $tour = $this->tour;
        $this->getJson(route('mobile.leaderboard.tour', ['tour' => $tour]))
            ->assertSuccessful()
            ->assertSeeInOrder([200, 187, 150]);
    }

    /** @test */
    public function a_user_can_view_the_all_time_leaderboard()
    {
        ScoreCard::where('id', '>', 0)->delete();

        $tours = [
            $this->tour,
            factory(Tour::class)->create(['type' => TourType::ADVENTURE]),
            factory(Tour::class)->create(['type' => TourType::ADVENTURE])
        ];

        for ($i = 0; $i < 105; $i++) {
            $user = factory(MobileUser::class)->create();
            $tour = (195 / ($i + 1)) > 3 ? $tours[0] : (195 / ($i + 1)) > 2 ? $tours[1] : $tours[2];
            factory(ScoreCard::class)->create([
                'user_id' => $user->id,
                'tour_id' => $tour->id,
                'points' => (200 - $i),
            ]);
        }

        $this->assertEquals(105, ScoreCard::count());

        $this->getJson(route('mobile.leaderboard'))
            ->assertStatus(200)
            ->assertJsonFragment(['points' => 200])
            ->assertJsonFragment(['points' => 101])
            ->assertJsonMissing(['points' => 100]);
    }

    /** @test */
    public function the_all_time_leaderboard_should_order_the_entries_by_points()
    {
        $this->getJson(route('mobile.leaderboard'))
            ->assertStatus(200)
            ->assertSeeInOrder([200, 187, 150]);
    }

    /** @test */
    public function the_all_time_leaderboard_should_only_show_one_entry_per_user_tour_combo()
    {
        $tour = $this->tour;
        factory(ScoreCard::class)->create([
            'user_id' => $this->user->id,
            'tour_id' => $tour->id,
            'points' => 5,
        ]);

        $otherTour = factory(Tour::class)->create(['type' => TourType::ADVENTURE]);
        factory(ScoreCard::class)->create([
            'user_id' => $this->user->id,
            'tour_id' => $otherTour->id,
            'points' => 10,
        ]);

        $this->getJson(route('mobile.leaderboard'))
            ->assertSuccessful()
            ->assertJsonCount(4, 'leaders')
            ->assertJsonMissing(['points' => 5])
            ->assertJsonFragment(['points' => 10, 'tour_id' => $otherTour->id])
            ->assertJsonFragment(['points' => 150, 'tour_id' => $this->tour->id])
            ->assertJsonFragment(['points' => 200, 'tour_id' => $this->tour->id])
            ->assertJsonFragment(['points' => 187, 'tour_id' => $this->tour->id]);
    }
}
