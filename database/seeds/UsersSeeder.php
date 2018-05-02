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

        $client = Client::create([
            'email' => 'client@test.com',
            'name' => 'Test Client',
            'password' => bcrypt('qweqwe'),
        ]);

        create(App\Tour::class, ['user_id' => $client->id], 4);

        create(Client::class, [], 10);
        create(MobileUser::class, [], 10);
        create(Admin::class, [], 3);
    }
}
