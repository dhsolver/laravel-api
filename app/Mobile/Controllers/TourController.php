<?php

namespace App\Mobile\Controllers;

use App\Tour;
use App\Http\Controllers\Controller;
use App\Mobile\Resources\TourResource;
use App\Mobile\Resources\StopResource;
use App\Mobile\Resources\TourRouteResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Mobile\Resources\TourCollection
     */
    public function index()
    {
        return TourResource::collection(
            Tour::published()
                ->search(request()->search)
                ->paginate()
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
        if (!$tour->isPublished) {
            throw new ModelNotFoundException('Tour not available');
        }

        return response()->json([
            'tour' => new TourResource($tour),
            'stops' => StopResource::collection($tour->stops),
            'route' => TourRouteResource::collection($tour->route),
        ]);
    }

    /**
     * Gets all tours without paging
     *
     * @return \App\Mobile\Resources\TourResource
     */
    public function all()
    {
        return TourResource::collection(
            Tour::published()->get()
        );
    }
}
