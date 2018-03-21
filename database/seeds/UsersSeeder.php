<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'email' => 'admin@wejunket.com',
            'name' => 'Master Account',
            'password' => bcrypt('qweqwe'),
        ]);
        \Bouncer::assign('superadmin')->to($user);

        $user = User::create([
            'email' => 'user@test.com',
            'name' => 'Test User',
            'password' => bcrypt('qweqwe'),
        ]);
        \Bouncer::assign('user')->to($user);

        $user = User::create([
            'email' => 'admin@test.com',
            'name' => 'Test Admin',
            'password' => bcrypt('qweqwe'),
        ]);
        \Bouncer::assign('admin')->to($user);

        $user = User::create([
            'email' => 'business@test.com',
            'name' => 'Test Business',
            'password' => bcrypt('qweqwe'),
        ]);
        \Bouncer::assign('business')->to($user);
    }
}
