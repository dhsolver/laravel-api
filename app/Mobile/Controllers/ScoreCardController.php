<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Mobile\Resources\ScoreCardResource;

class ScoreCardController extends Controller
{
    /**
     * Get the user's score for all of their completed Tours.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $scores = auth()->user()->scoreCards()
            ->finished()
            ->get();

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
        $score = auth()->user()->scoreCards()
            ->finished()
            ->forTour($tour)
            ->first();

        if (empty($score)) {
            throw new ModelNotFoundException('User has no score for this Tour.');
        }

        return new ScoreCardResource($score);
    }
}
