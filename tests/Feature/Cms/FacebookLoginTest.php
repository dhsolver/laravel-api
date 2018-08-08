<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\Two\User;

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
    public function mockSocialiteFacade($email = 'test@test.com', $token = 'FAKETOKEN', $id = 100000)
    {
        $socialiteUser = $this->createMock(User::class);
        $socialiteUser->token = $token;
        $socialiteUser->id = $id;
        $socialiteUser->email = $email;

        $provider = $this->createMock(FacebookProvider::class);
        $provider->expects($this->any())
            ->method('user')
            ->willReturn($socialiteUser);

        $stub = $this->createMock(Socialite::class);
        $stub->expects($this->any())
            ->method('driver')
            ->willReturn($provider);

        // Replace Socialite Instance with our mock
        $this->app->instance(Socialite::class, $stub);
    }

    /** @test */
    public function the_api_has_a_redirect_for_facebook_oauth()
    {
        $response = $this->call('GET', route('facebook.login'));

        $this->assertContains('facebook.com', $response->getTargetUrl());
    }

    /** @test */
    public function it_retrieves_github_request_and_creates_a_new_user()
    {
        // Mock the Facade and return a User Object with the email 'foo@bar.com'
        $this->mockSocialiteFacade('test@test.com');

        $this->get(route('facebook.callback'));
        // ->seePageIs('/home');

        $this->seeInDatabase('users', [
            'email' => 'test@test.com',
        ]);
    }
}
