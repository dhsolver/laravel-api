<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tour;

class CalculateTourLengths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tour:fix-length';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-calculate length of all Tours in the system.';

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
        $success = 0;
        $errors = 0;
        $failures = 0;

        $this->info('Resetting length of ' . Tour::count() . ' tours...');

        foreach (Tour::all() as $tour) {
            try {
                if ($tour->updateLength()) {
                    $success++;
                } else {
                    $failures++;
                }
            } catch (\Exception $ex) {
                $errors++;
            }
        }

        $this->info("Operation complete: $success Tours updated, $failures failures, $errors errors.");
    }
}
