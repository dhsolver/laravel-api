<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Points\TourTracker;
use App\Tour;
use App\Http\Requests\RecordActivityRequest;
use App\TourStop;
use Carbon\Carbon;
use App\Action;
use App\Mobile\Resources\UserScoreResource;

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

            $tour->activity()->create([
                'user_id' => auth()->user()->id,
                'action' => $item['action'],
                'device_id' => $item['device_id'],
                'created_at' => $ts
            ]);

            switch ($item['action']) {
                case Action::START:
                    $tracker = new TourTracker($tour, auth()->user());
                    $tracker->startTour($ts);
                    break;

                case Action::STOP:
                    $tracker = new TourTracker($tour, auth()->user());
                    $score = $tracker->finishTour($ts);
                    $data = new UserScoreResource($score);
                    break;
            }
        }

        return response()->json(['result' => 1, 'data' => $data]);
    }

    /**
     * Track Stop related activity.
     *
     * @return \Illuminate\Http\Response
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
