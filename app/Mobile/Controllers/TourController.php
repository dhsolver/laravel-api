<?php

namespace App\Mobile\Controllers;

use App\Tour;
use App\Http\Controllers\Controller;
use App\Mobile\Resources\TourResource;
use App\Mobile\Resources\StopResource;
use App\Mobile\Resources\TourRouteResource;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Mobile\Resources\TourCollection
     */
    public function index()
    {
        return TourResource::collection(Tour::paginate());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Tour  $tour
     * @return \App\Mobile\Resources\TourResource
     */
    public function show(Tour $tour)
    {
        return response()->json([
            'tour' => new TourResource($tour),
            'stops' => StopResource::collection($tour->stops),
            'route' => TourRouteResource::collection($tour->route),
        ]);
    }
}
