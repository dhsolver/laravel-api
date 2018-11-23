<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tour;
use App\Action;

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
        foreach (Tour::all() as $tour) {
            $date = strtotime('today');
            $yyyymmdd = date('Ymd', $date);

            if ($tour->stats()->forDate($yyyymmdd)->exists()) {
                $tour->stats()->forDate($yyyymmdd)->update($this->getStatsForDate($tour, $date));
            } else {
                $tour->stats()->create(array_merge($this->getStatsForDate($tour, $date), ['yyyymmdd' => $yyyymmdd]));
            }
        }

        // - tour_stats
        // tour_id
        // yyyymmdd
        // downloads
        // time_spent
        // actions

        // - stop_stats
        // tour_id
        // stop_id
        // yyymmdd
        // visits
        // time_spent
        // actions

        // - device_stats
        // tour_id
        // yyymmdd
        // os
        // device_type
        // downloads
        // actions
        // unique visitors
    }

    public function getStatsForDate($tour, $timestamp)
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
