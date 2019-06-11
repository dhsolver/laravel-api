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
        foreach ($request->activity as $item) {
            $beginTs = Carbon::createFromTimestampUTC($item['begin_timestamp']);
            $endTs = Carbon::createFromTimestampUTC($item['end_timestamp']);

            $tour->activity()->create([
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id'],
                'begin_at' => $beginTs,
                'end_at' => $endTs
            ]);

            $data[] = [
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id']
            ];
        }

        return response()->json(['message' => 'OK']);
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
            $beginTs = Carbon::createFromTimestampUTC($item['begin_timestamp']);
            $endTs = Carbon::createFromTimestampUTC($item['end_timestamp']);

            $stop->activity()->create([
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id'],
                'begin_at' => $beginTs,
                'end_at' => $endTs
            ]);

            $data[] = [
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id'],
            ];
        }

        return response()->json(['message' => 'OK']);
    }
}
