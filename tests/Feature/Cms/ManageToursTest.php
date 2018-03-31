<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;

class ManageToursTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $business;

    public function setUp()
    {
        parent::setUp();

        $this->business = createUser('business');

        $this->tour = create('App\Tour', ['user_id' => $this->business->id]);
    }

    /** @test */
    public function a_tour_requires_a_title_description_and_proper_types_to_be_updated()
    {
        $this->loginAs($this->business);

        $this->updateTour(['title' => null])->assertStatus(422);
        $this->updateTour(['description' => null])->assertStatus(422);
        $this->updateTour(['pricing_type' => null])->assertStatus(422);
        $this->updateTour(['type' => null])->assertStatus(422);
    }

    /** @test */
    public function a_tour_can_be_updated_by_its_creator()
    {
        $this->loginAs($this->business);

        $this->updateTour([
            'title' => 'test title',
            'description' => 'test desc',
            'pricing_type' => Tour::$PRICING_TYPES[0],
            'type' => Tour::$TOUR_TYPES[0],
            ])
            ->assertSee('test title')
            ->assertSee('test desc');
    }

    /** @test */
    public function a_tour_cannot_be_updated_by_another_user()
    {
        $this->signIn('business');

        $this->updateTour([
            'title' => 'new title',
            ])
            ->assertStatus(403);
    }

    protected function updateTour($overrides = [])
    {
        $data = array_merge($this->tour->toArray(), $overrides);

        return $this->json('PATCH', route('cms.tours.update', $this->tour->id), $data);
    }

    /** @test */
    public function a_user_can_get_a_list_of_only_their_tours()
    {
        $otherTour = create('App\Tour', []);

        $this->assertCount(2, Tour::all());

        $this->loginAs($this->business);

        $this->json('GET', route('cms.tours.index'))
            ->assertSee($this->tour->title)
            ->assertDontSee($otherTour->title);
    }

    /** @test */
    public function a_tour_can_be_deleted_by_its_creator()
    {
        $this->loginAs($this->business);

        $this->assertCount(1, $this->business->tours);

        $this->json('DELETE', route('cms.tours.destroy', $this->tour->id))
            ->assertStatus(204);

        $this->assertCount(0, $this->business->fresh()->tours);
    }

    /** @test */
    public function a_tour_cannot_be_deleted_by_another_user()
    {
        $this->signIn('business');

        $this->assertCount(1, $this->business->tours);

        $this->json('DELETE', route('cms.tours.destroy', $this->tour->id))
            ->assertStatus(403);

        $this->assertCount(1, $this->business->fresh()->tours);
    }
}
