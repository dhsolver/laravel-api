<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestStuff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:stuff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Just a way to test via console';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $lat = 25.811481872869393;
        $lon = -80.13444900512695;

        // 3959 * acos( cos( radians(lat1) )
        // * cos( radians(lat2) )
        // * cos( radians(lon2) - radians(lon1)) + sin(radians(lat1))
        // * sin( radians(lat2) )

        $distanceQuery = "(3959 * acos( cos( radians($lat) ) * cos( radians(locations.latitude) ) * cos( radians(locations.longitude) - radians($lon)) + sin(radians($lat)) * sin( radians(locations.latitude) )))";

        $results = \App\Tour::leftJoin('locations', function ($join) {
            $join->on('tours.id', '=', 'locations.locationable_id')
                ->where('locations.locationable_type', '=', "App\Tour");
        })
            ->selectRaw("tours.*, ($distanceQuery) as distance")
            ->whereNotNull('locations.latitude')
            ->whereNotNull('locations.longitude')
            ->orderBy('distance')
            ->get();

        dd($results->first()->toArray());
    }
}
