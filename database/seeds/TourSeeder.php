<?php

use Illuminate\Database\Seeder;
use App\Tour;

class TourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        create(Tour::class, [], 10);
    }
}
