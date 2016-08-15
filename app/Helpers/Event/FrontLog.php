<?php


namespace App\Helpers\Event;

use App\Event as Log;
use Mail;

class FrontLog
{
    protected $levels = [
        'emergency' => 600,
        'alert' => 550,
        'critical' => 500,
        'error' => 400,
        'warning' => 300,
        'notice' => 250,
        'info' => 200,
        'debug' => 100,
    ];

    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * The system is unusable
     *
     * @param $message
     * @param $context
     */
    public function emergency($message, $context = [])
    {
        $this->createEvent('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately
     *
     * @param $message
     * @param $context
     */
    public function alert($message, $context = [])
    {
        $this->createEvent('alert', $message, $context);
    }

    /**
     * Critical conditions, e.g. unexpected events
     *
     * @param $message
     * @param $context
     */
    public function critical($message, $context = [])
    {
        $this->createEvent('critical', $message, $context);
    }

    /**
     * Errors that don't require immediate action but that should be monitored
     *
     * @param $message
     * @param $context
     */
    public function error($message, $context = [])
    {
        $this->createEvent('error', $message, $context);
    }

    /**
     * Exceptional events that are not errors, e.g. undesirable things that are not necessarily wrong
     *
     * @param $message
     * @param $context
     */
    public function warning($message, $context = [])
    {
        $this->createEvent('warning', $message, $context);
    }

    /**
     * Normal but significant events
     *
     * @param $message
     * @param $context
     */
    public function notice($message, $context = [])
    {
        $this->createEvent('notice', $message, $context);
    }

    /**
     * Interesting events, e.g. user logs in
     *
     * @param $message
     * @param $context
     */
    public function info($message, $context = [])
    {
        $this->createEvent('info', $message, $context);
    }

    /**
     * Detailed bug information
     *
     * @param $message
     * @param $context
     */
    public function debug($message, $context = [])
    {
        $this->createEvent('debug', $message, $context);

    }

    private function createEvent($level, $event, $context)
    {
        $code = $this->levels[$level];
        $url = \Request::fullUrl();
        $ip = $_SERVER['REMOTE_ADDR'];

        if ($code >= config('chat.errors.minLevel')) {
            $emails = config('chat.errors.emails');

            foreach(explode('\r\n', $emails) as $email) {
                Mail::send('emails.log_alert', [
                    'level' => $level,
                    'event' => $event,
                    'context' => print_r($context, true),
                    'url' => $url,
                    'ip' => $ip,
                ], function ($m) use ($email, $level, $event) {
                    $m->subject(ucfirst($level) . ": $event");

                    $m->to($email);
                });
            }
        }

        Log::create([
            'level' => $level,
            'code' => $code,
            'event' => $event,
            'context' => json_encode($context),
            'url' => $url,
            'ip' => $ip,
        ]);
    }
}