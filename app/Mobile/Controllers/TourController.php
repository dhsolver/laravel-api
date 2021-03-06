<?php

namespace App\Mobile\Controllers;

use App\Tour;
use App\Http\Controllers\Controller;
use App\Mobile\Resources\TourResource;
use App\Mobile\Resources\StopResource;
use App\Mobile\Resources\TourRouteResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Mobile\Resources\ReviewResource;
use Illuminate\Http\Request;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illumate\Http\Request
     * @return \Illuminate\Http\Resources\Json\ResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lat = 0;
        $lon = 0;
        if ($request->has('nearby')) {
            $coordinates = $request->nearby;

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
            Tour::published($request->debug == 1)
                ->distanceFrom($lat, $lon)
                ->favoritedBy($request->favorites == 1 ? auth()->id() : null)
                ->search($request->search)
                ->paginate()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Response
     * @throws ModelNotFoundException
     */
    public function show(Tour $tour)
    {
        if (! $tour->isLive(request()->debug == 1)) {
            if (! empty($tour->last_published_at)) {
                // tour was published at one time, but it no longer available
                throw new ModelNotFoundException('Tour no longer available.');
            }
            throw new ModelNotFoundException('Tour not available.');
        }

        $reviews = $tour->reviews()->whereNotNull('review')->with('user')->latest()->limit(5)->get();

        return response()->json([
            'tour' => new TourResource($tour, 'detail'),
            'stops' => StopResource::collection($tour->stops),
            'route' => TourRouteResource::collection($tour->route),
            'latest_reviews' => ReviewResource::collection($reviews),
        ]);
    }

    /**
     * Gets all tours without paging.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function all()
    {
        return TourResource::collection(
            Tour::published(request()->debug)->paginate(999999)
        );
    }
}
