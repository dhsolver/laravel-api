<?php

namespace App\Mobile\Controllers;

use App\Tour;
use App\Mobile\Resources\TourResource;
use App\Mobile\Resources\TourCollection;
use App\Http\Controllers\Controller;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Mobile\Resources\TourCollection
     */
    public function index()
    {
        return new TourCollection(
            Tour::all()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Tour  $tour
     * @return \App\Mobile\Resources\TourResource
     */
    public function show(Tour $tour)
    {
        return new TourResource($tour);
    }
}
