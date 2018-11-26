<?php

namespace App\Points;

use App\Exceptions\UntraceableTourException;
use App\ScoreCard;

class AdventureCalculator extends DistanceCalculator implements IPointsCalculator
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
     * Calculate the points that should be awarded to the user
     * based on their time.
     *
     * @param ScoreCard $scoreCard
     * @return int
     * @throws UntraceableTourException
     */
    public function getPoints(ScoreCard $scoreCard)
    {
        return $this->calculatePoints($scoreCard->duration, $scoreCard->par, $scoreCard->skippedStopCount);
    }

    /**
     * Calculate the points that should be awarded to the user
     * based on their time.
     *
     * @param float $timeInMinutes
     * @param float $par
     * @return int
     * @throws UntraceableTourException
     */
    public function calculatePoints($timeInMinutes, $par = null, $skippedStops = 0)
    {
        $time = floatval($timeInMinutes);
        $min = config('junket.points.min_points', 50);
        $max = config('junket.points.max_points', 200);
        $skipPenalty = config('junket.points.skip_penalty', 10);

        if ($par == null) {
            $par = $this->getPar();
        }

        $penalty = $skippedStops * $skipPenalty;

        $total = $max - $penalty;

        if ($time <= $par) {
            // user beat the clock, award all points
            return $total;
        }

        $difference = abs($par - $time);
        $wholeMinutes = floor($difference);
        $partialMinutes = $difference - $wholeMinutes;

        // subtract two points for every minute
        $total -= intval($wholeMinutes * 2);

        // subtract one point for (rounded) half minutes
        if ($partialMinutes > 0.5) {
            $total -= 1;
        }

        if ($total < $min) {
            return $min;
        }

        return $total;
    }

    /**
     * Check if the given score qualifies for a trophy.
     *
     * @param ScoreCard|int $score
     * @return bool
     */
    public function scoreQualifiesForTrophy($score)
    {
        if ($score instanceof ScoreCard) {
            $score = $score->points;
        }

        $trophyRate = config('junket.points.trophy_rate', 70);
        $max = config('junket.points.max_points', 200);

        return $score >= (floatval($max) * (floatval($trophyRate) / 100));
    }

    /**
     * Get the average amount of time (minutes) that it should
     * take to finish the tour.
     *
     * @return float
     * @throws UntraceableTourException
     */
    public function getPar()
    {
        list($route, $distance) = $this->getShortestRoute();

        $walkingTime = $this->getTimeToWalkDistance($distance);
        $audioTime = $this->getAudioTime($route);
        $decisionTime = floatval(config('junket.points.decision_time', 5));

        $total = $walkingTime + $audioTime + $decisionTime;

        return floatval(ceil($total));
    }
}
