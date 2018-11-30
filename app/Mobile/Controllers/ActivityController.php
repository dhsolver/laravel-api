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
     * @param RecordActivityRequest $request
     * @return \Illuminate\Http\Response
     */
    public function tour(Tour $tour, RecordActivityRequest $request)
    {
        $data = [];

        foreach ($request->activity as $item) {
            $ts = Carbon::createFromTimestampUTC($item['timestamp']);

            if ($ts > Carbon::now()) {
                $ts = Carbon::now();
            }

            $tour->activity()->create([
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id'],
                'created_at' => $ts
            ]);
        }

        return response()->json(['result' => 1, 'data' => $data]);
    }

    /**
     * Track Stop related activity.
     *
     * @param \App\TourStop $stop
     * @param RecordActivityRequest $request
     * @return \Illuminate\Http\Response
     */
    public function stop(TourStop $stop, RecordActivityRequest $request)
    {
        $data = [];

        foreach ($request->activity as $item) {
            $ts = Carbon::createFromTimestampUTC($item['timestamp']);

            if ($ts > Carbon::now()) {
                $ts = Carbon::now();
            }

            $stop->activity()->create([
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id'],
                'created_at' => $ts,
            ]);
        }

        return response()->json(['result' => 1, 'data' => $data]);
    }
}
