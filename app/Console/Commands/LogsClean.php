<?php

namespace App\Console\Commands;

use App\Message;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LogsClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans out old conversation logs';

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
        $yearAgo = Carbon::now()->addYear(-1);

        $deleted = Message::where('created_at', '<=', $yearAgo)->forceDelete();

        $this->info("Cleaned out $deleted messages from the conversation log.");
    }
}
