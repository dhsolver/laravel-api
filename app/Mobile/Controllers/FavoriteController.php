<?php

namespace App\Mobile\Controllers;

use App\Tour;
use App\Http\Controllers\Controller;
use App\Mobile\Resources\TourResource;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(['favorites' => TourResource::collection(
            auth()->user()->favorites
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
        auth()->user()->favorites()->attach($tour);

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
        auth()->user()->favorites()->detach($tour);

        return $this->success('Tour has been removed from favorites.');
    }
}
