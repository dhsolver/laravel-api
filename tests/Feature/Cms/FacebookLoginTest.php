<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Two\FacebookProvider;
use App\User;

class FacebookLoginTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    /**
     * Mock the Socialite Factory, so we can hijack the OAuth Request.
     * @param  string  $email
     * @param  string  $token
     * @param  int $id
     * @return void
     */
    public function mockSocialiteFacade($email = 'test@test.com', $token = 'FAKETOKEN', $id = 100000, $name = 'Fake User')
    {
        $socialiteUser = $this->createMock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->token = $token;
        $socialiteUser->id = $id;
        $socialiteUser->email = $email;
        $socialiteUser->name = $name;

        $provider = $this->createMock(FacebookProvider::class);
        $provider->expects($this->any())
            ->method('user')
            ->willReturn($socialiteUser);

        $provider->expects($this->any())
            ->method('userFromToken')
            ->willReturn($socialiteUser);

        $stub = $this->createMock(Socialite::class);
        $stub->expects($this->any())
            ->method('driver')
            ->willReturn($provider);

        // Replace Socialite Instance with our mock
        $this->app->instance(Socialite::class, $stub);
    }

    /** @test */
    public function a_user_can_login_with_a_facebook_access_token()
    {
        $this->mockSocialiteFacade('test@test.com');

        $this->json('POST', route('facebook.login'), ['token' => 'fake'])
            ->assertStatus(200)
            ->assertJsonStructure(['user', 'token'])
            ->assertJson(['user' => [
                'email' => 'test@test.com',
                'name' => 'Fake User',
            ]]);
    }

    /** @test */
    public function it_creates_a_new_user_if_none_matches()
    {
        $this->assertCount(0, User::all());

        $this->mockSocialiteFacade('test@test.com');

        $this->json('POST', route('facebook.login'), ['token' => 'fake'])
            ->assertStatus(200);

        $this->assertCount(1, User::all());
    }

    /** @test */
    public function it_logs_into_accounts_with_matching_facebook_emails()
    {
        $user = createUser('user');

        $this->assertCount(1, User::all());

        $this->mockSocialiteFacade($user->email);

        $this->json('POST', route('facebook.login'), ['token' => 'fake'])
            ->assertStatus(200);

        $this->assertCount(1, User::all());
    }

    /** @test */
    public function it_logs_into_accounts_with_matching_facebook_ids()
    {
        $user = createUser('user');

        $this->assertCount(1, User::all());

        $this->mockSocialiteFacade($user->email);

        $this->json('POST', route('facebook.login'), ['token' => 'fake'])
            ->assertStatus(200);

        $this->assertCount(1, User::all());

        $user->update(['email' => 'anything@else.com']);

        $this->json('POST', route('facebook.login'), ['token' => 'fake'])
            ->assertStatus(200);

        $this->assertCount(1, User::all());
    }

    /** @test */
    public function it_can_create_a_client_account_if_specified_otherwise_it_creates_a_mobile_user()
    {
        $this->mockSocialiteFacade('test@test.com');

        $this->json('POST', route('facebook.login'), ['token' => 'fake', 'role' => 'client'])
            ->assertStatus(200)
            ->assertJsonFragment(['role' => 'client']);
    }
}
