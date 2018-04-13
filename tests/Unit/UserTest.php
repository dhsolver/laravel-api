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
        $user = create('App\User')->assignRole('user');

        $this->assertTrue($user->role === 'user');
    }

    /** @test */
    public function it_can_identify_if_it_owns_one_of_its_tour()
    {
        $user = create('App\User')->assignRole('client');
        $user2 = create('App\User')->assignRole('user');

        $tour = create('App\Tour', ['user_id' => $user->id]);

        $this->assertTrue($user->ownsTour($tour->id));
        $this->assertFalse($user2->ownsTour($tour->id));
    }
}
