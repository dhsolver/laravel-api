<?php

namespace App\Reports;

use App\StopStat;

class StopOverviewReport extends BaseReport
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
        $results = [];

        $stops = $this->tour->stops->pluck('id');

        $stats = StopStat::whereIn('stop_id', $stops)
            ->betweenDates($this->start_date, $this->end_date)
            ->groupBy('stop_id')
            ->selectRaw('sum(visits) as visits, sum(time_spent) as time_spent, sum(actions) as actions, stop_id')
            ->get();

        foreach ($this->tour->stops as $stop) {
            $stat = $stats->where('stop_id', $stop->id)->first();

            $results[$stop->id] = [
                'title' => $stop->title,
                'time' => (int) $stat['time_spent'],
                'visits' => (int) $stat['visits'],
                'actions' => (int) $stat['actions'],
            ];
        }

        return ['stops' => $results];
    }
}
