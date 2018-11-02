<?php

namespace App\Points;

use App\StopRoute;
use App\Exceptions\UntraceableTourException;
use App\ScoreCard;
use drupol\phpermutations\Generators\Permutations;

class AdventureCalculator implements PointsCalculator
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
     * Calculate the points that should be awarded to the user
     * based on their time.
     *
     * @param ScoreCard $scoreCard
     * @return int
     * @throws UntraceableTourException
     */
    public function getPoints(ScoreCard $scoreCard)
    {
        return $this->calculatePoints($scoreCard->duration, $scoreCard->par);
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
    public function calculatePoints($timeInMinutes, $par = null)
    {
        $time = floatval($timeInMinutes);
        $min = config('junket.points.min_points', 50);
        $max = config('junket.points.max_points', 200);

        if ($par == null) {
            $par = $this->getPar();
        }
        $total = $max;

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

    /**
     * Get the shortest route path and distance.
     *
     * @return array
     * @throws UntraceableTourException
     */
    public function getShortestRoute()
    {
        $best = 0;
        $shortestRoute = null;

        foreach ($this->getPossiblePaths() as $path) {
            $distance = 0;
            $previous = null;
            foreach ($path as $id) {
                $stop = $this->getStop($id);
                if (! empty($previous)) {
                    $distance += $this->getDistanceBetweenStops($previous, $stop);
                }
                $previous = $stop;
            }

            if ($best == 0 || $distance < $best) {
                $shortestRoute = $path;
                $best = $distance;
            }
        }

        return [$shortestRoute, $best];
    }

    /**
     * Get an array of all the possible paths for the tour.
     *
     * @return \Illuminate\Support\Collection
     * @throws UntraceableTourException
     */
    public function getPossiblePaths()
    {
        $firstStop = $this->getFirstStop();
        $lastStop = $this->getLastStop();

        $nextStopList = [];
        foreach ($this->stops as $stop) {
            $nextStops = $this->getNextStops($stop);

            if (empty($nextStops)) {
                // last stop
                $nextStopList[$stop->id] = [];
                break;
            }

            $nextStopList[$stop->id] = $nextStops->pluck('id')->toArray();
        }

        $validPermutations = [];
        $permutations = $this->getPermutations($this->allStopIds());
        foreach ($permutations as $permutation) {
            $valid = false;
            $previous = null;

            $p = $permutation;

            // if permutation doesn't start with the first stop, it is invalid
            if ($p[0] != $firstStop->id) {
                continue;
            }

            foreach ($p as $id) {
                if (empty($previous)) {
                    $previous = $id;
                    continue;
                }

                if (in_array($id, $nextStopList[$previous])) {
                    $valid = true;
                    $previous = $id;
                    continue;
                }

                $valid = false;
                break; // not valid
            }

            if ($valid && $p[count($p) - 1] == $lastStop->id) {
                array_push($validPermutations, $p);
            }
        }

        return collect($validPermutations)->values();
    }

    /**
     * Get possible next stops for a given stop.  Returns collection of TourStop
     * objects, unless the stop is the end point for the Tour, in which
     * case it will return null.
     *
     * @param \App\TourStop $stop
     * @return \Illuminate\Support\Collection
     * @throws UntraceableTourException
     */
    public function getNextStops($stop)
    {
        if ($this->tour->end_point_id == $stop->id) {
            return null;
        }

        if ($stop->is_multiple_choice) {
            // get all stops from choices
            $next = [];
            foreach ($stop->choices as $choice) {
                $obj = $this->getStop($choice->next_stop_id);

                if (empty($obj)) {
                    throw new UntraceableTourException($this->tour, $stop, UntraceableTourException::MISSING_NEXT_STOP);
                }

                array_push($next, $obj);
            }

            return collect($next);
        } else {
            // next one from the next_stop_id
            $next = $this->getStop($stop->next_stop_id);

            if (empty($next)) {
                throw new UntraceableTourException($this->tour, $stop, UntraceableTourException::NO_NEXT_STOP);
            }

            return collect([$next]);
        }
    }

    /**
     * Get the last stop of the Tour.
     *
     * @return \App\TourStop
     * @throws UntraceableTourException
     */
    public function getLastStop()
    {
        if (! $stop = $this->getStop($this->tour->end_point_id)) {
            throw new UntraceableTourException($this->tour, null, UntraceableTourException::NO_END_POINT);
        }

        return $stop;
    }

    /**
     * Get the first stop of the Tour.
     *
     * @return \App\TourStop
     * @throws UntraceableTourException
     */
    public function getFirstStop()
    {
        if (! $stop = $this->getStop($this->tour->start_point_id)) {
            throw new UntraceableTourException($this->tour, null, UntraceableTourException::NO_START_POINT);
        }

        return $stop;
    }

    /**
     * Get the distance between two stops.
     *
     * @param \App\TourStop $stop1
     * @param \App\TourStop $stop2
     * @return float
     */
    public function getDistanceBetweenStops($stop1, $stop2)
    {
        $path = StopRoute::where('stop_id', $stop1->id)
            ->where('next_stop_id', $stop2->id)
            ->inOrder()
            ->get();

        if (empty($path)) {
            // if there is no path, just get the straight line distance
            // between the two stops coordinates.
            return $this->getDistance(
                $stop1->location->latitude,
                $stop1->location->longitude,
                $stop2->location->latitude,
                $stop2->location->longitude
            );
        }

        // get every point along the route and get the sum of the distance
        // between stop1, all of those points and stop2.
        $total = 0;
        $previousPoint = $stop1->location;
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

        return $total;
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

    /**
     * Estimate the amount of time (in minutes) that it should take
     * to walk the given distance.
     *
     * @param float $distance
     * @return float
     */
    protected function getTimeToWalkDistance($distance)
    {
        $milesPerHour = config('junket.points.average_walking_speed', 4);
        return (floatval($distance) / floatval($milesPerHour)) * floatval(60);
    }

    /**
     * Get the stop with the given ID from the pre-loaded stops array.
     *
     * @param int $id
     * @return \App\TourStop
     */
    protected function getStop($id)
    {
        return $this->stops->where('id', $id)->first();
    }

    /**
     * Get an array of the stops ids.
     *
     * @return array
     */
    protected function allStopIds()
    {
        return collect($this->stops)->pluck('id')->values()->toArray();
    }

    /**
     * Get the total length of audio for the current Tour.
     *
     * @return float
     */
    protected function getAudioTime($stops)
    {
        $total = floatval(0.0);

        if (! empty($this->tour->backgroundAudio)) {
            $total += floatval($this->tour->backgroundAudio->length);
        }

        foreach ($stops as $id) {
            $stop = $this->getStop($id);
            if (! empty($stop->introAudio)) {
                $total += floatval($stop->introAudio->length);
            }
        }

        return ($total / floatval(60));
    }

    /**
     * Get all possible permutations of an array.
     *
     * @param array $items
     * @return array
     */
    protected function getPermutations(array $items)
    {
        $all = [];

        for ($i = 2; $i <= count($items); $i++) {
            $p = new Permutations($items, $i);
            $all = array_merge($all, $p->toArray());
        }

        return $all;
    }
}
