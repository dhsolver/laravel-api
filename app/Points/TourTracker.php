<?php

namespace App\Points;

use App\Action;
use App\Activity;
use App\Exceptions\MissingScoreCardException;
use App\Exceptions\UntraceableTourException;
use App\TourStop;
use App\User;
use App\ScoreCard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TourTracker
{
    /**
     * The Tour.
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

            return $this->createScoreCard($startTime);
        } catch (UntraceableTourException $ex) {
            return false;
        }
    }

    /**
     * Save the users progress and calculate the score.
     *
     * @param \Carbon\Carbon $endTime
     * @return bool
     * @throws MissingScoreCardException
     */
    public function completeTour($endTime = null)
    {
        if (! $this->ensureScoreCard()) {
            // this should never happen
            throw new MissingScoreCardException('Unable to locate user scorecard.');
        }

        $this->scoreCard->finished_at = $endTime ?: Carbon::now();
        $this->scoreCard->points = $this->tour->calculator()->getPoints($this->scoreCard);
        if ($this->tour->calculator()->scoreQualifiesForTrophy($this->scoreCard)) {
            $this->scoreCard->won_trophy_at = Carbon::now();
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
     * Increase user's progress by increasing the
     * number of stops they visited.
     *
     * @return bool
     * @throws MissingScoreCardException
     */
    public function completeStop()
    {
        if (! $this->ensureScoreCard()) {
            // this should never happen
            throw new MissingScoreCardException('Unable to locate user scorecard.');
        }

        $this->scoreCard->stops_visited = $this->getStopsVisited($this->scoreCard->started_at);
        $this->scoreCard->points = $this->tour->calculator()->getPoints($this->scoreCard);
        if ($this->tour->calculator()->scoreQualifiesForTrophy($this->scoreCard)) {
            $this->scoreCard->won_trophy_at = Carbon::now();
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
     * Create user score card using the current user and
     * tour on the instance.
     *
     * @param \Carbon\Carbon $startTime
     * @return bool
     */
    public function createScoreCard($startTime = null)
    {
        $startTime = $startTime ?: Carbon::now();

        if ($this->scoreCard = $this->user->scoreCards()->create([
            'tour_id' => $this->tour->id,
            'is_adventure' => $this->tour->isAdventure(),
            'par' => $this->tour->calculator()->getPar(),
            'total_stops' => $this->tour->calculator()->getTotalStops(),
            'stops_visited' => 0,
            'started_at' => $startTime,
            'won_trophy_at' => null,
        ])) {
            return true;
        }

        return false;
    }

    /**
     * Make sure the current score card is set, otherwise
     * create a new one.  This is called when a scorecard
     * should exist already, as protected to make sure there
     * is always one in the database.
     *
     * @return bool
     */
    public function ensureScoreCard()
    {
        if (empty($this->scoreCard)) {
            Log::warning('User completed stop without starting the Tour.', [
                'user' => $this->user->toArray(),
                'tour' => $this->tour->toArray(),
                'stops_visited' => $this->user->activity()
                    ->where('actionable_type', 'App\TourStop')
                    ->where('action', Action::STOP)
                    ->get()
                    ->toArray()
            ]);

            return $this->createScoreCard();
        } else {
            return true;
        }
    }

    /**
     * Calculate and save users scoring stats.
     *
     * @return bool
     */
    public function persistUserStats()
    {
        $points = ScoreCard::getBest($this->user)
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

        $this->user->stats()->update([
            'points' => $points,
            'tours_completed' => $completedTours,
            'stops_visited' => $this->getStopsVisited(),
            'trophies' => $trophies,
        ]);

        return true;
    }

    /**
     * Get the number of stops visited by the current
     * users for the current Tour.
     *
     * @param \Carbon\Carbon $since
     * @return int
     */
    private function getStopsVisited($since = null)
    {
        return (int) Activity::where('user_id', modelId($this->user))
            ->since($since)
            ->where('actionable_type', TourStop::class)
            ->where('action', Action::STOP)
            ->whereIn('actionable_id', $this->tour->stops->pluck('id'))
            ->get()
            ->unique('actionable_id')
            ->count();
    }
}
