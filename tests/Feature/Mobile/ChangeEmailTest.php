<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Http\Requests\ChangeEmailRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangeEmailActivation;
use Carbon\Carbon;

class ChangeEmailTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    public function setup()
    {
        parent::setup();

        $this->withoutExceptionHandling();
    }

    /** @test */
    public function a_user_can_submit_a_request_to_change_their_email()
    {
        $this->signIn('user');

        $data = [
            'email' => 'test@test.com',
            'email_confirmation' => 'test@test.com',
        ];

        $this->assertCount(0, ChangeEmailRequest::all());

        $this->postJson(route('mobile.profile.change-email', $data))
            ->assertStatus(200);

        $this->assertCount(1, ChangeEmailRequest::all());
    }

    /** @test */
    public function a_change_email_request_should_dispatch_an_email_to_the_new_address()
    {
        Mail::fake();

        $this->signIn('user');

        $data = [
            'email' => 'test@test.com',
            'email_confirmation' => 'test@test.com',
        ];

        $this->postJson(route('mobile.profile.change-email', $data))
            ->assertStatus(200);

        Mail::assertSent(ChangeEmailActivation::class, function ($mail) {
            return $mail->hasTo('test@test.com');
        });
    }

    /** @test */
    public function a_user_cannot_change_their_email_to_an_existing_email()
    {
        $this->withExceptionHandling();

        $this->signIn('user');

        $data = [
            'email' => $this->signInUser->email,
            'email_confirmation' => $this->signInUser->email,
        ];

        $this->postJson(route('mobile.profile.change-email', $data))
            ->assertStatus(422);
    }

    /** @test */
    public function a_user_must_enter_the_new_email_twice_to_change_it()
    {
        $this->withExceptionHandling();

        $this->signIn('user');

        $data = [
            'email' => 'test@test.com',
            'email_confirmation' => 'doesnt match',
        ];

        $this->postJson(route('mobile.profile.change-email', $data))
            ->assertStatus(422);
    }

    /** @test */
    public function a_user_can_confirm_their_change_email_request_with_an_activation_code()
    {
        $this->signIn('user');

        $oldEmail = $this->signInUser->email;
        $newEmail = 'new@test.com';
        $this->assertTrue($oldEmail != $newEmail);

        $data = ['email' => $newEmail, 'email_confirmation' => $newEmail];
        $this->postJson(route('mobile.profile.change-email', $data))
            ->assertStatus(200);

        $cer = ChangeEmailRequest::first();

        $this->postJson(route('mobile.profile.change-email.confirm', ['code' => $cer->activation_code]))
            ->assertStatus(200);

        $this->assertNotNull($cer->fresh()->confirmed_at);
        $this->assertEquals($newEmail, $this->signInUser->fresh()->email);
    }

    /** @test */
    public function a_change_email_confirmation_requires_a_valid_activation_code()
    {
        $this->withExceptionHandling();

        $this->signIn('user');

        $oldEmail = $this->signInUser->email;
        $newEmail = 'new@test.com';

        $data = ['email' => $newEmail, 'email_confirmation' => $newEmail];
        $this->postJson(route('mobile.profile.change-email', $data))
            ->assertStatus(200);

        $this->postJson(route('mobile.profile.change-email.confirm', ['code' => '123456']))
            ->assertStatus(404);
    }

    /** @test */
    public function a_user_cannot_confirm_using_an_old_activation_code()
    {
        $this->withExceptionHandling();

        $this->signIn('user');

        $newEmail = 'new@test.com';

        $data = ['email' => $newEmail, 'email_confirmation' => $newEmail];
        $this->postJson(route('mobile.profile.change-email', $data))
            ->assertStatus(200);

        $cer = ChangeEmailRequest::first();
        $cer->update(['expires_at' => Carbon::now()->subMinutes(1)]);

        $this->postJson(route('mobile.profile.change-email.confirm', ['code' => $cer->activation_code]))
            ->assertStatus(419);
    }

    /** @test */
    public function a_user_can_only_confirm_the_activation_once()
    {
        $this->signIn('user');

        $newEmail = 'new@test.com';

        $data = ['email' => 'new@test.com', 'email_confirmation' => 'new@test.com'];
        $this->postJson(route('mobile.profile.change-email', $data))
            ->assertStatus(200);

        $cer = ChangeEmailRequest::first();

        $this->postJson(route('mobile.profile.change-email.confirm', ['code' => $cer->activation_code]))
            ->assertStatus(200);

        $this->postJson(route('mobile.profile.change-email.confirm', ['code' => $cer->activation_code]))
            ->assertStatus(419);
    }

    /** @test */
    public function a_user_cannot_confirm_another_users_activation_code()
    {
        $this->signIn('user');

        $otherUser = createUser('user');

        $cer = ChangeEmailRequest::create([
            'expires_at' => Carbon::now()->addMinutes(10),
            'user_id' => $otherUser->id,
            'new_email' => 'new@test.com',
            'activation_code' => '123456',
        ]);

        $this->postJson(route('mobile.profile.change-email.confirm', ['code' => '123456']))
            ->assertStatus(403);
    }

    /** @test */
    public function an_activation_code_should_be_case_insensitive()
    {
        $this->signIn('user');

        $cer = ChangeEmailRequest::create([
            'expires_at' => Carbon::now()->addMinutes(10),
            'user_id' => $this->signInUser->id,
            'new_email' => 'new@test.com',
            'activation_code' => 'ABCDEF',
        ]);

        $this->postJson(route('mobile.profile.change-email.confirm', ['code' => 'abcdef']))
            ->assertStatus(200);
    }
}
