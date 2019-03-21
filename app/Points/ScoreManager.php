<?php

namespace App\Points;

use App\User;
use App\Tour;
use Carbon\Carbon;

class ScoreManager
{
    /**
     * The user's current score card for the Tour.
     *
     * @var \App\ScoreCard
     */
    public $scoreCard;

    /**
     * Get the user object.
     *
     * @var \App\User
     */
    public $user;

    /**
     * Get the tour object.
     *
     * @var \App\Tour
     */
    public $tour;

    /**
     * Create a new instance.
     *
     * @param \App\Tour tour
     * @param \App\User user
     */
    public function __construct($scoreCard)
    {
        $this->user = $scoreCard->user;
        $this->tour = $scoreCard->tour;
        $this->scoreCard = $scoreCard;
    }

    /**
     * Accessor for the tour's points calculator object.
     *
     * @return IPointsCalculator
     */
    public function calculator()
    {
        return $this->scoreCard->tour->calculator();
    }

    /**
     * Create user score card using the current user and
     * tour on the instance.
     *
     * @param \App\User|\Illuminate\Contracts\Auth\Authenticatable $user
     * @param \App\Tour $tour
     * @param \Carbon\Carbon $startTime
     * @return mixed
     */
    public static function createScoreCard(User $user, Tour $tour, Carbon $startTime = null)
    {
        $startTime = $startTime ?: Carbon::now();

        if ($scoreCard = $user->scoreCards()->create([
            'tour_id' => $tour->id,
            'is_adventure' => $tour->isAdventure(),
            'par' => $tour->calculator()->getPar(),
            'total_stops' => $tour->calculator()->getTotalStops(),
            'stops_visited' => 0,
            'started_at' => $startTime,
            'won_trophy_at' => null
        ])) {
            return $scoreCard;
        }

        return false;
    }

    /**
     * Record progress after visiting the given stop.
     *
     * @param mixed $stop
     * @param Carbon $timestamp
     * @return bool
     */
    public function recordStopVisit($stop, Carbon $timestamp = null, $skippedQuestion = false)
    {
        $timestamp = $timestamp ?: Carbon::now();

        if (! $this->scoreCard->stops()->where('stop_id', modelId($stop))->exists()) {
            $this->scoreCard->stops()->attach($stop->id, [
                'visited_at' => $timestamp,
                'skipped_question' => $skippedQuestion
            ]);
        }

        $this->scoreCard->stops_visited = $this->scoreCard->stops()->count();
        $this->scoreCard->points = $this->calculator()->getPoints($this->scoreCard);
        if ($this->calculator()->scoreQualifiesForTrophy($this->scoreCard)) {
            $this->scoreCard->won_trophy_at = Carbon::now();
        }

        if ($this->isFinalStop($stop)) {
            // complete the tour
            $this->scoreCard->finished_at = $timestamp ?: Carbon::now();
        }

        if (! $this->scoreCard->save()) {
            return false;
        }

        if (! $this->persistUserStats()) {
            return false;
        }

        return true;
    }

    /**
     * Calculate and save users scoring stats.
     *
     * @return bool
     */
    public function persistUserStats()
    {
        $points = $this->user->scoreCards()
            ->onlyBest()
            ->sum('points');

        $completedTours = $this->user->scoreCards()
            ->finished()
            ->get()
            ->unique('tour_id')
            ->count();

        $trophies = $this->user->scoreCards()
            ->whereNotNull('won_trophy_at')
            ->get()
            ->unique('tour_id')
            ->count();

        $totalStops = $this->getUniqueStopsVisited();

        $this->user->stats()->update([
            'points' => $points,
            'tours_completed' => $completedTours,
            'stops_visited' => $totalStops,
            'trophies' => $trophies
        ]);

        return true;
    }

    /**
     * Get the number of stops visited in total by the user.
     *
     * @return int
     */
    private function getUniqueStopsVisited()
    {
        $stops = [];

        foreach ($this->user->scoreCards()->with(['stops'])->get() as $score) {
            foreach ($score->stops as $stop) {
                if (in_array($stop->id, $stops)) {
                    continue;
                }
                array_push($stops, $stop->id);
            }
        }

        return count($stops);
    }

    /**
     * Detect if the stop is the last stop on the tour.
     *
     * @param mixed $stop
     * @return bool
     */
    private function isFinalStop($stop)
    {
        if ($this->tour->isAdventure()) {
            if ($this->tour->end_point_id == modelId($stop)) {
                return true;
            }
        } else {
            // regular tours
            if ($this->scoreCard->stops_visited == $this->scoreCard->total_stops) {
                return true;
            }
        }

        return false;
    }
}
