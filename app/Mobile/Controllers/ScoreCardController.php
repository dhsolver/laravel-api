<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Mobile\Resources\ScoreCardResource;
use App\ScoreCard;

class ScoreCardController extends Controller
{
    /**
     * Get the user's score for all of their completed Tours.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
        $scores = ScoreCard::getBest(auth()->id(), $tour);

        if (empty($scores)) {
            throw new ModelNotFoundException('User has no score for this Tour.');
        }

        return new ScoreCardResource($scores->first());
    }
}
