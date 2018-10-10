<?php

namespace App\Adventure;

class AdventureCalculator
{
    /**
     * The adventure Tour.
     *
     * @var App\Tour
     */
    public $tour;

    /**
     * The Tour's stops, in order, with all loaded data.
     *
     * @var App\TourStop
     */
    public $stops;

    /**
     * Create a new instance.
     *
     * @param App\Tour tour
     */
    public function __construct($tour)
    {
        $this->tour = $tour;
        $this->stops = $tour->stops()->ordered()->get();
    }

    public function getShortestRoute()
    {
        // $stops = $this->tour->stops()->ordered()->get();
    }

    public function getRoutePermutations()
    {
    }

    /**
     * Get the distance between two stops.
     *
     * @param App\TourStop $stop1
     * @param App\TourStop $stop2
     * @return void
     */
    public function getDistanceBetweenStops($stop1, $stop2)
    {
        return $this->getDistance(
            $stop1->location->latitude,
            $stop1->location->longitude,
            $stop2->location->latitude,
            $stop2->location->longitude
        );
    }

    /**
     * Get the distance between two points.
     * Supported units: K = kilometers
     *                  N = nautical miles
     *                  default: miles
     *
     * @param float $latitude_1
     * @param float $longitude_1
     * @param float $latitude_2
     * @param float $longitude_2
     * @param float $unit
     * @return float
     */
    public function getDistance($latitude_1, $longitude_1, $latitude_2, $longitude_2, $unit = null)
    {
        $theta = $longitude_1 - $longitude_2;
        $dist = sin(deg2rad($latitude_1)) * sin(deg2rad($latitude_2)) + cos(deg2rad($latitude_1)) * cos(deg2rad($latitude_2)) * cos(deg2rad($theta));
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
}
