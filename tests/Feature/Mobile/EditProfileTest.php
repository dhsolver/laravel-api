<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Hash;

class EditProfileTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    /** @test */
    public function a_user_can_view_their_profile()
    {
        $this->signIn('user');

        $this->getJson(route('mobile.profile.show', ['user' => $this->signInUser]))
            ->assertStatus(200)
            ->assertJsonFragment(['name' => $this->signInUser->name])
            ->assertSee($this->signInUser->email);
    }

    /** @test */
    public function a_user_can_view_another_users_profile()
    {
        $otherUser = createUser('user');

        $this->signIn('user');

        $this->getJson(route('mobile.profile.show', ['user' => $otherUser]))
            ->assertStatus(200)
            ->assertJsonFragment(['name' => $otherUser->name]);
    }

    /** @test */
    public function a_user_can_update_their_name()
    {
        $this->signIn('user');

        $data = $this->signInUser->toArray();
        $data['name'] = 'Foo Bar';

        $this->postJson(route('mobile.profile.update', $data))
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Foo Bar']);
    }

    /** @test */
    public function a_user_can_update_their_email()
    {
        $this->signIn('user');

        $data = $this->signInUser->toArray();
        $data['email'] = 'foo@bar.com';

        $this->postJson(route('mobile.profile.update', $data))
            ->assertStatus(200)
            ->assertJsonFragment(['email' => 'foo@bar.com']);
    }

    /** @test */
    public function a_user_should_not_see_another_users_sensitive_info()
    {
        $otherUser = createUser('user');

        $this->signIn('user');

        $this->getJson(route('mobile.profile.show', ['user' => $otherUser]))
            ->assertStatus(200)
            ->assertJsonMissing(['email' => $otherUser->email])
            ->assertJsonMissing(['fb_id' => $otherUser->fb_id]);
    }

    /** @test */
    public function a_users_profile_should_contian_facebook_id()
    {
        $this->signIn('user');

        $this->signInUser->update(['fb_id' => 12345]);

        $this->getJson(route('mobile.profile.show', ['user' => $this->signInUser]))
            ->assertStatus(200)
            ->assertJsonFragment(['fb_id' => '12345']);
    }

    /** @test */
    public function a_users_profile_should_always_contain_a_gravatar_url()
    {
        $this->signIn('user');

        $hash = md5($this->signInUser->email);

        $this->getJson(route('mobile.profile.show', ['user' => $this->signInUser]))
            ->assertStatus(200)
            ->assertJsonFragment(['avatar_url' => "https://www.gravatar.com/avatar/$hash?s=2048&d=identicon&rating=g"]);
    }

    /** @test */
    public function a_user_can_update_their_password()
    {
        $this->signIn('user');

        $password = 'new password';

        $this->patchJson('/mobile/profile/password', [
            'password' => $password,
            'password_confirmation' => $password
        ])->assertStatus(200);

        $this->assertTrue(Hash::check($password, $this->signInUser->fresh()->password));
        $this->assertFalse(Hash::check('invalid', $this->signInUser->fresh()->password));
    }
}
