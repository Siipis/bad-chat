<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use File;
use Illuminate\Console\Command;

class UploadClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes old uploaded files';

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
     * @return void
     */
    public function handle()
    {
        $date = Carbon::today()->subDays(config('image.keep'));

        $storage = config('image.storage');

        foreach (File::directories($storage) as $directory) {
            $name = basename($directory);

            $nameDate = Carbon::createFromFormat("Y-m-d", $name)->startOfDay();

            if ($nameDate <= $date) {
                File::deleteDirectory($directory);

                $this->info("Deleted directory $directory.");
            }
        }
    }
}
