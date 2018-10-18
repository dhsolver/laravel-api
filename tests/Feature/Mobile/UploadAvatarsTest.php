<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Illuminate\Http\UploadedFile;

class UploadAvatarsTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public function setUp()
    {
        parent::setUp();

        \Storage::fake('s3');
    }

    /**
     * Helper to upload avatar file and return the filename.
     *
     * @param String $key
     * @param String $filename
     * @param [type] $image
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function uploadAvatar(&$filename, $image = null)
    {
        if (empty($image)) {
            $image = UploadedFile::fake()
                ->image('avatar.png', 500, 500)
                ->size(config('junket.imaging.max_file_size') - 1);
        }

        $resp = $this->postJson(route('mobile.profile.avatar'), [
            'image' => $image,
        ]);

        try {
            $filename = $resp->getData()->data->file;
        } catch (\Exception $ex) {
            $filename = null;
        }

        return $resp;
    }

    /** @test */
    public function a_user_can_upload_an_avatar()
    {
        $this->withoutExceptionHandling();

        $this->signIn('user');

        $this->assertNull($this->signInUser->avatar);

        $this->uploadAvatar($file)
            ->assertStatus(200);

        $this->assertNotNull($this->signInUser->fresh()->avatar);
    }

    /** @test */
    public function avatar_uploads_should_return_a_profile_resource_including_the_avatar_url()
    {
        $this->signIn('user');

        $this->uploadAvatar($file)
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'first_name', 'last_name', 'avatar_url']]);
    }

    /** @test */
    public function avatars_must_be_image_file_types()
    {
        $this->signIn('user');

        $pdfFile = UploadedFile::fake()
            ->create('document.pdf', 5000);

        $this->uploadAvatar($file, $pdfFile)
            ->assertStatus(422);
    }
}
