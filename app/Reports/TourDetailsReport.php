<?php

namespace App\Reports;

use App\Activity;

class TourDetailsReport extends BaseReport
{
    /**
     * The begin date.
     *
     * @var string
     */
    protected $start_date;

    /**
     * The end date.
     *
     * @var string
     */
    protected $end_date;

    /**
     * Filter the results to between two dates.
     *
     * @param string $start
     * @param string $end
     * @return $this
     */
    public function forDates($start, $end)
    {
        $this->start_date = $start;
        $this->end_date = $end;

        return $this;
    }

    /**
     * Run the report and return the results.
     *
     * @return array
     */
    public function run()
    {
        // if no date range set, get the first date of stats
        if (empty($this->start_date)) {
            $firstRecord = $this->tour->stats()->orderBy('yyyymmdd')->first();
            if (empty($firstRecord)) {
                return [];
            }
            $date = date('m/d/Y', strtotime($firstRecord->yyyymmdd));
            $this->forDates($date, date('m/d/Y', strtotime('now')));
        }

        $results = $this->tour->stats()
            ->betweenDates($this->start_date, $this->end_date)
            ->orderBy('yyyymmdd')
            ->get();

        return ['data' => $results->map(function ($item) {
            $downloads = Activity::select('device_id')
                ->distinct()
                ->where('action', 'start')
                ->where('actionable_id', $item->tour_id)
                ->where('actionable_type', 'App\Tour')
                ->where('created_at', 'like', date('Y-m-d', strtotime($item->yyyymmdd)) . '%')
                ->get();
            return [
                'yyyymmdd' => $item->yyyymmdd,
                'actions' => (int) $item->actions,
                'downloads' => count($downloads),
                'time' => (int) $item->time_spent,
                'tour_id' => (int) $item->tour_id
            ];
        })];
    }
}
