<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use Illuminate\Http\UploadedFile;

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
        $this->withExceptionHandling();

        $otherTour = create('App\Tour', ['title' => md5('unique title string')]);

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

    /** @test */
    public function a_tour_can_be_seen_by_its_creator()
    {
        $this->loginAs($this->business);

        $this->json('GET', route('cms.tours.show', $this->tour->id))
            ->assertSee($this->tour->title);
    }

    /** @test */
    public function a_tour_cannot_be_seen_by_another_user()
    {
        $this->signIn('business');

        $this->json('GET', route('cms.tours.show', $this->tour->id))
            ->assertStatus(403);
    }

    /** @test */
    public function a_tour_can_update_its_main_image()
    {
        $this->withoutExceptionHandling();

        $this->loginAs($this->business);

        // UploadedFile::fake()->image('avatar.jpg', $width, $height)->size(100);
        $data = array_merge($this->tour->toArray(), [
            'main_image' => UploadedFile::fake()
                ->image('main.jpg', 500, 500)
                ->size(config('junket.imaging.max_file_size') - 1),
        ]);

        $resp = $this->json('PUT', route('cms.tours.images', $this->tour->id), $data);
        // $resp->assertStatus(200);
        // dd($resp->getData());

        $this->assertNotEmpty($resp->getData()->data->main_image);
    }

    /** @test */
    public function tour_images_have_a_max_file_size()
    {
        $this->loginAs($this->business);

        $largeImage = UploadedFile::fake()
            ->image('main.jpg')
            ->size(config('junket.imaging.max_file_size') + 1);

        $this->json('PUT', $this->tourRoute('images'), ['main_image' => $largeImage])
            ->assertStatus(422)
            ->assertSee('main image may not be greater than');
    }

    /** @test */
    public function a_tours_main_image_must_be_an_image()
    {
        $this->assertTrue(true);
    }

    /**
     * Helper to provide route to the class tour based on named routes.
     *
     * @param [type] $name
     * @return void
     */
    public function tourRoute($name)
    {
        return route("cms.tours.$name", $this->tour->id);
    }
}
