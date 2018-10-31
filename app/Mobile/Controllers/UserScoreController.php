<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Tour;
use App\Mobile\Resources\UserScoreResource;

class UserScoreController extends Controller
{
    /**
     * Get the user's score for all of their completed Tours.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $scores = auth()->user()->scores()
            ->finished()
            ->get();

        return UserScoreResource::collection($scores);
    }

    /**
     * Get the user's score for all of their completed Tours.
     *
     * @param int $tour
     * @return \Illuminate\Http\Response
     */
    public function show($tour)
    {
        $score = auth()->user()->scores()
            ->finished()
            ->forTour($tour)
            ->first();

        if (empty($score)) {
            throw new ModelNotFoundException('User has no score for this Tour.');
        }

        return new UserScoreResource($score);
    }
}
