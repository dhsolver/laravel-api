<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tour;
use App\Action;
use App\TourStat;

class SummarizeAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create daily summary for Tour analytics.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // create temp summaries for the current day
        foreach (Tour::published() as $tour) {
            $date = strtotime('today');
            $yyyymmdd = date('Ymd', $date);

            // calculate tour stats
            if ($tour->stats()->forDate($yyyymmdd)->exists()) {
                $tour->stats()->forDate($yyyymmdd)->update($this->getTourStatsForDate($tour, $date));
            } else {
                $tour->stats()->create(array_merge($this->getTourStatsForDate($tour, $date), ['yyyymmdd' => $yyyymmdd]));
            }

            // calculate stop stats
            foreach ($tour->stops as $stop) {
                if ($stop->stats()->forDate($yyyymmdd)->exists()) {
                    $stop->stats()->forDate($yyyymmdd)->update($this->getStopStatsForDate($stop, $date));
                } else {
                    $stop->stats()->create(array_merge($this->getStopStatsForDate($stop, $date), ['yyyymmdd' => $yyyymmdd]));
                }
            }
        }

        // finalize all summaries for previous days
        $stats = TourStat::where('yyyymmdd', '<', date('Ymd', strtotime('today')))
            ->where('final', false)
            ->get();

        foreach ($stats as $summary) {
            $summary->update(array_merge($this->getTourStatsForDate($tour, $summary->yyyymmdd), ['final' => true]));
        }

        // - device_stats
        // tour_id
        // yyymmdd
        // os
        // device_type
        // downloads
        // actions
        // unique visitors
    }

    public function getStopStatsForDate($stop, $timestamp)
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

    public function getTourStatsForDate($tour, $timestamp)
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
