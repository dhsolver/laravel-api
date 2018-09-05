<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Favorite;

class FavoriteToursTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    /** @test */
    public function a_user_can_favorite_a_tour()
    {
        $this->signIn('user');

        $tour = create(\App\Tour::class);

        $this->assertCount(0, Favorite::all());

        $this->postJson(route('mobile.favorites.store'), ['tour_id' => $tour->id])
            ->assertStatus(200);

        $this->assertCount(1, Favorite::all());
        $this->assertCount(1, $this->signInUser->user->favorites);
    }

    /** @test */
    public function a_favorite_must_belong_to_a_valid_tour()
    {
        $this->signIn('user');

        $this->assertCount(0, Favorite::all());

        $this->postJson(route('mobile.favorites.store'), ['tour_id' => 235235])
            ->assertStatus(422);

        $this->assertCount(0, Favorite::all());
    }

    /** @test */
    public function a_user_can_remove_a_favorite()
    {
        $this->disableExceptionHandling();

        $this->signIn('user');

        $tour = create(\App\Tour::class);

        $user = $this->signInUser->user;

        $user->favorites()->attach($tour->id);

        $this->assertCount(1, $user->fresh()->favorites);

        $this->deleteJson(route('mobile.favorites.destroy'), ['tour_id' => $tour->id])
            ->assertStatus(200);

        $this->assertCount(0, $user->fresh()->favorites);
    }
}
