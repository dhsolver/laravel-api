<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TourStop;
use App\StopRoute;
use App\User;
use App\Mail\WelcomeMail;

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
        $user = User::first();
        $token = '-testtoken-';

        \Mail::to('jonwillard11@Gmail.com')
            ->send(new WelcomeMail($user));
    }

    public function getDistanceBetweenPoints()
    {
        $stop1 = TourStop::find(100022074);
        $stop2 = TourStop::find(100022075);
        $tour = $stop1->tour;

        $path = StopRoute::where('stop_id', $stop1->id)
            ->where('next_stop_id', $stop2->id)
            ->inOrder()
            ->get();

        $total = 0;
        $previousPoint = $tour->location;
        foreach ($path as $point) {
            $distance = $this->getDistance(
                $previousPoint->latitude,
                $previousPoint->longitude,
                $point->latitude,
                $point->longitude
            );

            $total += $distance;

            $previousPoint = $point;
        }

        $total += $this->getDistance(
            $previousPoint->latitude,
            $previousPoint->longitude,
            $stop2->location->latitude,
            $stop2->location->longitude
        );

        dd($total);
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2, $unit = null)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = (float) $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == 'K') {
            return ($miles * 1.609344);
        } elseif ($unit == 'N') {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    public function testDistance()
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
