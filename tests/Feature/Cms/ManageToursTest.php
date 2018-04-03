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
            ])->assertStatus(200)
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
            ->assertStatus(200)
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
            ->assertStatus(200)
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
    public function a_user_can_update_a_tours_its_main_image()
    {
        \Storage::fake('s3');

        $this->withoutExceptionHandling();

        $this->loginAs($this->business);

        $file = $this->uploadImage('main_image');

        \Storage::disk('s3')->assertExists($file);
    }

    /** @test */
    public function a_user_can_update_a_tours_sub_images()
    {
        $this->loginAs($this->business);

        $file = $this->uploadImage('image_1');
        \Storage::disk('s3')->assertExists($file);

        $file = $this->uploadImage('image_2');
        \Storage::disk('s3')->assertExists($file);

        $file = $this->uploadImage('image_3');
        \Storage::disk('s3')->assertExists($file);
    }

    /** @test */
    public function tour_images_have_a_max_file_size()
    {
        $this->loginAs($this->business);

        $largeImage = UploadedFile::fake()
            ->image('main.jpg')
            ->size(config('junket.imaging.max_file_size') + 1);

        $file = $this->uploadImage('main_image', $largeImage, '422');
        $file = $this->uploadImage('image_1', $largeImage, '422');
        $file = $this->uploadImage('image_2', $largeImage, '422');
        $file = $this->uploadImage('image_3', $largeImage, '422');
    }

    /** @test */
    public function tour_images_must_be_images()
    {
        $this->loginAs($this->business);

        $pdfFile = UploadedFile::fake()
            ->create('document.pdf', 5000);

        $this->json('PUT', $this->tourRoute('images'), ['main_image' => $pdfFile])
            ->assertStatus(422)
            ->assertSee('main image must be an image');
    }

    /** @test */
    public function other_users_cannot_upload_images()
    {
        $this->signIn('business');

        $this->json('PUT', $this->tourRoute('images'), [])
            ->assertStatus(403);
    }

    /**
     * Helper to provide route to the class tour based on named routes.
     *
     * @param String $name
     * @return void
     */
    public function tourRoute($name)
    {
        return route("cms.tours.$name", $this->tour->id);
    }

    /**
     * Helper to upload a tour image and return the filename.
     *
     * @param String $key
     * @param [type] $image
     * @param integer $expectedStatus
     * @return String
     */
    public function uploadImage($key, $image = null, $expectedStatus = 200)
    {
        \Storage::fake('s3');

        if (empty($image)) {
            $image = UploadedFile::fake()
                ->image('main.jpg', 500, 500)
                ->size(config('junket.imaging.max_file_size') - 1);
        }

        $resp = $this->json('PUT', $this->tourRoute('images'), [$key => $image]);
        $resp->assertStatus(intval($expectedStatus));

        try {
            return $resp->getData()->data->$key;
        } catch (\Exception $ex) {
            return null;
        }
    }
}
