<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Mobile\Resources\ScoreCardResource;
use App\ScoreCard;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ScoreCardController extends Controller
{
    /**
     * Get the user's score for all of their completed Tours.
     *
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
}
