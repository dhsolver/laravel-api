<?php

namespace App\Points;

use App\ScoreCard;

class TourCalculator extends DistanceCalculator implements IPointsCalculator
{
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
     * @param ScoreCard $score
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
     * @param ScoreCard $scoreCard
     * @return int
     */
    public function getPoints(ScoreCard $scoreCard)
    {
        $pointsPer = config('junket.points.per_stop', 1);

        return (int) ($pointsPer * $scoreCard->stops_visited);
    }
}
