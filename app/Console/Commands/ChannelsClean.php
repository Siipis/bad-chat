<?php

namespace App\Console\Commands;

use App\Channel;
use Illuminate\Console\Command;

class ChannelsClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft deletes expired channels';

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
        $i = 0;

        foreach(Channel::expired()->get() as $channel) {
            $i++;
            \FrontLog::debug("Channel $channel->name has expired.");
            $this->info("Channel $channel->name has expired.");

            $channel->delete();
        }

        $this->info("$i channels have expired.");
    }
}
