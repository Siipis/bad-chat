<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Event;
use App\Login;
use App\Message;
use App\User;
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
        $search = $request->input('search');

        if (!empty($channel)) {
            $channel = Channel::findByNameOrFail("#$channel");
        } else {
            $channel = Channel::defaults()->first();
        }

        $query = Message::channel($channel)->public();

        if (!empty($search)) {
            $query = $query->where('message', 'like', "%$search%");

            $users = User::active()->where('name', 'like', "%$search%")->get(['id'])->pluck('id');

            foreach ($users as $id) {
                $query->orWhere('user_id', $id);
            }
        }

        $messages = $query->orderBy('id', 'desc')->paginate(50);

        $messages->setPath('logs?channel='. trim($channel->name, '#') . (empty($search) ? '' : "&search=$search"));

        foreach ($messages as $key => $message) {
            $messages[$key] = $message->toModel();
        }

        return CMS::render('logs.main', [
            'channel' => $channel,
            'channels' => Channel::all()->sortBy('name'),
            'search' => $search,
            'messages' => $messages,
        ]);
    }

    public function postIndex(Request $request)
    {
        $channel = $request->input('channel');
        $search = $request->input('search');

        if (!empty($search)) {
            return redirect("logs?channel=$channel&search=$search");
        }

        return redirect("logs?channel=$channel");
    }

    public function getVisits()
    {
        return CMS::render('logs.visits', [
            'visits' => Login::orderBy('id', 'desc')->simplePaginate(50),
        ]);
    }

    public function getEvents()
    {
        return CMS::render('logs.events', [
            'events' => Event::orderBy('id', 'desc')->simplePaginate(50),
        ]);
    }

    public function getErrors()
    {
        $log = \File::get(storage_path('logs/laravel.log'));

        $log = preg_replace('/[\n]/', '<br />', $log);

        return CMS::render('logs.errors', [
            'log' => $log
        ]);
    }
}
