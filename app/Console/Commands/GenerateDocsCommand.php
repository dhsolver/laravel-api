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
        if (file_exists('index.html')) {
            $this->error('Can\'t generate documentation: index.html file already exists in this directory');
            return;
        }

        // use swagger-codegen to generate a static html file
        $result = exec('swagger-codegen generate -i docs/api.yaml -l html2');

        if (!file_exists('index.html')) {
            $this->error('Error running codegen!');
            return;
        }

        // move generated file to docs folder
        rename('index.html', 'docs/api.html');

        // delete left over codegen files to keep working directory clean.
        unlink('.swagger-codegen-ignore');
        $this->delTree('.swagger-codegen');

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
