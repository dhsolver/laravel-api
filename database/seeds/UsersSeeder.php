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
        ])->assignRole('superadmin');

        $user = User::create([
            'email' => 'user@test.com',
            'name' => 'Test User',
            'password' => bcrypt('qweqwe'),
        ])->assignRole('user');

        $user = User::create([
            'email' => 'admin@test.com',
            'name' => 'Test Admin',
            'password' => bcrypt('qweqwe'),
        ])->assignRole('admin');

        $user = User::create([
            'email' => 'client@test.com',
            'name' => 'Test Client',
            'password' => bcrypt('qweqwe'),
        ])->assignRole('client');
    }
}
