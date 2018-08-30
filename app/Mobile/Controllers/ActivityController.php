<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Tour;
use App\Http\Requests\RecordActivityRequest;
use App\TourStop;

class ActivityController extends Controller
{
    /**
     * Track Tour related activity.
     *
     * @param Tour $tour
     * @param RecordTourActivityRequest $request
     * @return Response
     */
    public function tour(Tour $tour, RecordActivityRequest $request)
    {
        $tour->activity()->create(
            array_merge($request->validated(), ['user_id' => auth()->user()->id])
        );

        return response()->json(['result' => 1]);
    }

    /**
     * Track Stop related activity.
     *
     * @return void
     */
    public function stop(TourStop $stop, RecordActivityRequest $request)
    {
        $stop->activity()->create(
            array_merge($request->validated(), ['user_id' => auth()->user()->id])
        );

        return response()->json(['result' => 1]);
    }
}
