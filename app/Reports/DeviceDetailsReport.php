<?php

namespace App\Reports;

class DeviceDetailsReport extends BaseReport
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
            $firstRecord = $this->tour->deviceStats()->orderBy('yyyymmdd')->first();
            if (empty($firstRecord)) {
                return [];
            }
            $date = date('m/d/Y', strtotime($firstRecord->yyyymmdd));
            $this->forDates($date, date('m/d/Y', strtotime('now')));
        }

        return $this->tour->deviceStats()
            ->betweenDates($this->start_date, $this->end_date)
            ->orderBy('yyyymmdd')
            ->selectRaw('os, device_type, sum(downloads) as downloads, sum(visitors) as visitors, sum(actions) as actions')
            ->groupBy(['os', 'device_type'])
            ->get();
    }
}
