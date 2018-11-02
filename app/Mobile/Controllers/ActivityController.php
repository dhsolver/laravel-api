<?php

namespace App\Mobile\Controllers;

use App\Exceptions\MissingScoreCardException;
use App\Http\Controllers\Controller;
use App\Points\TourTracker;
use App\Tour;
use App\Http\Requests\RecordActivityRequest;
use App\TourStop;
use Carbon\Carbon;
use App\Action;
use App\Mobile\Resources\ScoreCardResource;

class ActivityController extends Controller
{
    /**
     * Track Tour related activity.
     *
     * @param Tour $tour
     * @param RecordActivityRequest $request
     * @return \Illuminate\Http\Response
     * @throws MissingScoreCardException
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

            switch ($item['action']) {
                case Action::START:
                    $tracker = new TourTracker($tour, auth()->user());
                    $tracker->startTour($ts);
                    if (! $tracker->ensureScoreCard()) {
                        // this should never happen
                        throw new MissingScoreCardException('Unable to locate user scorecard.');
                    }
                    $data = new ScoreCardResource($tracker->scoreCard);
                    break;

                case Action::STOP:
                    $tracker = new TourTracker($tour, auth()->user());
                    if (! $tracker->completeTour($ts)) {
                        // TODO: log this
                        throw new \Exception('Error saving user score!');
                    }
                    $data = new ScoreCardResource($tracker->scoreCard);
                    break;
            }
        }

        return response()->json(['result' => 1, 'data' => $data]);
    }

    /**
     * Track Stop related activity.
     *
     * @return \Illuminate\Http\Response
     * @throws MissingScoreCardException
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

            switch ($item['action']) {
                case Action::STOP:
                    $tracker = new TourTracker($stop->tour, auth()->user());
                    $tracker->completeStop();
                    $data = new ScoreCardResource($tracker->scoreCard);
                    break;
            }
        }

        return response()->json(['result' => 1, 'data' => $data]);
    }
}
