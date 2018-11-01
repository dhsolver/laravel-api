<?php

namespace App\Points;

use App\Exceptions\UntraceableTourException;
use App\User;
use App\UserScore;
use Illuminate\Support\Carbon;

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
     * @var \App\UserScore
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

        $this->scoreCard = UserScore::current($tour, $user);
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
            $data = [
                'tour_id' => $this->tour->id,
                'is_adventure' => $this->tour->isAdventure(),
                'par' => $this->tour->calculator()->getPar(),
                'total_stops' => $this->tour->calculator()->getTotalStops(),
                'stops_visited' => 0,
                'started_at' => $startTime,
            ];

            if ($this->scoreCard = $this->user->scores()->create($data)) {
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
        if ($this->scoreCard->save()) {
            return true;
        }

        return false;

//        // auto-calculate a new score when the Tour is finished.
//        if ($model->isDirty('finished_at')) {
////                $tracker = new TourTracker($model->tour, $model->user);
////                $model->points = $tracker->calculatePoints($model);
////                $model->won_trophy = $tracker->scoreQualifiesForTrophy($model);
//
//            $ac = new AdventureCalculator($model->tour);
//            $model->points = $ac->calculatePoints($model->duration, $model->par, $model->user_id);
//            $model->won_trophy = $ac->scoreQualifiesForTrophy($model->points, $model->par);
//        }
//
    }

    public function completeStop($endTime)
    {
        return false;

    }
}