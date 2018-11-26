<?php

namespace App\Console\Commands;

use App\Analytics\AnalyticsSummarizer;
use Illuminate\Console\Command;
use App\Tour;

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
        $summarizer = new AnalyticsSummarizer();

        // create temp summaries for the current day
        foreach (Tour::published()->get() as $tour) {
            $date = strtotime('today');
            $summarizer->summarizeTour($tour, $date);
        }

        // finalize all summaries for previous days
        $summarizer->finalizePreviousDays();
    }
}
