<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\User;
use Notification;
use App\Notifications\ResetPasswordNotification;
use Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class AuthTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    /**
     * Helper function to determine if a user with the given roll may login.
     *
     * @param [type] $role
     * @return void
     */
    public function assertRoleLogsIn($role)
    {
        $user = createUser($role);

        $this->json('POST', '/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ])->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email, 'role' => $role]);
    }

    /** @test */
    public function a_user_with_any_role_can_log_in()
    {
        $this->withExceptionHandling();

        $this->assertRoleLogsIn('client');
        $this->assertRoleLogsIn('user');
        $this->assertRoleLogsIn('admin');
        $this->assertRoleLogsIn('superadmin');
    }

    /** @test */
    public function a_user_cannot_access_the_cms()
    {
        $this->withExceptionHandling();

        $this->signIn('user')
            ->json('GET', '/cms/tours')
            ->assertStatus(403);
    }

    /** @test */
    public function a_superadmin_can_access_all_sections()
    {
        $this->withExceptionHandling();

        $this->signIn('superadmin')->json('GET', '/admin/session')->assertStatus(200);
        $this->signIn('superadmin')->json('GET', '/cms/session')->assertStatus(200);
        $this->signIn('superadmin')->json('GET', '/mobile/tours')->assertStatus(200);
    }

    /** @test */
    public function an_admin_can_access_all_sections()
    {
        $this->withExceptionHandling();

        $this->signIn('admin')->json('GET', '/admin/session')->assertStatus(200);
        $this->signIn('admin')->json('GET', '/cms/session')->assertStatus(200);
        $this->signIn('admin')->json('GET', '/mobile/tours')->assertStatus(200);
    }

    /** @test */
    public function a_user_can_only_access_the_mobile_api()
    {
        $this->withExceptionHandling();

        $this->signIn('user')->json('GET', '/admin/session')->assertStatus(403);
        $this->signIn('user')->json('GET', '/cms/session')->assertStatus(403);
        $this->signIn('user')->json('GET', '/mobile/tours')->assertStatus(200);
    }

    /** @test */
    public function a_client_can_access_the_cms_and_the_mobile_api()
    {
        $this->withExceptionHandling();

        $this->signIn('client')->json('GET', '/admin/session')->assertStatus(403);
        $this->signIn('client')->json('GET', '/cms/session')->assertStatus(200);
        $this->signIn('client')->json('GET', '/mobile/tours')->assertStatus(200);
    }

    /** @test */
    public function a_guest_cant_access_shit()
    {
        $this->withExceptionHandling();

        $this->json('GET', '/admin/session')->assertStatus(401);
        $this->json('GET', '/cms/session')->assertStatus(401);
        $this->json('GET', '/mobile/tours')->assertStatus(401);
    }

    /** @test */
    public function a_mobile_user_can_register()
    {
        $this->withoutExceptionHandling();

        $this->json('POST', '/auth/signup', [
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => 'sdgdhe2354',
            'password_confirmation' => 'sdgdhe2354',
            'role' => 'user',
        ])->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test User', 'role' => 'user']);
    }

    /** @test */
    public function a_client_can_register()
    {
        $this->json('POST', '/auth/signup', [
            'name' => 'Test client',
            'email' => 'client@test.com',
            'password' => 'sdgdhe2354',
            'password_confirmation' => 'sdgdhe2354',
            'role' => 'client',
        ])->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test client', 'role' => 'client']);
    }

    /** @test */
    public function an_admin_cannot_register()
    {
        $this->json('POST', '/auth/signup', [
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'sdgdhe2354',
            'password_confirmation' => 'sdgdhe2354',
            'role' => 'admin',
        ])->assertStatus(200);

        $user = User::where('email', 'admin@test.com')->first();
        $this->assertEquals('user', $user->role);
    }

    /** @test */
    public function a_user_can_forget_and_reset_their_password()
    {
        Notification::fake();

        $user = create(User::class);

        $this->json('POST', '/auth/forgot-password', ['email' => $user->email])
            ->assertStatus(200);

        $token = '';

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user, &$token) {
            $token = $notification->token;
            return $user->email == $notification->email;
        });

        $password = 'new password';

        $this->json('POST', '/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => $password,
            'password_confirmation' => $password
        ])->assertStatus(200);

        $this->assertTrue(Hash::check($password, $user->fresh()->password));
        $this->assertFalse(Hash::check('invalid', $user->fresh()->password));
    }

    /** @test */
    public function when_a_user_registers_they_are_sent_a_welcome_email()
    {
        Mail::fake();

        $this->json('POST', '/auth/signup', [
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => 'sdgdhe2354',
            'password_confirmation' => 'sdgdhe2354',
            'role' => 'user',
        ])->assertStatus(200);

        Mail::assertSent(WelcomeMail::class, function ($mail) {
            return $mail->hasTo('user@test.com');
        });
    }

    /** @test */
    public function when_a_user_registers_a_confirm_email_token_is_generated()
    {
        $this->json('POST', '/auth/signup', [
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => 'sdgdhe2354',
            'password_confirmation' => 'sdgdhe2354',
            'role' => 'user',
        ])->assertStatus(200);

        $user = User::first();
        $this->assertNotNull($user->email_confirmation_token);
    }

    /** @test */
    public function a_user_can_confirm_their_email_after_registration()
    {
        $this->withoutExceptionHandling();

        $this->json('POST', '/auth/signup', [
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => 'sdgdhe2354',
            'password_confirmation' => 'sdgdhe2354',
            'role' => 'user',
        ])->assertStatus(200);

        $user = User::first();

        $this->assertNull($user->email_confirmed_at);

        $this->postJson(route('confirm-email', ['token' => $user->email_confirmation_token]))
            ->assertStatus(200);

        $this->assertNotNull($user->fresh()->email_confirmed_at);
    }

    /** @test */
    public function a_mobile_user_can_specify_a_zipcode_upon_registration()
    {
        $this->json('POST', '/auth/signup', [
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => 'sdgdhe2354',
            'password_confirmation' => 'sdgdhe2354',
            'zipcode' => '12345',
            'role' => 'user',
        ])->assertStatus(200);

        $user = User::first();
        $this->assertEquals('12345', $user->zipcode);
    }

    /** @test */
    public function disabled_users_cannot_login()
    {
        $user = createUser('client');
        $user->user->deactivate();

        $this->json('POST', '/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ])->assertStatus(401);
    }

    /** @test */
    public function if_an_authenticated_cms_user_is_disabled_they_cannot_complete_any_more_requests()
    {
        $this->signIn('client');

        $this->getJson(route('cms.tours.index'))
            ->assertStatus(200);

        $this->signInUser->user->deactivate();

        $this->assertEquals(0, $this->signInUser->fresh()->active);

        $this->getJson(route('cms.tours.index'))
            ->assertStatus(401);
    }

    /** @test */
    public function if_an_authenticated_mobile_user_is_disabled_they_cannot_complete_any_more_requests()
    {
        $this->signIn('user');

        $this->getJson(route('mobile.tours.all'))
            ->assertStatus(200);

        $this->signInUser->user->deactivate();

        $this->assertEquals(0, $this->signInUser->fresh()->active);

        $this->getJson(route('mobile.tours.all'))
            ->assertStatus(401);
    }
}
