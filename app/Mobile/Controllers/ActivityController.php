<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Tour;
use App\Http\Requests\RecordActivityRequest;
use App\TourStop;
use Carbon\Carbon;

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
        $fin = false;

        foreach ($request->activity as $item) {
            $tour->activity()->create([
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id'],
                'created_at' => Carbon::createFromTimestampUTC($item['timestamp'])
            ]);

            if ($item['action'] == 'stop') {
                $fin = true;
            }
        }

        if ($fin && $tour->type == 'adventure') {
            // TODO: return the users score for adventure stops
        }

        return response()->json(['result' => 1]);
    }

    /**
     * Track Stop related activity.
     *
     * @return void
     */
    public function stop(TourStop $stop, RecordActivityRequest $request)
    {
        foreach ($request->activity as $item) {
            $stop->activity()->create([
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id'],
                'created_at' => Carbon::createFromTimestampUTC($item['timestamp'])
            ]);
        }

        return response()->json(['result' => 1]);
    }
}
