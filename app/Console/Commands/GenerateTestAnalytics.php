<?php

namespace App\Console\Commands;

use App\DeviceStat;
use App\StopStat;
use App\TourStat;
use Illuminate\Console\Command;
use App\Tour;
use Illuminate\Support\Carbon;
use Tests\HasTestTour;

class GenerateTestAnalytics extends Command
{
    use HasTestTour;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:analytics {tour} {days?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate back-dated analytics for the given tour.';

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
        $tour = Tour::findOrFail($this->argument('tour'));
        $days = (int) $this->argument('days') ?? 30;

        $this->info("Generating $days days worth of analytics for tour: {$tour->title}");

         $date = Carbon::now();

         for ($i = 0; $i < $days; $i++) {
             $date = $date->subDay();
             $yyyymmdd = $date->format('Ymd');

             TourStat::where([
                'yyyymmdd' => $yyyymmdd,
                'tour_id' => $tour->id,
             ])->delete();

             factory(TourStat::class)->create([
                 'yyyymmdd' => $yyyymmdd,
                 'tour_id' => $tour->id,
             ]);

             DeviceStat::where([
                'yyyymmdd' => $yyyymmdd,
                'tour_id' => $tour->id,
             ])->delete();

             factory(DeviceStat::class)->create([
                 'yyyymmdd' => $yyyymmdd,
                 'tour_id' => $tour->id,
                 'os' => 'ios',
                 'device_type' => 'phone',
             ]);
             factory(DeviceStat::class)->create([
                 'yyyymmdd' => $yyyymmdd,
                 'tour_id' => $tour->id,
                 'os' => 'ios',
                 'device_type' => 'tablet',
             ]);
             factory(DeviceStat::class)->create([
                 'yyyymmdd' => $yyyymmdd,
                 'tour_id' => $tour->id,
                 'os' => 'android',
                 'device_type' => 'phone',
             ]);
             factory(DeviceStat::class)->create([
                 'yyyymmdd' => $yyyymmdd,
                 'tour_id' => $tour->id,
                 'os' => 'android',
                 'device_type' => 'tablet',
             ]);

             foreach ($tour->stops as $stop) {
                 StopStat::where([
                    'yyyymmdd' => $yyyymmdd,
                    'stop_id' => $stop->id,
                 ])->delete();

                factory(StopStat::class)->create([
                    'yyyymmdd' => $yyyymmdd,
                    'stop_id' => $stop->id,
                ]);
             }
         }

        $this->info('Success.');
    }
}
