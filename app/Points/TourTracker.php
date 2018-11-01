<?php

namespace App\Points;

use App\Action;
use App\Activity;
use App\Exceptions\UntraceableTourException;
use App\TourStop;
use App\User;
use App\ScoreCard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TourTracker
{
    /**
     * The adventure Tour.
     *
     * @var \App\Tour
     */
    public $tour;

    /**
     * The user that is being tracked.
     *
     * @var \App\User
     */
    public $user;

    /**
     * The user's current score card for the Tour.
     *
     * @var \App\ScoreCard
     */
    public $scoreCard;

    /**
     * Create a new instance.
     *
     * @param \App\Tour tour
     * @param \App\User user
     */
    public function __construct($tour, $user)
    {
        $this->tour = $tour;

        if ($user instanceof User) {
            $this->user = $user;
        } else {
            $this->user = User::find($user);
        }

        $this->scoreCard = ScoreCard::for($tour, $user);
    }

    /**
     * Create starting record for the given Tour and the
     * authenticated user.
     *
     * @param \Carbon\Carbon $startTime
     * @return bool
     */
    public function startTour($startTime = null)
    {
        $startTime = $startTime ?: Carbon::now();

        try {
            // if regular tour, and score card exists, just use that
            if (! $this->tour->isAdventure()) {
                if ($this->scoreCard = ScoreCard::for($this->tour, $this->user)) {
                    $this->scoreCard->update([
                        'total_stops' => $this->tour->calculator()->getTotalStops(),
                    ]);
                    return true;
                }
            }

            if ($this->scoreCard = $this->user->scoreCards()->create([
                'tour_id' => $this->tour->id,
                'is_adventure' => $this->tour->isAdventure(),
                'par' => $this->tour->calculator()->getPar(),
                'total_stops' => $this->tour->calculator()->getTotalStops(),
                'stops_visited' => 0,
                'started_at' => $startTime,
            ])) {
                return true;
            }

            return false;
        } catch (UntraceableTourException $ex) {
            return false;
        }
    }

    /**
     * Save the users progress and calculate the score.
     *
     * @param \Carbon\Carbon $endTime
     * @return bool
     */
    public function completeTour($endTime = null)
    {
        if (empty($this->scoreCard)) {
            // TODO: throw and catch error - this shouldn't happen, can't finish tour never started
            return false;
        }

        $this->scoreCard->finished_at = $endTime ?: Carbon::now();
        $this->scoreCard->points = $this->tour->calculator()->getPoints($this->scoreCard);
        $this->scoreCard->won_trophy = $this->tour->calculator()->scoreQualifiesForTrophy($this->scoreCard);

        DB::beginTransaction();

        if (! $this->scoreCard->save()) {
            DB::rollBack();
            return false;
        }

        if (! $this->persistUserStats()) {
            DB::rollBack();
            return false;
        }

        DB::commit();

        return true;
    }

    public function completeStop()
    {
        if (empty($this->scoreCard)) {
            // TODO: throw and catch error - this shouldn't happen, can't finish tour never started
            return false;
        }

//        echo "\r\n stops visited: " . $this->getStopsVisited() . "\r\n";

        $this->scoreCard->stops_visited = $this->getStopsVisited();
        $this->scoreCard->points = $this->tour->calculator()->getPoints($this->scoreCard);
        $this->scoreCard->won_trophy = $this->tour->calculator()->scoreQualifiesForTrophy($this->scoreCard);

        DB::beginTransaction();

        if (! $this->scoreCard->save()) {
            DB::rollBack();
            return false;
        }

        if (! $this->persistUserStats()) {
            DB::rollBack();
            return false;
        }

        DB::commit();

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
            ->forRegularTours()
            ->sum('points');

        // TODO: calculate total points for adventure tours

        $completedTours = $this->user->scoreCards()
            ->finished()
            ->get()
            ->unique('tour_id')
            ->count();

        $stopsVisited = $this->user->activity()
            ->where('actionable_type', 'App\TourStop')
            ->where('action', Action::STOP)
            ->get()
            ->unique('actionable_id')
            ->count();

        $trophies = $this->user->scoreCards()->where('won_trophy', true)->get()->unique('tour_id')->count();

        // TODO: create tests for all of these stats
        if ($this->user->stats()->update([
            'points' => $points,
            'tours_completed' => $completedTours,
            'stops_visited' => $stopsVisited,
            'trophies' => $trophies,
        ])) {
            return true;
        }

        return false;
    }

    /**
     * Get the number of stops visited by the current
     * users for the current Tour.
     *
     * @return int
     */
    private function getStopsVisited()
    {
        return (int) Activity::where('user_id', modelId($this->user))
            ->where('actionable_type', TourStop::class)
            ->whereIn('actionable_id', $this->tour->stops->pluck('id'))
            ->get()
            ->unique('actionable_id')
            ->count();
    }
}
