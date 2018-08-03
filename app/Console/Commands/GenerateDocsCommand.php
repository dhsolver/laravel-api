<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDocsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate static html file from the API specifications yaml file.';

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
        $result = exec('redoc-cli bundle docs/api.yaml -o docs/api.html --title "Junket API 1.0 Documentation"');

        if (!file_exists('docs/api.html')) {
            $this->error('Error running bundling documentation!');
            return;
        }

        $this->info('API documentation generated to docs/api.html');
    }

    /**
     * Recursive delete directory.
     *
     * @param string $dir
     * @return boolean
     */
    public function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
