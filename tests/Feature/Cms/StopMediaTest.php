<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Illuminate\Http\UploadedFile;
use App\TourStop;

class StopMediaTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $business;

    public function setUp()
    {
        parent::setUp();

        \Storage::fake('s3');

        $this->business = createUser('business');

        $this->tour = create('App\Tour', ['user_id' => $this->business->id]);

        $this->stop = create('App\TourStop', ['tour_id' => $this->tour->id, 'order' => 1]);
    }

    /** @test */
    public function a_user_can_update_a_stops_images()
    {
        $this->loginAs($this->business);

        foreach (TourStop::$imageAttributes as $key) {
            $this->uploadMedia($key, $file)
               ->assertStatus(200);

            \Storage::disk('s3')->assertExists($file);

            $this->assertNotEmpty($this->stop->fresh()->toArray()[$key]);
        }
    }

    /** @test */
    public function stop_images_must_be_of_valid_type_and_size()
    {
        $this->loginAs($this->business);

        $largeImage = UploadedFile::fake()
            ->image('main.jpg')
            ->size(config('junket.imaging.max_file_size') + 1);

        $pdfFile = UploadedFile::fake()
            ->create('document', 5000);

        foreach (TourStop::$imageAttributes as $key) {
            $this->uploadMedia($key, $file, $largeImage)
                ->assertStatus(422)
                ->assertSee('may not be greater than');

            $this->uploadMedia($key, $file, $pdfFile)
                ->assertStatus(422)
                ->assertSee('must be an image');
        }
    }

    /** @test */
    public function stop_media_can_not_be_updated_by_another_user()
    {
        $this->signIn('business');

        $this->json('PUT', $this->stopRoute('media'), [])
            ->assertStatus(403);
    }

    /** @test */
    public function the_creator_can_update_a_stops_audio()
    {
        $this->loginAs($this->business);

        $audioFile = UploadedFile::fake()
            ->create('audio.mp3', 5000);

        foreach (TourStop::$audioAttributes as $key) {
            $this->uploadMedia($key, $file, $audioFile)
            ->assertStatus(200);

            $this->assertNotEmpty($this->stop->fresh()->$key);

            \Storage::disk('s3')->assertExists($file);
        }
    }

    /** @test */
    public function stop_audio_must_be_of_valid_type_and_size()
    {
        $this->loginAs($this->business);

        $pdfFile = UploadedFile::fake()
            ->create('audio.pdf');

        $largeFile = UploadedFile::fake()
            ->create('audio.mp3', config('junket.audio.max_file_size') + 1);

        foreach (TourStop::$audioAttributes as $key) {
            $this->uploadMedia($key, $file, $pdfFile)
                ->assertStatus(422)
                ->assertSee('file of type:');

            $this->uploadMedia($key, $file, $largeFile)
                ->assertStatus(422)
                ->assertSee('may not be greater than');
        }
    }

    /**
     * Helper to provide route to the class tour based on named routes.
     *
     * @param String $name
     * @return void
     */
    public function stopRoute($name)
    {
        return route("cms.stops.$name", ['tour' => $this->tour->id, 'stop' => $this->stop->id]);
    }

    /**
     * Helper to upload tour media and return the filename.
     *
     * @param String $key
     * @param String $filename
     * @param [type] $image
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function uploadMedia($key, &$filename, $file = null)
    {
        if (empty($file)) {
            $file = UploadedFile::fake()
                ->image('main.jpg', 500, 500)
                ->size(config('junket.imaging.max_file_size') - 1);
        }

        $resp = $this->json('PUT', $this->stopRoute('media'), [$key => $file]);

        try {
            $filename = $resp->getData()->$key;
        } catch (\Exception $ex) {
            $filename = null;
        }

        return $resp;
    }
}
