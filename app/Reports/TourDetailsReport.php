<?php

namespace App\Reports;

use App\Action;

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
        $downloads = $this->tour->activity()->where('action', Action::DOWNLOAD)
            ->betweenDates($this->start_date, $this->end_date)
            ->count();

        $starts = $this->tour->activity()->where('action', Action::START)
            ->betweenDates($this->start_date, $this->end_date)
            ->get();

        $finishes = $this->tour->activity()->where('action', Action::STOP)
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

        return [
            'time' => $timeSpent,
            'downloads' => $downloads,
            'actions' => $actions,
        ];
    }
}
