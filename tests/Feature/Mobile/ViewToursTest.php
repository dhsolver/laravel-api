<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use App\Tour;
use App\Device;
use Carbon\Carbon;

class ViewToursTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $user;
    protected $device;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;
        $this->device = $this->user->devices()->create(factory(Device::class)->make()->toArray());
    }

    /** @test */
    public function a_user_can_get_a_list_of_published_tours()
    {
        factory(Tour::class, 5)->create(['published_at' => Carbon::now()]);

        $this->assertCount(5, Tour::all());

        $this->getJson(route('mobile.tours.index'))
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function a_user_cannot_see_unpublished_tours_in_the_list()
    {
        factory(Tour::class, 5)->create(['published_at' => Carbon::now()]);

        $this->assertCount(5, Tour::all());

        Tour::first()->update(['published_at' => null]);

        $this->getJson(route('mobile.tours.index'))
            ->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    /** @test */
    public function a_user_cannot_see_tours_created_by_deactivated_users_in_the_list()
    {
        factory(Tour::class, 5)->create(['published_at' => Carbon::now()]);

        $this->assertCount(5, Tour::all());

        Tour::first()->creator->deactivate();

        $this->getJson(route('mobile.tours.index'))
            ->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    /** @test */
    public function a_user_cannot_view_an_unpublished_tour()
    {
        $tour = factory(Tour::class)->create(['published_at' => null]);

        $this->assertFalse($tour->is_published);

        $this->getJson(route('mobile.tours.show', ['tour' => $tour]))
            ->assertStatus(404);
    }

    /** @test */
    public function a_user_cannot_view_a_tour_by_a_deactivated_user()
    {
        $tour = factory(Tour::class)->create(['published_at' => Carbon::now()]);

        Tour::first()->creator->deactivate();

        $this->getJson(route('mobile.tours.show', ['tour' => $tour]))
            ->assertStatus(404);
    }
}
