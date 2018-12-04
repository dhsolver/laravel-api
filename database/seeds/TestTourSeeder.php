<?php

use Illuminate\Database\Seeder;
use Tests\HasTestTour;
use App\User;

class TestTourSeeder extends Seeder
{
    use HasTestTour;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'lance.zaal@iworksllc.com')->first();

        $this->createTestAdventure(true, $user);
    }
}
