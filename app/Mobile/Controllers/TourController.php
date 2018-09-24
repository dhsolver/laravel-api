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
        $lat = 0;
        $lon = 0;
        if (request()->has('nearby')) {
            $coordinates = request()->nearby;

            if (! strpos($coordinates, ',')) {
                return $this->fail(422, 'Invalid nearby coordinates.');
            }

            $lat = floatval(substr($coordinates, 0, strpos($coordinates, ',')));
            $lon = floatval(substr($coordinates, strpos($coordinates, ',') + 1));

            if ($lat == 0 || $lon == 0) {
                return $this->fail(422, 'Invalid distance_from coordinates.');
            }
        }

        return TourResource::collection(
            Tour::published()
                ->distanceFrom($lat, $lon)
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
        if (! $tour->isPublished) {
            if (! empty($tour->last_published_at)) {
                // tour was published at one time, but it no longer available
                throw new ModelNotFoundException('Tour no longer available.');
            }
            throw new ModelNotFoundException('Tour not available.');
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
