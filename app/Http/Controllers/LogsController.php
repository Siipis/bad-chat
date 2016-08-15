<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Event;
use App\Login;
use App\Message;
use Illuminate\Http\Request;

use App\Http\Requests;
use CMS;

class LogsController extends Controller
{
    public function __construct()
    {
        $this->middleware('access:view.logs', [
            'only' => [
                'getIndex',
            ]
        ]);

        $this->middleware('access:view.visits', [
            'only' => [
                'getVisits',
            ]
        ]);

        $this->middleware('access:view.events', [
            'only' => [
                'getEvents',
            ]
        ]);
    }

    public function getIndex(Request $request)
    {
        $channel = $request->input('channel');

        if (!empty($channel)) {
            $channel = Channel::findByNameOrFail("#$channel");
        } else {
            $channel = Channel::defaults()->first();
        }

        $messages = Message::channel($channel)->public()->simplePaginate(50);

        $messages->setPath('logs?channel='. trim($channel->name, '#'));

        foreach ($messages as $key => $message) {
            $messages[$key] = $message->toModel();
        }

        return CMS::render('logs.main', [
            'channel' => $channel,
            'channels' => Channel::all()->sortByDesc('isDefault'),
            'messages' => $messages,
        ]);
    }

    public function postIndex(Request $request)
    {
        $channel = $request->input('channel');

        return redirect("logs?channel=$channel");
    }

    public function getVisits()
    {
        return CMS::render('logs.visits', [
            'visits' => Login::simplePaginate(50),
        ]);
    }

    public function getEvents()
    {
        return CMS::render('logs.events', [
            'events' => Event::simplePaginate(50),
        ]);
    }
}
