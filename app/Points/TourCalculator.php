<?php

namespace App\Points;

use App\UserScore;

class TourCalculator implements PointsCalculator
{
    /**
     * The adventure Tour.
     *
     * @var \App\Tour
     */
    public $tour;

    /**
     * The Tour's stops, in order, with all loaded data.
     *
     * @var \App\TourStop
     */
    public $stops;

    /**
     * Create a new instance.
     *
     * @param \App\Tour tour
     */
    public function __construct($tour)
    {
        $this->tour = $tour;
        $this->stops = $tour->stops()->ordered()->get();
    }

    /**
     * Get the total number of stops on the Tour.
     *
     * @return int
     */
    public function getTotalStops()
    {
        return $this->tour->stops()->count();
    }

    /**
     * Check if the given score qualifies for a trophy.
     *
     * @param UserScore $score
     * @return bool
     */
    public function scoreQualifiesForTrophy($score)
    {
        $trophyRate = config('junket.points.trophy_rate', 70);

        return $score->stops_visited >= (floatval($score->total_stops) * (floatval($trophyRate) / 100));
    }

    /**
     * Return empty value for par because there is no par for
     * regular Tours.
     *
     * @return float
     */
    public function getPar()
    {
        return (float) 0;
    }

    /**
     * Get the number of points awarded to the user.
     *
     * @param UserScore $scoreCard
     * @return int
     */
    public function getPoints(UserScore $scoreCard)
    {
        $pointsPer = config('junket.points.per_stop', 1);

        return (int) ($pointsPer * $scoreCard->stops_visited);
    }
}
