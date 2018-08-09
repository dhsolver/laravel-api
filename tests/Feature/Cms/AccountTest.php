<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Hash;

class AccountTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $client;

    public function setUp()
    {
        parent::setUp();

        $this->client = createUser('client', 'secret');
    }

    /** @test */
    public function a_user_can_update_their_email()
    {
        $this->loginAs($this->client);

        $data = $this->client->toArray();
        $data['email'] = 'new@test.com';

        $this->json('PATCH', '/cms/profile', $data)
            ->assertStatus(200)
            ->assertJsonFragment(['email' => 'new@test.com']);
    }

    /** @test */
    public function a_user_can_update_their_name()
    {
        $this->loginAs($this->client);

        $data = $this->client->toArray();
        $data['name'] = 'New Name';

        $this->json('PATCH', '/cms/profile', $data)
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);
    }

    /** @test */
    public function a_user_can_update_their_company_name()
    {
        $this->loginAs($this->client);

        $data = $this->client->toArray();
        $data['company_name'] = 'New Company';

        $this->json('PATCH', '/cms/profile', $data)
            ->assertStatus(200)
            ->assertJsonFragment(['company_name' => 'New Company']);
    }

    /** @test */
    public function a_user_cannot_change_their_email_to_one_that_is_already_in_use()
    {
        $this->loginAs($this->client);

        $otherUser = create(\App\Client::class);

        $data = $this->client->toArray();
        $data['email'] = $otherUser->email;

        $this->json('PATCH', '/cms/profile', $data)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function a_user_can_change_their_password()
    {
        $this->loginAs($this->client);

        $this->assertTrue(Hash::check('secret', $this->client->user->fresh()->password));

        $password = 'new password';

        $this->json('PATCH', '/cms/profile/password', [
            'old_password' => 'secret',
            'password' => $password,
            'password_confirmation' => $password
        ])->assertStatus(200);

        $this->assertTrue(Hash::check($password, $this->client->user->fresh()->password));
        $this->assertFalse(Hash::check('invalid', $this->client->user->fresh()->password));
    }
}
