<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Tour;
use App\Http\Requests\RecordActivityRequest;
use App\TourStop;
use Carbon\Carbon;
use App\Action;

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
        $data = [];

        foreach ($request->activity as $item) {
            $ts = Carbon::createFromTimestampUTC($item['timestamp']);

            $tour->activity()->create([
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id'],
                'created_at' => $ts
            ]);

            switch ($item['action']) {
                case Action::START:
                    auth()->user()->startTour($tour, $ts);
                    break;
                case Action::STOP:
                    $data = auth()->user()->finishTour($tour, $ts);
                    break;
            }
        }

        return response()->json(['result' => 1, 'data' => $data]);
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
