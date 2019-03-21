<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Mobile\Resources\ScoreCardResource;
use App\Mobile\Resources\TourScoreCardCollection;
use App\ScoreCard;
use App\Tour;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\StartTourRequest;
use App\Http\Requests\TourProgressRequest;
use App\Points\ScoreManager;
use App\TourStop;

class ScoreCardController extends Controller
{
    /**
     * Get the user's score for all of their completed Tours.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(User $user)
    {
        $scores = $user->scoreCards()
            ->with('tour')
            ->onlyBest();
        
        $scoreCards = ScoreCardResource::collection($scores);
        $returnScores = array();
        for ($i = 0; $i < count($scoreCards); $i ++) {
            if ($i == 0) {
                $returnScores[] = $scoreCards[$i];
            } else if ($scoreCards[$i-1]->tour_id != $scoreCards[$i]->tour_id) {
                $returnScores[] = $scoreCards[$i];
            }
        }
        return $returnScores;
    }

    /**
     * Find all score cards for a given Tour.
     *
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     * @throws ModelNotFoundException
     */
    public function find(Tour $tour)
    {
        $scores = auth()->user()->scoreCards()
            ->forTour($tour)
            ->get();

        if ($scores->count() == 0) {
            throw new ModelNotFoundException('User has no score for this Tour.');
        }

        return new TourScoreCardCollection($scores);
    }

    /**
     * Get the user's score for all of their completed Tours.
     *
     * @param StartTourRequest $request
     * @return \Illuminate\Http\Resources\Json\Resource|\Illuminate\Http\Response
     */
    public function start(StartTourRequest $request)
    {
        $tour = Tour::findOrFail($request->tour_id);
        $ts = Carbon::createFromTimestampUTC($request->timestamp);

        if ($ts > Carbon::now()) {
            $ts = Carbon::now();
        }

        if ($scoreCard = ScoreManager::createScoreCard(auth()->user(), $tour, $ts)) {
            return response()->json(new ScoreCardResource($scoreCard));
        }

        return $this->fail(500, 'Error creating user score card, please try again.');
    }

    /**
     * Update score card progress by saving a visited stop.
     *
     * @param TourProgressRequest $request
     * @param ScoreCard $scoreCard
     * @return \Illuminate\Http\Resources\Json\Resource|\Illuminate\Http\Response
     */
    public function progress(TourProgressRequest $request, ScoreCard $scoreCard)
    {
        if ($scoreCard->user_id != auth()->id()) {
            return $this->fail(401, 'You do not have access to this score card.');
        }

        if (! empty($scoreCard->finished_at)) {
            return $this->fail(403, 'User already finished the Tour.');
        }

        $stop = TourStop::findOrFail($request->stop_id);
        if ($scoreCard->tour_id != $stop->tour_id) {
            return $this->fail(422, 'Invalid tour stop ID.');
        }

        $ts = Carbon::createFromTimestampUTC($request->timestamp);
        if ($ts > Carbon::now()) {
            $ts = Carbon::now();
        }

        $skipped = $request->skipped_question == true ? true : false;

        if ($scoreCard->manager()->recordStopVisit($stop, $ts, $skipped)) {
            return response()->json(new ScoreCardResource($scoreCard->fresh()));
        }

        return $this->fail(500, 'Error updating user score card, please try again.');
    }
}
