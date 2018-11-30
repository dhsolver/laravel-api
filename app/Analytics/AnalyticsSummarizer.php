<?php

namespace App\Analytics;

use App\Activity;
use App\DeviceStat;
use App\DeviceType;
use App\Os;
use App\Tour;
use App\TourStat;
use App\Action;
use App\StopStat;

class AnalyticsSummarizer
{
    /**
     * The device type and operating systems that should have their
     * own stat summaries.
     *
     * @var array
     */
    protected static $deviceTypes = [
        ['os' => Os::IOS, 'type' => DeviceType::PHONE],
        ['os' => Os::IOS, 'type' => DeviceType::TABLET],
        ['os' => Os::ANDROID, 'type' => DeviceType::PHONE],
        ['os' => Os::ANDROID, 'type' => DeviceType::TABLET],
    ];

    /**
     * Generates stat summary for the given Tour and date.
     *
     * @param Tour $tour
     * @param int $timestamp
     * @return void
     */
    public function summarizeTour(Tour $tour, $timestamp)
    {
        $yyyymmdd = date('Ymd', $timestamp);

        // calculate devices stats
        foreach (self::$deviceTypes as $device) {
            $stats = $this->getTourStatsForDevice($tour, $timestamp, $device['os'], $device['type']);

            if ($tour->deviceStats()->forDate($yyyymmdd)->forDevice($device['os'], $device['type'])->exists()) {
                $tour->deviceStats()->forDate($yyyymmdd)->forDevice($device['os'], $device['type'])->update($stats);
            } else {
                $tour->deviceStats()->create(array_merge($stats, ['yyyymmdd' => $yyyymmdd]));
            }
        }

        // calculate tour stats
        if ($tour->stats()->forDate($yyyymmdd)->exists()) {
            $tour->stats()->forDate($yyyymmdd)->update($this->getTourStatsForDate($tour, $timestamp));
        } else {
            $tour->stats()->create(array_merge($this->getTourStatsForDate($tour, $timestamp), ['yyyymmdd' => $yyyymmdd]));
        }

        // calculate stop stats
        foreach ($tour->stops as $stop) {
            if ($stop->stats()->forDate($yyyymmdd)->exists()) {
                $stop->stats()->forDate($yyyymmdd)->update($this->getStopStatsForDate($stop, $timestamp));
            } else {
                $stop->stats()->create(array_merge($this->getStopStatsForDate($stop, $timestamp), ['yyyymmdd' => $yyyymmdd]));
            }
        }
    }

    /**
     * Re-calculate and set all previous day stats to final.
     *
     * @return void
     */
    public function finalizePreviousDays()
    {
        $stats = DeviceStat::where('yyyymmdd', '<', date('Ymd', strtotime('today')))
                    ->where('final', false)
                    ->get();

        foreach ($stats as $summary) {
            $tour = $summary->tour;
            $summary->update(array_merge($this->getTourStatsForDevice($tour, strtotime($summary->yyyymmdd), $summary->os, $summary->device_type), ['final' => true]));
        }

        $stats = TourStat::where('yyyymmdd', '<', date('Ymd', strtotime('today')))
            ->where('final', false)
            ->get();

        foreach ($stats as $summary) {
            $tour = $summary->tour;
            $summary->update(array_merge($this->getTourStatsForDate($tour, strtotime($summary->yyyymmdd)), ['final' => true]));
        }

        $stats = StopStat::where('yyyymmdd', '<', date('Ymd', strtotime('today')))
            ->where('final', false)
            ->get();

        foreach ($stats as $summary) {
            $stop = $summary->stop;
            $summary->update(array_merge($this->getStopStatsForDate($stop, strtotime($summary->yyyymmdd)), ['final' => true]));
        }
    }

    /**
     * Create breakdown by device type summary for tours.
     *
     * @param \App\Tour $tour
     * @param int $timestamp
     * @param string $os
     * @param string $type
     * @return array
     */
    protected function getTourStatsForDevice($tour, $timestamp, $os, $type)
    {
        $date = date('m/d/Y', $timestamp);

        $downloads = $tour->activity()->where('action', Action::DOWNLOAD)
            ->betweenDates($date, $date)
            ->join('devices', 'device_id', 'devices.id')
            ->where('devices.os', $os)
            ->where('devices.type', $type)
            ->count();

        $actions = $tour->activity()->whereIn('action', [Action::SHARE, Action::LIKE])
            ->betweenDates($date, $date)
            ->join('devices', 'device_id', 'devices.id')
            ->where('devices.os', $os)
            ->where('devices.type', $type)
            ->count();

        $visitors = Activity::where('action', Action::VISIT)
            ->where('actionable_type', 'App\TourStop')
            ->whereIn('actionable_id', $tour->stops->pluck('id'))
            ->betweenDates($date, $date)
            ->join('devices', 'device_id', 'devices.id')
            ->where('devices.os', $os)
            ->where('devices.type', $type)
            ->groupBy('devices.id')
            ->count();

        return [
            'os' => $os,
            'device_type' => $type,
            'downloads' => $downloads,
            'actions' => $actions,
            'visitors' => $visitors,
        ];
    }

    /**
     * Generate stats totals for the given stop and date.
     *
     * @param \App\TourStop $stop
     * @param int $timestamp
     * @return array
     */
    protected function getStopStatsForDate($stop, $timestamp)
    {
        $date = date('m/d/Y', $timestamp);

        $actions = $stop->activity()->whereIn('action', [Action::LIKE, Action::SHARE])
            ->betweenDates($date, $date)
            ->count();

        $visits = $stop->activity()->where('action', Action::VISIT)
            ->betweenDates($date, $date)
            ->count();

        $starts = $stop->activity()->where('action', Action::START)
            ->betweenDates($date, $date)
            ->get();

        $finishes = $stop->activity()->where('action', Action::STOP)
            ->betweenDates($date, date('m/d/Y', strtotime('+1 day', $timestamp)))
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
            'time_spent' => $timeSpent,
            'visits' => $visits,
            'actions' => $actions,
        ];
    }

    /**
     * Generate stats totals for the given tour and date.
     *
     * @param \App\Tour $tour
     * @param int $timestamp
     * @return array
     */
    protected function getTourStatsForDate($tour, $timestamp)
    {
        $date = date('m/d/Y', $timestamp);

        $actions = $tour->activity()->whereIn('action', [Action::LIKE, Action::SHARE])
            ->betweenDates($date, $date)
            ->count();

        $downloads = $tour->activity()->where('action', Action::DOWNLOAD)
            ->betweenDates($date, $date)
            ->count();

        $starts = $tour->activity()->where('action', Action::START)
            ->betweenDates($date, $date)
            ->get();

        $finishes = $tour->activity()->where('action', Action::STOP)
            ->betweenDates($date, date('m/d/Y', strtotime('+1 day', $timestamp)))
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
            'time_spent' => $timeSpent,
            'downloads' => $downloads,
            'actions' => $actions,
        ];
    }
}
