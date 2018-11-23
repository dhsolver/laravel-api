<?php

namespace App\Reports;

use App\Action;

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
     * Filter the reuslts to between two dates.
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
        foreach ($this->tour->stops as $stop) {
            $actions = $stop->activity()->whereIn('action', [Action::LIKE, Action::SHARE])
                ->betweenDates($this->start_date, $this->end_date)
                ->count();

            $visits = $stop->activity()->where('action', Action::VISIT)
                ->betweenDates($this->start_date, $this->end_date)
                ->count();

            $starts = $stop->activity()->where('action', Action::START)
                ->betweenDates($this->start_date, $this->end_date)
                ->get();

            $finishes = $stop->activity()->where('action', Action::STOP)
                ->betweenDates($this->start_date, $this->end_date)
                ->get();

            $timeSpent = 0;
            foreach ($starts as $s) {
                $f = $finishes->where('user_id', $s->user_id)
                    ->where('created_at', '>', $s->created_at)
                    ->sortBy('created_at')
                    ->first();

                // skip starts that have no finish
                if (empty($f)) {
                    continue;
                }

                $timeSpent += $s->created_at->diffInMinutes($f->created_at);
            }

            $results[$stop->id] = [
                'title' => $stop->title,
                'time' => $timeSpent,
                'visits' => $visits,
                'actions' => $actions,
            ];
        }

        return ['stops' => $results];
    }
}
