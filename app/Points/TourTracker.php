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
    }

    /**
     * Create starting record for the given Tour and the
     * authenticated user.
     *
     * @param \Carbon\Carbon $startTime
     * @return UserScore
     */
    public function startTour($startTime = null)
    {
        $startTime = $startTime ?: Carbon::now();

        try {
            $ac = new AdventureCalculator($this->tour);
            $par = $ac->getPar();
        } catch (UntraceableTourException $ex) {
            return false;
        }

        return $this->user->scores()->create([
            'tour_id' => $this->tour->id,
            'par' => $par,
            'started_at' => $startTime,
        ]);
    }

    /**
     * Save the users progress and calculate the score.
     *
     * @param \Carbon\Carbon $endTime
     * @return UserScore
     */
    public function finishTour($endTime = null)
    {
        $endTime = $endTime ?: Carbon::now();

        $this->user->scores()
            ->forTour($this->tour)
            ->first()
            ->update([
                'finished_at' => $endTime,
            ]);

        return $this->user->scores()->forTour($this->tour)->first();
    }
}