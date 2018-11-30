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
    protected $signature = 'analytics:summary {date?}';

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
        $this->date = $this->argument('date') ? strtotime($this->argument('date')) : strtotime('today');

        $this->info('Running analytics summary for ' . date('m/d/Y', $this->date));

        $summarizer = new AnalyticsSummarizer();

        // create temp summaries for the current day
        foreach (Tour::published()->get() as $tour) {
            $summarizer->summarizeTour($tour, $this->date);
        }

        // finalize all summaries for previous days
        $summarizer->finalizePreviousDays();
    }
}
