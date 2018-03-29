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
}
