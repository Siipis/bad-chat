<?php


namespace App\Listeners;

use Log;
use Illuminate\Mail\Events\MessageSending;

class LogEmail
{
    public function handle(MessageSending $event)
    {
        $message = $event->message;

        Log::info($message);
    }
}