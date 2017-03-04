<?php

namespace App\Http\Controllers;

use Access;
use App\Ban;
use App\Channel;
use App\Http\Requests;
use App\Ignore;
use App\Invite;
use App\Login;
use App\Message;
use App\Models\Message\Emote;
use App\Models\Message\Info;
use App\Models\Message\Post;
use App\Models\Message\System;
use App\Models\Message\Whisper;
use App\Online;
use App\Settings;
use App\User;
use App\Vouch;
use Auth;
use Carbon\Carbon;
use DB;
use Log;
use FrontLog;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatController extends Controller
{
    /**
     * Available commands
     *
     * @var array
     */
    protected $commands;

    /**
     * The correct channel name format
     *
     * @var string
     */
    protected $channelRegex = "#[a-zA-Z_-]+";

    /**
     * ChatController constructor.
     */
    public function __construct()
    {
        $this->middleware('online', [
            'except' => [
                'getLogout',
            ]
        ]);

        $this->middleware('discourage', [
            'except' => [
                'getLogout',
            ]
        ]);

        $this->commands = $this->getCommands();
    }

    /**
     * Defines the available commands
     *
     * @return array
     */
    private function getCommands()
    {
        $nick = "([0-9a-z_-]+)";
        $channel = "($this->channelRegex)";
        $any = "(.*)+";
        $alpha_dash = "([a-z_-]+)";

        return [
            'whisper' => "/^(\/w|\/whisper|\/msg) $nick $any$/i",
            'seen' => "/^(\/seen|\/online) $nick$/i",
            'emote' => "/^(\/me|\/emote|\/do) $any$/i",
            'roll' => "/^(\/roll|\/dice|\/die) ([0-9]+)d([0-9]+)$/i",
            'join' => "/^(\/join|\/enter) $channel$/i",
            'part' => "/^(\/part|\/leave)( $channel)?$/i",
            'invite' => "/^(\/inv|\/invite) $nick( $channel)?+$/i",
            'uninvite' => "/^(\/uninv|\/uninvite) $nick( $channel)?+$/i",
            'protegees' => "/^(\/protegees|\/vouched)$/i",
            'protectors' => "/^(\/protectors|\/vouches)$/i",
            'vouch' => "/^(\/vouch) $nick$/i",
            'unvouch' => "/^(\/unvouch|\/devouch) $nick$/i",
            'promote' => "/^(\/promote) $nick$/i",
            'demote' => "/^(\/demote) $nick$/i",
            'topic' => "/^(\/topic|\/subject) $any+$/i",
            'set' => "/^(\/set|\/settings) $alpha_dash $any+$/i",
            'about' => "/^(\/about|\/info)( $channel)?$/i",
            'ignore' => "/^(\/ignore|\/silence|\/mute) $nick$/i",
            'unignore' => "/^(\/unignore|\/unsilence|\/unmute) $nick$/i",
            'ban' => "/^(\/ban|\/remove) $nick$/i",
            'unban' => "/^(\/unban|\/revoke) $nick$/i",
            'kick' => "/^(\/kick|\/boot) $nick$/i",
            'whois' => "/^(\/who|\/whois|\/ip) $nick$/i",
            'find' => "/^(\/find|\/where|\/channels) $nick$/i",
            'logout' => "/^(\/quit|\/logout|\/exit)$/i",
            'afk' => "/^(\/afk|\/away)$/i",
            'brb' => "/^(\/brb)$/i",
            'gaming' => "/^(\/game|\/gaming)$/i",
            'working' => "/^(\/work|\/working)$/i",
            'art' => "/^(\/art|\/writing|\/painting)$/i",
            'phone' => "/^(\/phone|\/call)$/i",
            'back' => "/^(\/back|\/online)$/i",
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Http methods
    |--------------------------------------------------------------------------
    |
    | Routing methods to be called in URL
    |
    */


    /**
     * Logs the user in
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        try {
            $auth = Auth::user();

            if ($auth->isSuspended()) {
                Log::debug('User is suspended in '. __FUNCTION__, [
                    'IP' => $request->ip(),
                    'Auth' => $auth,
                    'URL' => $request->fullUrl(),
                ]);

                Login::logout($auth);
                
                return response('Logging out.', 307);
            }

            $settings = Settings::user($auth);

            if (!$settings instanceof Settings) {
                $settings = Settings::defaults($auth);
            }

            $firstChannel = null;
            $failedJoining = [];

            // Attempt to auto-join channels...
            foreach ($settings->channels as $channel) {
                $channel = $this->initChannel($channel);

                if ($channel->canJoin($auth)) {
                    if (is_null($firstChannel)) {
                        $firstChannel = $channel;
                    }

                    $this->enterChannel($channel);
                } else {
                    array_push($failedJoining, $channel);
                }
            }

            // (Declare join errors)
            if ($firstChannel instanceof Channel) {
                foreach ($failedJoining as $channel) {
                    $this->createInfo($firstChannel, 'join_error', $channel->name);
                }
            }

            // ...or fall back to the defaults
            if (is_null($firstChannel)) {
                foreach (Channel::defaults()->get() as $channel) {
                    if (is_null($firstChannel)) {
                        $firstChannel = $channel;
                    }

                    $this->enterChannel($channel);
                }

                // Declare join errors
                foreach ($failedJoining as $channel) {
                    $this->createInfo($firstChannel, 'join_error', $channel->name);
                }
            }

            // Compile the response
            $data = [
                'user' => $auth,
                'channel' => $firstChannel,
                'config' => [
                    'interval' => [
                        'messages' => ($settings->interval > 0 ? $settings->interval : config('chat.interval.messages')) * 1000,
                        'notifications' => config('chat.interval.notifications') * 1000,
                    ],
                    'maxLength' => config('chat.input.maxLength'),
                    'settings' => $settings,
                ],
            ];

            $this->closeConnection();

            return response()->json($data);
        } catch (\Exception $e) {
            $this->closeConnection();

            if (config('app.debug')) {
                throw $e;
            }

            return response($e->getMessage(), 500);
        }
    }

    /**
     * Logs the user out
     *
     * @return ResponseFactory|Response
     */
    public function getLogout()
    {
        Login::logout();

        Auth::logout();

        $this->closeConnection();

        return response('Logging out.', 307);
    }

    /**
     * Returns a JSON object with the latest data
     *
     * @param Requests\UpdateRequest $request
     * @return Response
     */
    public function postUpdate(Requests\UpdateRequest $request)
    {
        try {
            $auth = Auth::user();
            $login = Login::active($auth);

            if (is_null($login) || is_null($auth)) {
                Log::debug('No valid login was found in '. __FUNCTION__, [
                    'IP' => $request->ip(),
                    'Auth' => $auth,
                    'URL' => $request->fullUrl(),
                ]);

                Auth::logout();

                return response('Logging out.', 307);
            }

            if ($login->channels->count() == 0 || $auth->isSuspended()) {
                Log::debug('Invalid login status in '. __FUNCTION__, [
                    'IP' => $request->ip(),
                    'Auth' => $auth,
                    'URL' => $request->fullUrl(),
                ]);

                return response('Kicked.', 409);
            }

            $ignored = $auth->ignores->map(function ($ignore) {
                return $ignore->target;
            });

            $channel = $this->getChannel($request);
            $channels = $this->getChannels($request);
            $latest = $this->getLatest($request);

            $messages = $latest > 0 ? Message::after($latest, $channel) : Message::latest($channel, config('chat.channels.backtrack'));

            $rows = $messages->filter(function ($row) use ($auth, $ignored) {
                if (isset($row->user_id) && !is_null($row->user_id)) {
                    return !$ignored->contains($row->user);
                }

                if ($row->message == 'kicked') {
                    if (isset($row->context['target'])) {
                        if ($row->context['target'] == $auth->name) {
                            return false;
                        }
                    }
                }

                return true;
            });

            $users = $channel->online->map(function ($online) use ($channel, $auth, $ignored) {
                $user = $online->login->user;

                if ($channel->isStaff($auth)) {
                    $user->role = $channel->getRole($user);
                } else {
                    $user->role = 'member';
                }

                if ($ignored->contains($user)) {
                    $user->ignored = true;
                }

                $user->status = $online->status;

                return $user;
            });

            $channels = $login->channels->map(function ($c) use ($channel, $channels, $latest, $rows) {
                $obj = new Channel; // Use a dummy to avoid excessive information

                $obj->name = $c->name;

                // Object is the current channel
                if ($c->name == $channel->name) {
                    $obj->latest = $rows->count() > 0 ? $rows->last()->id : $latest;
                    $obj->changed = false;

                    return $obj;
                }

                // Object is some other channel
                $item = $channels->where('name', $c->name)->first();

                if (isset($item['latest']) && $item['latest'] > 0) {
                    $obj->latest = $item['latest'];
                    $obj->changed = Message::existsAfter($obj->latest, $c, false);
                } else {
                    $obj->latest = 0;
                    $obj->changed = false;
                }

                return $obj;
            });

            // If the current channel is no longer available...
            if(!$login->channels->contains($channel)) {
                $channel = $login->channels->first();

                $request->merge([
                    'channel' => $channel->toArray(),
                ]);

                return $this->postUpdate($request);
            }

            // Otherwise, prepare the data
                $data = [
                'channel' => $channel,
                'channels' => $channels,
                'users' => $users,
                'rows' => $rows,
            ];

            $data = collect($data)->toArray(); // prevent constant JSON errors

            $this->closeConnection();

            return response()->json($data);
        } catch (\Exception $e) {
            $this->closeConnection();

            if (config('app.debug')) {
                throw $e;
            }

            return response($e->getMessage(), 500);
        }

    }

    public function postNotifications()
    {
        $notifications = 0;

        if (Access::can('control.registration')) {
            $notifications += User::where('is_active', false)->count();
        }

        $notifications += \App\Conversation::readable()->visible()->get()->filter(function($c) {
            return $c->hasUnread();
        })->count();

        $this->closeConnection();

        return response()->json($notifications);
    }

    /**
     * Stores the user input
     *
     * @param Requests\SendRequest $request
     * @return mixed|Response
     */
    public function postSend(Requests\SendRequest $request)
    {
        try {
            $auth = Auth::user();
            $login = Login::active($auth);

            $channel = $this->getChannel($request);
            $message = $this->getMessage($request);

            if ($login->channels->count() == 0 || $auth->isSuspended()) {
                Log::debug('Invalid login status in '. __FUNCTION__, [
                    'IP' => $request->ip(),
                    'Auth' => $auth,
                    'URL' => $request->fullUrl(),
                ]);

                return response('Kicked.', 409);
            }

            if ($login->hasStatus() && !preg_match($this->commands['back'], $message)) {
                if (!in_array($login->getStatus(), Online::getPersistStatuses())) {
                    // Only remove the status for non-sticky statuses like afk
                    $this->setStatus($channel, 'online');
                }
            }

            foreach ($this->commands as $type => $command) {
                if (preg_match($command, $message)) {
                    return call_user_func_array([$this, 'create' . ucfirst($type)], [
                        $channel, $message
                    ]);
                }
            }

            if (starts_with($message, '/')) {
                return $this->createInfo($channel, 'unknown_command', trim($message));
            }

            return $this->createPost($channel, $message, $request->input('color'));
        } catch (\Exception $e) {
            $this->closeConnection();

            if (config('app.debug')) {
                throw $e;
            }

            return response($e->getMessage(), 500);
        }
    }

    public function getJoinable()
    {
        $auth = Auth::user();

        $channels = [];

        foreach (Channel::all()->sortBy('name') as $channel) {
            if ($channel instanceof Channel) {
                if ($channel->canJoin($auth)) {
                    $chatting = $channel->online->map(function(Online $online) {
                        return $online->login->user->name;
                    });

                    $channel->chatting = count($chatting) > 0 ? $chatting->implode(', ') : '--';
                    $channel->topic = ''; // save bandwidth

                    array_push($channels, $channel);
                }
            }
        }

        return response()->json($channels);
    }

    /**
     * Attempts to delete a message
     *
     * @param Requests\DeleteRequest $request
     * @return ResponseFactory|Response
     * @throws \Exception
     */
    public function postDelete(Requests\DeleteRequest $request)
    {
        $auth = Auth::user();
        $channel = $this->getChannel($request);
        $id = $request->input('id');

        $message = Message::find($id);

        if ($message instanceof Message) {
            if ($message->user_id == $auth->id || $channel->isStaff($auth)) {
                $message->delete();

                return $this->createSystem($channel, 'delete_row', $message);
            }
        }

        return $this->createInfo($channel, 'not_permitted');
    }

    /*
    |--------------------------------------------------------------------------
    | Message helpers
    |--------------------------------------------------------------------------
    |
    | Create message types from the ajax input
    |
    */

    /**
     * Creates a Post object
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createPost(Channel $channel, $message, $color = null)
    {
        $post = new Post([
            'message' => $message,
        ]);

        if ($color > 0) {
            $post->color = $color;
        }

        $post->user()->associate(Auth::user());

        $channel->messages()->save($post);

        $this->closeConnection();

        return response(null, 200);
    }

    /**
     * Creates a Whisper object
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createWhisper(Channel $channel, $message)
    {
        $split = explode(' ', $message);

        $cut = strlen($split[0] . $split[1]) + 2;

        $target = User::findByName($split[1]);
        $message = substr($message, $cut);

        if (!$target instanceof User) {
            return $this->createInfo($channel, 'user_not_found', $split[1]);
        }

        if (!Login::isChatting($target)) {
            return $this->createInfo($channel, 'user_not_online', $target->name);
        }

        $whisper = new Whisper([
            'message' => $message
        ]);

        $whisper->user()->associate(Auth::user());
        $whisper->target()->associate($target);

        $whisper->save();

        $this->closeConnection();

        return response(null, 200);
    }

    private function createSeen(Channel $channel, $message)
    {
        $split = explode(' ', $message);

        $auth = Auth::user();
        $user = User::findByName($split[1]);

        if ($user instanceof User) {
            if ($user == $auth) {
                return $this->createInfo($channel, 'self_target');
            }

            return $this->createInfo($channel, 'user_last_online', [
                'user' => $user->name,
                'seen' => $user->seen,
            ]);
        }

        return $this->createInfo($channel, 'user_not_found', $split[1]);
    }

    /**
     * Creates an Emote object
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createEmote(Channel $channel, $message)
    {
        $split = explode(' ', $message);

        $cut = strlen($split[0]) + 1;

        $message = substr($message, $cut);

        $emote = new Emote([
            'message' => $message
        ]);

        $emote->user()->associate(Auth::user());

        $channel->messages()->save($emote);

        $this->closeConnection();

        return response(null, 200);
    }

    /**
     * Rolls a number of dice and returns the result
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createRoll(Channel $channel, $message) {
        $split = explode(' ', $message);

        $roll = preg_split('/[Dd]/', $split[1]);

        $num = intval($roll[0]);
        $dice = intval($roll[1]);

        if ($num >= 1 && $num <= 20 && $dice >= 2 && $dice <= 99) {
            $result = [];
            $total = 0;

            for ($r = 0; $r < $num; $r++) {
                $roll = rand(1, $dice);

                $total += $roll;
                array_push($result, $roll);
            }

            sort($result);

            return $this->createSystem($channel, 'roll', [
                'user' => Auth::user()->name,
                'roll' => strtolower($split[1]),
                'result' => $result,
                'total' => $total,
            ]);
        }

        return $this->createInfo($channel, 'unknown_command', trim($message));
    }

    /**
     * Creates an Info object
     *
     * @param Channel $channel
     * @param $message
     * @param null $context
     * @return Response
     */
    private function createInfo(Channel $channel = null, $message, $context = null, User $target = null, User $user = null)
    {
        $info = new Info([
            'message' => $message,
            'context' => $context,
        ]);

        $info->target()->associate(is_null($target) ? Auth::user() : $target);

        if ($user instanceof User) {
            $info->user()->associate($user);
        }

        if ($channel instanceof Channel) {
            $channel->messages()->save($info);
        } else {
            $info->save();
        }

        $this->closeConnection();

        return response(null, 200);
    }

    /**
     * Creates a System object
     *
     * @param Channel|null $channel
     * @param $message
     * @param null $context
     * @param User|null $target
     * @return Response
     */
    private function createSystem(Channel $channel = null, $message, $context = null, User $target = null)
    {
        $system = new System([
            'message' => $message,
            'context' => $context,
        ]);

        if ($target instanceof User) {
            $system->target()->associate($target);
        }

        if ($channel instanceof Channel) {
            $channel->messages()->save($system);
        } else {
            $system->save();
        }

        $this->closeConnection();

        return response(null, 200);
    }

    /**
     * Attempts to join a channel
     *
     * @param Channel $origChannel
     * @param $message
     * @return Response|ResponseFactory
     */
    private function createJoin(Channel $origChannel, $message)
    {
        $split = explode(' ', $message);

        $cut = strlen($split[0]) + 1;

        $name = substr($message, $cut);

        if (strlen($name) > config('chat.channels.maxlength')) {
            return $this->createInfo($origChannel, 'channel_name', 1);
        }

        if (strlen($name) < config('chat.channels.minlength')) {
            return $this->createInfo($origChannel, 'channel_name', -1);
        }

        $channel = $this->initChannel($name);

        if (!$channel->canJoin(Auth::user())) {
            return $this->createInfo($origChannel, 'not_permitted', 'join');
        }

        if ($this->enterChannel($channel)) {
            return response()->json([
                'channel' => $channel
            ]);
        }

        return $this->createInfo($origChannel, 'join_error', $name);
    }

    /**
     * Attempts to leave a channel
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     * @throws \Exception
     */
    private function createPart(Channel $channel, $message)
    {
        $login = Login::active(Auth::user());

        if ($login->onlines->count() < 2) {
            return $this->createInfo($channel, 'cannot_leave_channel');
        }

        $split = explode(' ', $message);

        if (isset($split[1])) {
            $cut = strlen($split[0]) + 1;

            $name = substr($message, $cut);

            $channel = Channel::whereName($name)->firstOrFail();
        }

        $this->createSystem($channel, 'part', [
            'user' => Auth::user()->name
        ]);

        Online::where('channel_id', $channel->id)->where('login_id', $login->id)->delete();

        $newChannel = $login->onlines->first(function ($key, $online) use ($channel) {
            return $online->channel->name != $channel->name;
        })->channel;

        return response()->json([
            'channel' => $newChannel,
        ]);
    }

    /**
     * Attempts to invite a user to a channel
     *
     * @param Channel $origChannel
     * @param $message
     * @return Response
     */
    private function createInvite(Channel $origChannel, $message)
    {
        $split = explode(' ', $message);

        $auth = Auth::user();
        $user = User::findByName($split[1]);

        if (isset($split[2])) {
            $channel = Channel::whereName($split[2])->first();

            if (is_null($channel)) {
                return $this->createInfo($origChannel, 'channel_not_found', $split[2]);
            }
        } else {
            $channel = $origChannel;
        }

        if ($channel->access == 'public') {
            if ($user instanceof User) {
                if ($user == $auth) {
                    return $this->createInfo($channel, 'self_target');
                }

                return $this->createInfo(null, 'invite', $channel->name, $user, $auth);
            }

            return $this->createInfo($origChannel, 'user_not_found', $split[1]);
        }

        if ($channel->isStaff($auth)) {

            if ($user instanceof User) {
                if ($user == $auth) {
                    return $this->createInfo($channel, 'self_target');
                }

                if (!Invite::exists($channel, $user)) {
                    $invite = new Invite();

                    $invite->user()->associate($auth);
                    $invite->target()->associate($user);

                    $channel->invites()->save($invite);
                }

                return $this->createInfo(null, 'invite', $channel->name, $user, $auth);
            }

            return $this->createInfo($origChannel, 'user_not_found', $split[1]);
        }

        return $this->createInfo($origChannel, 'not_permitted', 'invite');
    }

    /**
     * Uninvites a user from a channel
     *
     * @param Channel $origChannel
     * @param $message
     * @return Response
     * @throws \Exception
     */
    private function createUninvite(Channel $origChannel, $message)
    {
        $split = explode(' ', $message);

        if (isset($split[2])) {
            $channel = Channel::whereName($split[2])->first();

            if (is_null($channel)) {
                return $this->createInfo($origChannel, 'channel_not_found', $split[2]);
            }
        } else {
            $channel = $origChannel;
        }

        if ($channel->access == 'public') {
            return $this->createInfo($origChannel, 'public_channel');
        }

        $auth = Auth::user();

        if ($channel->isStaff($auth)) {
            $user = User::findByName($split[1]);

            if ($user instanceof User) {
                if ($user == $auth) {
                    return $this->createInfo($channel, 'self_target');
                }

                if (Invite::exists($channel, $user)) {
                    Invite::where('channel_id', $channel->id)->where('target_id', $user->id)->delete();
                }

                $login = Login::active($user);

                if (Online::exists($channel, $login)) {
                    $this->createSystem($channel, 'part', [
                        'user' => $user->name
                    ]);

                    Online::where('channel_id', $channel->id)->where('login_id', $login->id)->delete();
                }

                return $this->createInfo(null, 'uninvite', $channel->name, $user, $auth);
            }

            return $this->createInfo($origChannel, 'user_not_found', $split[1]);
        }

        return $this->createInfo($origChannel, 'not_permitted', 'uninvite');
    }

    /**
     * Returns the user's protegees
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createProtegees(Channel $channel, $message)
    {
        $auth = Auth::user();

        $protegees = $auth->protegees->map(function($user) {
            return $user->name;
        });

        return $this->createInfo($channel, 'protegees', $protegees);
    }

    /**
     * Returns the user's protectors
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createProtectors(Channel $channel, $message)
    {
        $auth = Auth::user();

        $protectors = $auth->protectors->map(function($user) {
            return $user->name;
        });

        return $this->createInfo($channel, 'protectors', $protectors);
    }

    /**
     * Creates a vouch for an existing user
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createVouch(Channel $channel, $message)
    {
        $split = explode(' ', $message);

        $auth = Auth::user();
        $user = User::findByName($split[1]);

        if ($user instanceof User) {
            if ($user == $auth) {
                return $this->createInfo($channel, 'self_target');
            }
        } else {
            return $this->createInfo($channel, 'user_not_found', $split[1]);
        }

        if ($auth->canVouch()) {
            // Look for existing vouches
            $vouch = Vouch::where('email', $user->email)->where('user_id', $auth->id)->first();

            // Check if a vouch already exists
            if ($vouch instanceof Vouch) {
                return $this->createInfo($channel, 'already_vouched', $user->name);
            }

            // Create a new vouch
            $vouch = $auth->vouches()->create([
                'email' => $user->email
            ]);

            FrontLog::info("$auth->name has vouched for $user->name.");

            // Update the protegee's tier...
            $vouch->protegee()->associate($user)->save();

            $user->updateTier();

            $this->createInfo(null, 'new_vouch', $auth->name, $user);

            return $this->createInfo($channel, 'vouched', $user->name);
        } else {
            FrontLog::info("$auth->name attempted to vouch without permission", [
                'User' => $auth->name,
                'Target' => $user->name,
            ]);

            return $this->createInfo($channel, 'not_permitted', 'vouch');
        }
    }

    private function createUnvouch(Channel $channel, $message)
    {
        $split = explode(' ', $message);

        $auth = Auth::user();
        $user = User::findByName($split[1]);

        if ($user instanceof User) {
            if ($user == $auth) {
                return $this->createInfo($channel, 'self_target');
            }
        } else {
            return $this->createInfo($channel, 'user_not_found', $split[1]);
        }

        $vouch = Vouch::where('email', $user->email)->where('user_id', $auth->id)->first();

        // Check if a vouch already exists
        if ($vouch instanceof Vouch) {
            $vouch->delete();

            $user->updateTier();

            FrontLog::notice("$auth->name no longer vouches for $user->name.", [
                'Former protector' => $auth->name,
                'Protegee' => $user->name,
            ]);

            return $this->createInfo($channel, 'vouch_removed', $user->name);
        } else {
            return $this->createInfo($channel, 'vouch_not_found', $user->name);
        }
    }

    /**
     * Promotes a user on the channel
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createPromote(Channel $channel, $message)
    {
        $split = explode(' ', $message);

        $auth = Auth::user();

        if ($channel->isStaff($auth)) {
            $user = User::findByName($split[1]);

            if ($user instanceof User) {
                if ($user == $auth) {
                    return $this->createInfo($channel, 'self_target');
                }

                if ($channel->getRole($auth) == 'admin') {
                    if (Invite::exists($channel, $user)) {
                        $invite = Invite::where('channel_id', $channel->id)->where('target_id', $user->id)->firstOrFail();
                    } else {
                        $invite = new Invite();

                        $invite->user()->associate($auth);
                        $invite->target()->associate($user);

                        $channel->invites()->save($invite);
                    }

                    if ($invite->promote()) {
                        $invite->save();

                        return $this->createSystem($channel, 'promote', [
                            'user' => $auth->name,
                            'target' => $user->name,
                            'role' => $channel->getRole($user),
                        ]);
                    }
                }

                return $this->createInfo($channel, 'not_permitted', 'promote');
            }

            return $this->createInfo($channel, 'user_not_found', $split[1]);
        }

        return $this->createInfo($channel, 'not_permitted', 'promote');
    }

    /**
     * Demotes a user on the channel
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createDemote(Channel $channel, $message)
    {
        $split = explode(' ', $message);

        $auth = Auth::user();

        if ($channel->isStaff($auth)) {
            $user = User::findByName($split[1]);

            if ($user instanceof User) {
                if ($user == $auth) {
                    return $this->createInfo($channel, 'self_target');
                }

                if ($channel->getRole($auth) == 'admin') {
                    if (Invite::exists($channel, $user)) {
                        $invite = Invite::where('channel_id', $channel->id)->where('target_id', $user->id)->firstOrFail();
                    } else {
                        $invite = new Invite();

                        $invite->user()->associate($auth);
                        $invite->target()->associate($user);

                        $channel->invites()->save($invite);
                    }

                    if ($invite->demote()) {
                        $invite->save();

                        return $this->createSystem($channel, 'demote', [
                            'user' => $auth->name,
                            'target' => $user->name,
                            'role' => $channel->getRole($user),
                        ]);
                    }
                }

                return $this->createInfo($channel, 'not_permitted', 'demote');
            }

            return $this->createInfo($channel, 'user_not_found', $split[1]);
        }

        return $this->createInfo($channel, 'not_permitted', 'promote');
    }

    /**
     * Sets the channel topic
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createTopic(Channel $channel, $message)
    {
        if ($channel->isStaff(Auth::user())) {
            $split = explode(' ', $message);

            $cut = strlen($split[0]) + 1;

            $message = substr($message, $cut);

            $channel->topic = $message;
            $channel->save();

            return $this->createSystem($channel, 'topic', [
                'user' => Auth::user()->name,
                'topic' => $message,
            ]);
        }

        return $this->createInfo($channel, 'not_permitted', 'topic');
    }

    /**
     * Change the channel settings
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createSet(Channel $channel, $message)
    {
        if ($channel->isStaff(Auth::user())) {
            $split = explode(' ', $message);

            $cut = strlen($split[0]) + strlen($split[1]) + 2;

            $command = $split[1];
            $opt = substr($message, $cut);

            $allow = [
                'access' => ['public', 'private'],
                'persist' => ['on', 'off'],
            ];

            if (!isset($allow[$command]) || !in_array($opt, $allow[$command]) && $allow[$command] != ['*']) {
                return $this->createInfo($channel, 'unknown_command', trim($message));
            }

            if ($command == 'access') {
                $channel->access = $opt;

                $channel->save();
            }

            if ($command == 'persist') {
                if (Auth::user()->isStaff()) {
                    if ($opt == 'on') {
                        $channel->expires = null;

                        $channel->save();
                    } else {
                        if (is_null($channel->expires)) {
                            $channel->touchExpires(true);
                        } else {
                            return $this->createInfo($channel, 'expires', $channel->expires);
                        }
                    }
                } else {
                    return $this->createInfo($channel, 'not_permitted', 'setting');
                }
            }

            return $this->createSystem($channel, 'settings', [
                'user' => Auth::user()->name,
                'command' => $command,
                'option' => $opt,
            ]);
        }

        return $this->createInfo($channel, 'not_permitted', 'settings');
    }

    /**
     * Displays the channel information
     *
     * @param Channel $origChannel
     * @param $message
     * @return Response
     */
    private function createAbout(Channel $origChannel, $message)
    {
        if (Auth::user()->isStaff()) {
            $split = explode(' ', $message);

            if (isset($split[1])) {
                $channel = Channel::whereName($split[1])->first();

                if (is_null($channel)) {
                    return $this->createInfo($origChannel, 'channel_not_found', $split[2]);
                }
            } else {
                $channel = $origChannel;
            }

            return $this->createInfo($origChannel, 'about', [
                'name' => $channel->name,
                'user' => $channel->user->name,
                'access' => $channel->access,
                'expires' => $channel->expires,
            ]);
        }

        return $this->createInfo($origChannel, 'not_permitted', 'about');
    }

    /**
     * Ignores a user
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createIgnore(Channel $channel, $message)
    {
        $split = explode(' ', $message);

        $name = $split[1];

        $user = User::findByName($name);

        if ($user instanceof User) {
            if ($user == Auth::user()) {
                return $this->createInfo($channel, 'self_target');
            }

            if (Ignore::exists(Auth::user(), $user)) {
                return $this->createInfo($channel, 'ignore_exists', [
                    'user' => $user->name,
                ]);
            }

            $ignore = new Ignore();

            $ignore->user()->associate(Auth::user());
            $ignore->target()->associate($user);

            $ignore->save();

            return $this->createInfo($channel, 'ignored', [
                'user' => $user->name,
            ]);
        }

        return $this->createInfo($channel, 'user_not_found', $name);
    }

    /**
     * Unignores a user
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createUnignore(Channel $channel, $message)
    {
        $split = explode(' ', $message);

        $name = $split[1];

        $user = User::findByName($name);

        if ($user instanceof User) {
            if ($user == Auth::user()) {
                return $this->createInfo($channel, 'self_target');
            }

            if (!Ignore::exists(Auth::user(), $user)) {
                return $this->createInfo($channel, 'ignore_not_found', [
                    'user' => $user->name,
                ]);
            }

            Ignore::remove(Auth::user(), $user);

            return $this->createInfo($channel, 'unignored', [
                'user' => $user->name,
            ]);
        }

        return $this->createInfo($channel, 'user_not_found', $name);
    }

    /**
     * Bans a user
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createBan(Channel $channel, $message)
    {
        if (!Auth::user()->isStaff()) {
            return $this->createInfo($channel, 'not_permitted', 'ban');
        }

        $split = explode(' ', $message);

        $name = $split[1];

        $user = User::findByName($name);

        if ($user instanceof User) {
            $auth = Auth::user();

            if ($user == Auth::user()) {
                return $this->createInfo($channel, 'self_target');
            }

            if ($user->roleNum > $auth->roleNum) {
                FrontLog::warning("$auth->name attempted to ban someone above their level.", [
                    'Banning user' => $auth->name,
                    'Banner role' => $auth->role,
                    'Target user' => $user->name,
                ]);

                return $this->createInfo($channel, 'not_permitted', 'ban');
            }

            if (!is_null($ban = Ban::getExisting($user))) {
                return $this->createInfo($channel, 'ban_exists', [
                    'user' => $user->name,
                    'until' => $ban->until,
                ]);
            }

            $duration = config('chat.ban.default.duration');
            $units = config('chat.ban.default.unit');

            $expires = Ban::createTimestamp($duration, $units);

            $user->bans()->save(new Ban([
                'expires' => $expires
            ]))->save();

            $login = Login::active($user);

            if (!is_null($login)) {
                $login->onlines()->getQuery()->delete();
            }

            FrontLog::notice("$user->name has been banned for $duration $units.", [
                'Banning user' => $auth->name,
                'Banner role' => $auth->role,
                'Target user' => $user->name,
                'Ban duration' => "$duration $units",
                'Ban expires' => $expires->toDayDateTimeString(),
            ]);

            $this->createSystem(null, 'banned', [
                'user' => $auth->name,
                'target' => $user->name,
            ]);

            $this->createSystem(null, 'force_logout', null, $user);

            return $this->createInfo($channel, 'banned', [
                'user' => $user->name,
                'duration' => "$duration $units",
            ]);
        }

        return $this->createInfo($channel, 'user_not_found', $name);
    }

    /**
     * Revokes a user's ban
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createUnban(Channel $channel, $message)
    {
        if (!Auth::user()->isStaff()) {
            return $this->createInfo($channel, 'not_permitted', 'ban');
        }

        $split = explode(' ', $message);

        $name = $split[1];

        $user = User::findByName($name);

        if ($user instanceof User) {
            $auth = Auth::user();

            if ($user == Auth::user()) {
                return $this->createInfo($channel, 'self_target');
            }

            if ($user->roleNum > $auth->roleNum) {
                FrontLog::error("$auth->name attempted to unban without permission.", [
                    'Revoking user' => $auth->name,
                    'Revoker role' => $auth->role,
                    'Target user' => $user->name,
                ]);

                return $this->createInfo($channel, 'not_permitted', 'unban');
            }

            Ban::target($user)->delete();

            FrontLog::notice("$user->name's ban has been revoked.", [
                'Revoking user' => $auth->name,
                'Revoker role' => $auth->role,
                'Target user' => $user->name,
            ]);

            $this->createSystem(null, 'unbanned', [
                'user' => $auth->name,
                'target' => $user->name,
            ]);

            return $this->createInfo($channel, 'unbanned', [
                'user' => $user->name,
            ]);
        }

        return $this->createInfo($channel, 'user_not_found', $name);
    }

    /**
     * Kicks a user from the channel
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createKick(Channel $channel, $message)
    {
        if (!$channel->isStaff(Auth::user())) {
            return $this->createInfo($channel, 'not_permitted', 'kick');
        }

        $split = explode(' ', $message);

        $name = $split[1];

        $user = User::findByName($name);

        if ($user instanceof User) {
            $login = Login::active($user);

            if ($login instanceof Login) {
                if (!$login->channels->contains($channel)) {
                    return $this->createInfo($channel, 'user_not_found', $name);
                }

                Online::where('channel_id', $channel->id)->where('login_id', $login->id)->delete();

                $this->createSystem(null, 'kick', [
                    'channel' => $channel->name,
                ], $user);

                return $this->createSystem($channel, 'kicked', [
                    'user' => Auth::user()->name,
                    'target' => $user->name,
                ]);
            }

            return $this->createInfo($channel, 'user_not_found', $name);
        }

        return $this->createInfo($channel, 'user_not_found', $name);
    }

    /**
     * Attempts to read the IP of a person
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createWhois(Channel $channel, $message)
    {
        if (!Auth::user()->isStaff()) {
            return $this->createInfo($channel, 'not_permitted', 'whois');
        }

        $split = explode(' ', $message);

        $name = $split[1];

        $user = User::findByName($name);

        if ($user instanceof User) {
            $login = Login::active($user);

            if ($login instanceof Login) {
                return $this->createInfo($channel, 'whois', [
                    'user' => $user->name,
                    'ip' => $login->ip,
                ]);
            }

            return $this->createInfo($channel, 'user_not_found', $name);
        }

        return $this->createInfo($channel, 'user_not_found', $name);
    }

    /**
     * Returns the channels a user is on
     *
     * @param Channel $channel
     * @param $message
     * @return Response
     */
    private function createFind(Channel $channel, $message) {
        $split = explode(' ', $message);

        $name = $split[1];

        $user = User::findByName($name);

        if ($user instanceof User) {
            $login = Login::active($user);

            if ($login instanceof Login) {
                $isStaff = Auth::user()->isStaff();

                $channels = [];

                foreach ($login->channels as $c) {
                    if ($c->isPublic() || $isStaff) {
                        array_push($channels, $c->name);
                    }
                }

                return $this->createInfo($channel, 'find', [
                    'user' => $user->name,
                    'channels' => $channels,
                ]);
            }

            return $this->createInfo($channel, 'user_not_found', $name);
        }

        return $this->createInfo($channel, 'user_not_found', $name);

    }

    /**
     * Logs a user out
     *
     * @param Channel $channel
     * @param $message
     * @return ResponseFactory|Response
     */
    private function createLogout(Channel $channel, $message)
    {
        return $this->getLogout();
    }

    /**
     * Sets the user status to afk
     *
     * @param Channel $channel
     * @param $message
     * @return null|Response
     */
    private function createAfk(Channel $channel, $message)
    {
        return $this->setStatus($channel, 'afk');
    }

    /**
     * Sets the user status to brb
     *
     * @param Channel $channel
     * @param $message
     * @return null|Response
     */
    private function createBrb(Channel $channel, $message)
    {
        return $this->setStatus($channel, 'brb');
    }

    /**
     * Sets the user status to gaming
     *
     * @param Channel $channel
     * @param $message
     * @return null|Response
     */
    private function createGaming(Channel $channel, $message)
    {
        return $this->setStatus($channel, 'gaming');
    }

    /**
     * Sets the user status to working
     *
     * @param Channel $channel
     * @param $message
     * @return null|Response
     */
    private function createWorking(Channel $channel, $message)
    {
        return $this->setStatus($channel, 'working');
    }

    /**
     * Sets the user status to art
     *
     * @param Channel $channel
     * @param $message
     * @return null|Response
     */
    private function createArt(Channel $channel, $message)
    {
        return $this->setStatus($channel, 'art');
    }

    /**
     * Sets the user status to phone
     *
     * @param Channel $channel
     * @param $message
     * @return null|Response
     */
    private function createPhone(Channel $channel, $message)
    {
        return $this->setStatus($channel, 'phone');
    }

    /**
     * Sets the user status to online
     *
     * @param Channel $channel
     * @param $message
     * @return null|Response
     */
    private function createBack(Channel $channel, $message)
    {
        return $this->setStatus($channel, 'online');
    }

    /*
     |--------------------------------------------------------------------------
     | Actions
     |--------------------------------------------------------------------------
     |
     | Chat actions
     |
     */

    /**
     * Initializes a channel
     *
     * @param $name
     * @return Channel
     */
    private function initChannel($name)
    {
        $name = trim($name);

        if (!preg_match("/^$this->channelRegex$/", $name)) {
            throw new \Exception("Invalid channel name [$name].");
        }

        $channel = Channel::whereName($name)->withTrashed()->first();

        if (!$channel instanceof Channel) {
            $channel = new Channel([
                'name' => $name,
                'access' => config('chat.channels.access'),
                'expires' => Carbon::now()->addDays(config('chat.channels.expire')),
            ]);

            Auth::user()->channels()->save($channel);
        }

        if ($channel->trashed()) {
            $channel->restore(Auth::user());
        } else {
            $channel->touchExpires();
        }

        return $channel;
    }

    /**
     * Adds the user to a channel
     *
     * @param Channel $channel
     * @return bool
     */
    private function enterChannel(Channel $channel)
    {
        $user = Auth::user();

        if (!is_null($login = Login::active($user))) {
            if (Online::exists($channel, $login)) {
                return false;
            }

            try {
                DB::beginTransaction();

                $online = new Online();

                $online->login()->associate($login);
                $online->channel()->associate($channel);

                $online->save();

                DB::commit();

                $this->createSystem($channel, 'join', [
                    'user' => Auth::user()->name,
                ]);

                if (!empty($channel->topic)) {
                    $this->createSystem($channel, 'current_topic', $channel->topic, Auth::user());
                }
            } catch (\Exception $e) {
                DB::rollBack();

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Sets the user status
     *
     * @param Channel $channel
     * @param $status
     * @return Response|null
     */
    private function setStatus(Channel $channel, $status)
    {
        $auth = Auth::user();
        $login = Login::active($auth);

        if (!$login->hasStatus($status)) {
            $oldStatus = $login->getStatus();

            $login->setStatus($status);

            foreach ($login->channels as $c) {
                $this->createSystem($c, 'status', [
                    'user' => $auth->name,
                    'status' => $status,
                    'old_status' => $oldStatus,
                ]);
            }

            return null;
        }

        return $this->createInfo($channel, 'status_exists', $status);
    }

    /*
    |--------------------------------------------------------------------------
    | Ajax helpers
    |--------------------------------------------------------------------------
    |
    | Various ajax helpers
    |
    */

    /**
     * Returns the sanitized user input
     *
     * @param Request $request
     * @return string
     */
    private function getMessage(Request $request)
    {
        $message = $request->input('message');

        $message = trim($message);
        $message = htmlspecialchars($message);

        return substr($message, 0, config('chat.input.maxLength')); // Enforce the length restriction
    }

    /**
     * Returns the channel from the request
     *
     * @param Request $request
     * @return Channel
     */
    private function getChannel(Request $request)
    {
        $channel = $request->input('channel');

        return Channel::whereName($channel['name'])->firstOrFail();
    }

    /**
     * Returns the channel list from the request
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    private function getChannels(Request $request)
    {
        return collect($request->input('channels'));
    }

    /**
     * Returns the latest message id
     *
     * @param Request $request
     * @return int
     */
    private function getLatest(Request $request)
    {
        $channels = $this->getChannels($request);

        if (!$channels->isEmpty()) {
            $channel = $request->input('channel');

            $item = $channels->where('name', $channel['name'])->first();

            if (!is_null($item) && isset($item['latest'])) {
                if ($item['latest'] > 0) {
                    return $item['latest'];
                }
            }
        }

        return 0;
    }

    /**
     * Closes the database connection
     */
    private function closeConnection()
    {
        DB::disconnect('mysql');
    }
}
