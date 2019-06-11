<?php

namespace App\Reports;

use App\Activity;
use App\Device;
use App\Os;
use App\DeviceType;

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

        // calcuating downloads by OS and Device Type
        $downloads = Activity::betweenDates($this->start_date, $this->end_date)
            ->select('device_id')
            ->distinct()
            ->where('action', 'start_stop')
            ->where('actionable_id', $this->tour->id)
            ->where('actionable_type', 'App\Tour')
            ->get();

        $oses = Os::all();
        $deviceTypes = DeviceType::all();
        $downloadsByType = [];
        foreach ($oses as $os) {
            foreach ($deviceTypes as $deviceType) {
                $downloadsByType[$os][$deviceType] = 0;
            }
        }

        foreach ($downloads as $download) {
            $device = Device::find($download->device_id);
            $downloadsByType[$device->os][$device->type] ++;
        }

        $stats = $this->tour->deviceStats()
            ->betweenDates($this->start_date, $this->end_date)
            ->selectRaw('os, device_type')
            ->groupBy(['os', 'device_type'])
            ->get();
        
        foreach ($stats as &$stat) {
            $stat->downloads = $downloadsByType[$stat->os][$stat->device_type];
        }

        return $stats;
    }
}
