<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Mobile\Resources\LeaderboardCollection;
use App\ScoreCard;
use App\Tour;

class LeaderboardController extends Controller
{
    /**
     * Get the all time leaderboard.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index()
    {
        $scores = ScoreCard::with(['user'])
            ->selectRaw('user_id, SUM(points) as points')
            ->groupBy(['user_id'])
            ->orderBy('points', 'desc')
            ->where(function ($query) {
                return $query->where(function ($q) {
                    return $q->forAdventures()
                        ->finished();
                })
                ->orWhere(function ($q) {
                    return $q->forRegularTours();
                });
            })
            ->get();

        return new LeaderboardCollection($scores);
    }

    /**
     * Get the leaderboard for a given Tour.
     *
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function tour(Tour $tour)
    {
        $scores = ScoreCard::with(['user'])
            ->selectRaw('tour_id, user_id, MAX(points) as points')
            ->groupBy(['user_id', 'tour_id'])
            ->where('tour_id', modelId($tour))
            ->orderBy('points', 'desc')
            ->where(function ($query) {
                return $query->where(function ($q) {
                    return $q->forAdventures()
                          ->finished();
                })
                ->orWhere(function ($q) {
                    return $q->forRegularTours();
                });
            })
            ->limit(100)
            ->get();

        return new LeaderboardCollection($scores);
    }
}
