<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use Illuminate\Http\UploadedFile;

class TourMediaTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $client;

    public function setUp()
    {
        parent::setUp();

        \Storage::fake('s3');

        $this->client = createUser('client');

        $this->tour = create('App\Tour', ['user_id' => $this->client->id]);
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
     * Helper to upload tour media and return the filename.
     *
     * @param String $key
     * @param String $filename
     * @param [type] $image
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function uploadMedia($key, &$filename, $image = null)
    {
        if (empty($image)) {
            $image = UploadedFile::fake()
                ->image('main.jpg', 500, 500)
                ->size(config('junket.imaging.max_file_size') - 1);
        }

        $resp = $this->json('PUT', $this->tourRoute('media'), [$key => $image]);

        try {
            $filename = $resp->getData()->data->$key;
        } catch (\Exception $ex) {
            $filename = null;
        }

        return $resp;
    }

    /** @test */
    public function a_user_can_update_a_tours_images()
    {
        $this->withoutExceptionHandling();

        $this->loginAs($this->client);

        foreach (Tour::$imageAttributes as $key) {
            $this->uploadMedia($key, $file)
               ->assertStatus(200);

            \Storage::disk('s3')->assertExists($file);

            $this->assertNotEmpty($this->tour->fresh()->toArray()[$key]);
        }
    }

    /** @test */
    public function tour_images_have_a_max_file_size()
    {
        $this->loginAs($this->client);

        $largeImage = UploadedFile::fake()
            ->image('main.jpg')
            ->size(config('junket.imaging.max_file_size') + 1);

        foreach (Tour::$imageAttributes as $key) {
            $this->uploadMedia($key, $file, $largeImage)
                ->assertStatus(422)
                ->assertJsonValidationErrors($key);
        }
    }

    /** @test */
    public function tour_images_must_be_images()
    {
        $this->loginAs($this->client);

        $pdfFile = UploadedFile::fake()
            ->create('document.pdf', 5000);

        foreach (Tour::$imageAttributes as $key) {
            $this->uploadMedia($key, $file, $pdfFile)
            ->assertStatus(422)
            ->assertJsonValidationErrors($key);
        }
    }

    /** @test */
    public function only_the_creator_can_upload_media()
    {
        $this->signIn('client');

        $this->json('PUT', $this->tourRoute('media'), [])
            ->assertStatus(403);
    }

    /** @test */
    public function the_creator_can_update_a_tours_audio()
    {
        $this->loginAs($this->client);

        $audioFile = UploadedFile::fake()
            ->create('audio.mp3', 5000);

        foreach (Tour::$audioAttributes as $key) {
            $this->uploadMedia($key, $file, $audioFile)
            ->assertStatus(200);

            $this->assertNotEmpty($this->tour->fresh()->$key);

            \Storage::disk('s3')->assertExists($file);
        }
    }

    /** @test */
    public function tour_audio_uploads_must_be_of_valid_type_and_size()
    {
        $this->loginAs($this->client);

        $pdfFile = UploadedFile::fake()
            ->create('audio.pdf', 5000);

        $largeFile = UploadedFile::fake()
            ->create('audio.mp3', config('junket.audio.max_file_size') + 1);

        foreach (Tour::$audioAttributes as $key) {
            $this->uploadMedia($key, $file, $pdfFile)
                ->assertStatus(422)
                ->assertJsonValidationErrors($key);

            $this->uploadMedia($key, $file, $largeFile)
                ->assertStatus(422)
                ->assertJsonValidationErrors($key);
        }
    }
}
