<?php

namespace App\Mobile\Controllers;

use App\Tour;
use App\User;
use App\Http\Controllers\Controller;
use App\Mobile\Resources\TourResource;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user)
    {
        return response()->json(['favorites' => TourResource::collection(
            $user->favorites
        )]);
    }

    /**
     * Attach a Tour to user favorites.
     *
     * @param  \App\Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function store(Tour $tour)
    {
        if (! auth()->user()->favorites()->where('tour_id', $tour->id)->exists()) {
            auth()->user()->favorites()->attach($tour);
        }

        return $this->success('Tour has been added to favorites.');
    }

    /**
     * Detach a Tour from user favorites.
     *
     * @param  \App\Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tour $tour)
    {
        if (auth()->user()->favorites()->where('tour_id', $tour->id)->exists()) {
            auth()->user()->favorites()->detach($tour);
        }

        return $this->success('Tour has been removed from favorites.');
    }
}
