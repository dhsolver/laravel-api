<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Illuminate\Http\UploadedFile;

class UploadMediaTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public function setUp()
    {
        parent::setUp();

        \Storage::fake('s3');
    }

    /**
     * Helper to upload media and return the filename.
     *
     * @param String $key
     * @param String $filename
     * @param [type] $image
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function uploadMedia(&$filename, $type = 'image', $image = null)
    {
        if (empty($image)) {
            $image = UploadedFile::fake()
                ->image('main.jpg', 500, 500)
                ->size(config('junket.imaging.max_file_size') - 1);
        }

        $resp = $this->json('POST', route('cms.media'), [
            $type => $image,
        ]);

        try {
            $filename = $resp->getData()->data->file;
        } catch (\Exception $ex) {
            $filename = null;
        }

        return $resp;
    }

    /** @test */
    public function a_client_can_upload_media()
    {
        $this->signIn('client');

        $this->uploadMedia($file)->assertStatus(200);
    }

    /** @test */
    public function an_admin_can_upload_media()
    {
        $this->signIn('admin');

        $this->uploadMedia($file)->assertStatus(200);
    }

    /** @test */
    public function a_superadmin_can_upload_media()
    {
        $this->signIn('superadmin');

        $this->uploadMedia($file)->assertStatus(200);
    }

    /** @test */
    public function a_mobile_user_cannot_upload_media()
    {
        $this->signIn('user');

        $this->uploadMedia($file)->assertStatus(403);
    }

    /** @test */
    public function media_uploads_should_return_the_id_filename_and_path()
    {
        $this->signIn('client');

        $this->uploadMedia($file)
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'file', 'path']]);
    }

    /** @test */
    public function images_must_be_under_max_file_size()
    {
        $this->signIn('client');

        $largeImage = UploadedFile::fake()
            ->image('main.jpg')
            ->size(config('junket.imaging.max_file_size') + 1);

        $this->uploadMedia($file, 'image', $largeImage)
            ->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    /** @test */
    public function images_must_be_image_file_types()
    {
        $this->signIn('client');

        $pdfFile = UploadedFile::fake()
            ->create('document.pdf', 5000);

        $this->uploadMedia($file, 'image', $pdfFile)
            ->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    /** @test */
    public function audio_must_be_under_max_file_size()
    {
        $this->signIn('client');

        $largeFile = UploadedFile::fake()
            ->create('audio.mp3', config('junket.audio.max_file_size') + 1);

        $this->uploadMedia($file, 'audio', $largeFile)
            ->assertStatus(422)
            ->assertJsonValidationErrors('audio');
    }

    /** @test */
    public function audio_must_be_a_mp3_files()
    {
        $this->signIn('client');

        $pdfFile = UploadedFile::fake()
            ->create('document.pdf', 5000);

        $this->uploadMedia($file, 'audio', $pdfFile)
            ->assertStatus(422)
            ->assertJsonValidationErrors('audio');
    }

    /** @test */
    public function media_uploads_should_have_small_and_icon_paths()
    {
        $this->signIn('client');

        $this->uploadMedia($file)
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['path', 'small_path', 'icon_path']]);
    }
}
