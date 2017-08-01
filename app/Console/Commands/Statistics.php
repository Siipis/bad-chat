<?php

namespace App\Console\Commands;

use App\Channel;
use App\Message;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Statistics extends Command
{
    private $expires = 7; // days
    private $entries = 50;

    private $fetch = 5000;
    private $take = 1000;

    private $excluded = [];

    private $directory = "app/statistics/";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:stats {user?} {--all} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the chat statistics';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $file = \File::get(storage_path($this->directory . '/excluded_words.txt'));

        $this->excluded = array_filter(preg_split("/\s/", $file));
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('app.env') == 'local') {
            \DB::setDefaultConnection('remote');
        }

        if ($this->option('all')) {
            $this->info("Updating all users. Please wait...");

            foreach (User::active()->where('id', '!=', '1')->get() as $user) {
                $this->readFor($user->name);
            }

            $this->readFor();
        } else {
            $this->readFor($this->argument('user'));
        }
    }

    /**
     * @param null $target
     * @return bool
     */
    private function readFor($target = null) {
        try {
        $date = $this->getUpdatedDate($target);

        if (!$this->option('force') && Carbon::now()->subDays($this->entries) < $date) {
            $this->info('Skipping entries'. (!is_null($target) ? " for $target" : '') . ".\n");

            return false;
        }

        $this->info('Reading entries'. (!is_null($target) ? " for $target" : '') . '...');

        $query = Message::where('type', 'post')->public();

        foreach (Channel::defaults()->get() as $channel) {
            $query->where('channel_id', $channel->id);
        }

        $query->orderBy('id', 'desc');

        if ($target) {
            $user = User::findByName($target);

            if (!$user instanceof User) {
                $this->error("User not found.");
                return false;
            }

            $seen = $user->logins->last();

            if ($seen < $date) {
                $this->info("Records have not changed since last update.\n");

                return false;
            }

            $query = $query->where('user_id', $user->id);
        }

        $count = $query->count();

        $words = collect();

        $this->info("Found a total of $count entries.");

        if ($count < $this->take) {
            $this->info("Skipping user for lack of data.\n");

            return false;
        }

        // Calculate steps
        $steps = round($this->fetch / $this->take);
        $total = $this->take * $steps;

        // Recalculate if there are less entries than maximum accepted
        if ($count < $total) {
            $steps = ceil($count / $this->take);
            $total = $count;
        }

        $progress = $this->output->createProgressBar($steps);

        for ($i = 1; $i <= $steps; $i++) {
            foreach ($query->skip(($i - 1) * $this->take)->take($i * $this->take)->pluck('message') as $message) {
                $message = $this->cleanUp($message);

                $words = $words->merge($this->split($message));
            }

            $progress->advance();
        }

        $progress->finish();

        $frequencies = array_count_values(array_filter($words->toArray()));

        arsort($frequencies);

        $data = [
            'updated' => Carbon::now()->toAtomString(),
            'entries' => $count,
            'data' => array_slice($frequencies, 0, $this->entries),
        ];

        $name = !is_null($target) ? $target : 'all';
        $filename = storage_path($this->directory . "/statistics-$name.yaml");

        \File::put($filename, \YAML::dump($data), LOCK_EX);

        $this->info("\n\nDone!\n\n");

        return true;
        } catch (\Exception $e) {
            $this->info($e->getMessage());
            return false;
        }
    }

    /**
     * @param $target
     * @return mixed
     */
    private function getUpdatedDate($target)
    {
        $name = isset($target) ? $target : 'all';
        $filename = storage_path($this->directory . "/statistics-$name.yaml");

        if (\File::exists($filename)) {
            $stats = \YAML::parse(\File::get($filename));

            return Carbon::parse($stats['updated']);
        }

        return null;
    }

    /**
     * Cleans up the message
     *
     * @param $message
     * @return string
     */
    private function cleanUp($message)
    {
        $message = strtolower($message);

        $message = preg_replace("/\[url(\S+)\[\/url\]/", '', $message);
        $message = preg_replace("/http(\S)+/", '', $message);
        $message = preg_replace("/#[a-zA-Z0-9]+/", '', $message);
        $message = preg_replace("/\[(\/)?([a-z]+)\]/", '', $message);
        $message = preg_replace("/[=,?!)(|_;.>'<\#\+]/", '', $message);
        $message = preg_replace("/:[a-z]+:/", '', $message);

        $message = utf8_encode($message);

        return trim($message);
    }

    /**
     * Split the message into words
     *
     * @param $message
     * @return mixed
     */
    private function split($message)
    {
        $split = collect(array_filter(preg_split("/\s/", $message)));

        $words = $split->diff($this->excluded);

        return $words;
    }
}
