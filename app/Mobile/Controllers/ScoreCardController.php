<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Mobile\Resources\ScoreCardResource;
use App\ScoreCard;
use App\Tour;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests\StartTourRequest;
use App\Http\Requests\TourProgressRequest;
use App\Points\ScoreManager;
use App\TourStop;

class ScoreCardController extends Controller
{
    /**
     * Get the user's score for all of their completed Tours.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        // TODO: document this
        $scores = ScoreCard::getBest(auth()->id());

        return ScoreCardResource::collection($scores);
    }

    /**
     * Get the user's score for all of their completed Tours.
     *
     * @param int $tour
     * @return ScoreCardResource
     */
    public function show($tour)
    {
        // TODO: document this
        $scores = ScoreCard::getBest(auth()->id(), $tour);

        if ($scores->count() == 0) {
            throw new ModelNotFoundException('User has no score for this Tour.');
        }

        return new ScoreCardResource($scores->first());
    }

    /**
     * Get the user's score for all of their completed Tours.
     *
     * @param StartTourRequest $request
     * @return \Illuminate\Http\Response|ScoreCardResource
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
     * @return \Illuminate\Http\Response|ScoreCardResource
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

        if ($scoreCard->manager()->recordStopVisit($stop, $ts)) {
            return response()->json(new ScoreCardResource($scoreCard->fresh()));
        }

        return $this->fail(500, 'Error updating user score card, please try again.');
    }
}
