<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;

class ManageToursTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;

    public function setUp()
    {
        parent::setUp();

        $this->tour = create('App\Tour');
    }

    /**
     * Helper to provide route to the class tour based on named routes.
     *
     * @param String $name
     * @return void
     */
    public function tourRoute($name)
    {
        return route("admin.tours.$name", $this->tour->id);
    }

    protected function updateTour($overrides = [])
    {
        $data = array_merge($this->tour->toArray(), $overrides);

        return $this->json('PATCH', route('admin.tours.update', $this->tour->id), $data);
    }

    /** @test */
    public function a_tour_can_be_updated_by_an_admin()
    {
        $this->signIn('admin');

        $data = [
            'title' => 'test title',
            'description' => 'test desc',
            'pricing_type' => Tour::$PRICING_TYPES[0],
            'type' => \App\TourType::all()[0],
        ];

        $this->updateTour($data)
            ->assertStatus(200)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function an_admin_can_get_a_list_of_all_tours()
    {
        $otherTour = create('App\Tour', ['title' => md5('unique title string')]);

        $this->assertCount(2, Tour::all());

        $this->signIn('admin');

        $this->json('GET', route('admin.tours.index'))
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $this->tour->title])
            ->assertJsonFragment(['title' => $otherTour->title]);
    }

    /** @test */
    public function an_admin_can_delete_a_tour()
    {
        $this->signIn('admin');

        $this->assertCount(1, Tour::all());

        $this->json('DELETE', route('admin.tours.destroy', $this->tour->id))
            ->assertStatus(200);

        $this->assertCount(0, Tour::all());
    }

    /** @test */
    public function an_admin_can_view_a_single_tour()
    {
        $this->signIn('admin');

        $this->json('GET', route('admin.tours.show', $this->tour->id))
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $this->tour->title]);
    }

    /** @test */
    public function an_admin_can_create_a_tour()
    {
        $this->signIn('admin');

        $tour = make(Tour::class)->toArray();

        $this->assertCount(1, Tour::all());

        $this->json('POST', route('admin.tours.store'), $tour);

        $this->assertCount(2, Tour::all());
    }

    /** @test */
    public function an_admin_must_supply_a_client_id_to_create_a_tour()
    {
        $this->signIn('admin');

        $tour = make(Tour::class)->toArray();

        $this->assertCount(1, Tour::all());

        unset($tour['user_id']);

        $this->json('POST', route('admin.tours.store'), $tour)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);

        $this->assertCount(1, Tour::all());
    }

    /** @test */
    public function an_admin_can_transfer_a_tour_to_another_user()
    {
        $this->signIn('admin');

        $user = $this->tour->creator;
        $otherUser = createUser('client');

        $this->assertCount(1, $user->type->tours);
        $this->assertCount(0, $otherUser->tours);

        $this->json('PATCH', route('admin.tours.transfer', ['tour' => $this->tour->id]), ['user_id' => $otherUser->id])
            ->assertStatus(200);

        $this->assertCount(0, $user->fresh()->type->tours);
        $this->assertCount(1, $otherUser->fresh()->tours);
    }

    /** @test */
    public function an_admin_cannot_transfer_a_tour_to_a_user_exceeding_their_tour_limit()
    {
        $this->withExceptionHandling();

        $this->signIn('admin');

        $user = $this->tour->creator;
        $otherUser = createUser('client');
        $otherUser->update(['tour_limit' => 0]);

        $this->assertCount(1, $user->type->tours);
        $this->assertCount(0, $otherUser->tours);

        $this->json('PATCH', route('admin.tours.transfer', ['tour' => $this->tour->id]), ['user_id' => $otherUser->id])
            ->assertStatus(422)
            ->assertSee('exceed the number of tours');

        $this->assertCount(1, $user->fresh()->type->tours);
        $this->assertCount(0, $otherUser->fresh()->tours);

        $otherUser->update(['tour_limit' => 1]);

        $this->json('PATCH', route('admin.tours.transfer', ['tour' => $this->tour->id]), ['user_id' => $otherUser->id])
            ->assertStatus(200);

        $this->assertCount(0, $user->fresh()->type->tours);
        $this->assertCount(1, $otherUser->fresh()->tours);
    }
}
