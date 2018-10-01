<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_identify_its_role()
    {
        $user = createUser('user');

        $this->assertTrue($user->role === 'user');
    }

    /** @test */
    public function it_can_identify_if_it_owns_one_of_its_tour()
    {
        $user = createUser('client');
        $user2 = createUser('user');

        $tour = create('App\Tour', ['user_id' => $user->id]);

        $this->assertTrue($user->ownsTour($tour->id));
    }

    /** @test */
    public function a_user_can_get_its_role_class()
    {
        $client = create('App\Client');

        $this->assertInstanceOf('App\Client', $client->user->type);
    }

    /** @test */
    public function a_user_can_have_a_subscribe_override_setting()
    {
        $user = createUser('user');

        $user->update(['subscribe_override' => true]);

        $this->assertTrue($user->subscribe_override);
    }
}
