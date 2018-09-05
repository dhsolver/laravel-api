<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;

class EditProfileTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    /** @test */
    public function a_user_can_view_their_profile()
    {
        $this->signIn('user');

        $this->getJson(route('mobile.profile.user'))
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
            ->assertJsonFragment(['name' => $otherUser->name])
            ->assertDontSee($otherUser->email);
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
}
