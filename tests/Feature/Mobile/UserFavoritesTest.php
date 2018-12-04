<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;

class UserFavoritesTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;

        $this->tour = factory(Tour::class)->states('published')->create();

        $this->withoutExceptionHandling();
    }

    /** @test */
    public function a_user_can_favorite_a_tour()
    {
        $this->assertCount(0, $this->user->fresh()->favorites);

        $this->postJson(route('mobile.favorites.store', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertCount(1, $this->user->fresh()->favorites);
    }

    /** @test */
    public function a_user_can_unfavorite_a_tour()
    {
        $this->postJson(route('mobile.favorites.store', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertCount(1, $this->user->fresh()->favorites);

        $this->delete(route('mobile.favorites.destroy', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertCount(0, $this->user->fresh()->favorites);
    }

    /** @test */
    public function a_user_can_get_a_list_of_all_their_favorites()
    {
        $otherTour = factory(Tour::class)->states('published')->create();
        $this->postJson(route('mobile.favorites.store', ['tour' => $this->tour]));
        $this->postJson(route('mobile.favorites.store', ['tour' => $otherTour]));
        $this->assertCount(2, $this->user->fresh()->favorites);

        $this->getJson(route('mobile.favorites'))
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $this->tour->title])
            ->assertJsonFragment(['title' => $otherTour->title])
            ->assertJsonCount(2, 'favorites');
    }

    /** @test */
    public function a_users_profile_should_contain_the_number_of_favorites()
    {
        $this->signIn('user');

        $this->getJson(route('mobile.profile.show', ['user' => $this->signInUser]))
            ->assertStatus(200)
            ->assertJsonFragment(['favorites' => 0])
            ->assertSee($this->signInUser->email);

        $tour = factory(Tour::class)->states('published')->create();
        $this->signInUser->user->favorites()->attach($tour);

        $tour = factory(Tour::class)->states('published')->create();
        $this->signInUser->user->favorites()->attach($tour);

        $this->getJson(route('mobile.profile.show', ['user' => $this->signInUser]))
            ->assertStatus(200)
            ->assertJsonFragment(['favorites' => 2])
            ->assertSee($this->signInUser->email);
    }

    /** @test */
    public function a_user_can_favorite_a_tour_twice_without_throwing_an_sql_error()
    {
        $this->assertCount(0, $this->user->fresh()->favorites);

        $this->postJson(route('mobile.favorites.store', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertCount(1, $this->user->fresh()->favorites);

        $this->postJson(route('mobile.favorites.store', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->assertCount(1, $this->user->fresh()->favorites);
    }

    /** @test */
    public function a_user_can_unfavorite_a_tour_twice_without_throwing_an_sql_error()
    {
        $this->delete(route('mobile.favorites.destroy', ['tour' => $this->tour]))
            ->assertStatus(200);
    }
}
