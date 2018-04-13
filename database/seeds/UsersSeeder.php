<?php

use Illuminate\Database\Seeder;
use App\SuperAdmin;
use App\MobileUser;
use App\Admin;
use App\Client;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superadmin = SuperAdmin::create([
            'email' => 'admin@wejunket.com',
            'name' => 'Master Account',
            'password' => bcrypt('qweqwe'),
        ]);

        MobileUser::create([
            'email' => 'user@test.com',
            'name' => 'Test User',
            'password' => bcrypt('qweqwe'),
        ]);

        Admin::create([
            'email' => 'admin@test.com',
            'name' => 'Test Admin',
            'password' => bcrypt('qweqwe'),
        ]);

        Client::create([
            'email' => 'client@test.com',
            'name' => 'Test Client',
            'password' => bcrypt('qweqwe'),
        ]);

        create(Client::class, [], 50);
    }
}
