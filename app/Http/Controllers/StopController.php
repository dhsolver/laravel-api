<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cms\CreateStopRequest;
use App\Tour;
use App\Http\Resources\StopResource;
use App\TourStop;
use App\Http\Requests\Cms\UpdateStopRequest;
use App\Http\Resources\StopCollection;

class StopController extends Controller
{
    public function index(Tour $tour)
    {
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

        return new StopCollection(
            $tour->stops
        );
    }

    public function store(CreateStopRequest $request, Tour $tour)
    {
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

        $order = $tour->getNextStopOrder();

        return new StopResource(
            $tour->stops()->create(array_merge($request->validated(), ['order' => $order]))
        );
    }

    public function show(Tour $tour, TourStop $stop)
    {
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

        return new StopResource($stop);
    }

    public function update(UpdateStopRequest $request, Tour $tour, TourStop $stop)
    {
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

        return new StopResource(
            $tour->stops()->create($request->validated())
        );
    }

    public function destroy(Tour $tour, TourStop $stop)
    {
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

        $stop->delete();

        return response(null, 204);
    }

    public function changeOrder(Tour $tour, TourStop $stop)
    {
        request()->validate([
            'order' => 'required|numeric'
        ]);

        $stop->order = abs(request()->order);

        // get all objects with that order or higher
        // increase them all by one
        TourStop::where('tour_id', $tour->id)
            ->where('order', '>=', $stop->order)
            ->increment('order');

        $stop->save();

        return new StopCollection($tour->stops()->ordered()->get());
    }
}
