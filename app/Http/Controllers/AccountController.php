<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Login;
use App\Role;
use App\Settings;
use Illuminate\Http\Request;

use App\Http\Requests;
use CMS;
use Auth;
use App\User;
use Illuminate\Routing\Redirector;
use Mail;
use App\Vouch;
use DB;
use FrontLog;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'only' => [
                'getSettings',
                'postSettings',
                'getRoles',
                'postRoles',
                'getLogout',
                'postSave',
                'postInvite',
                'postUninvite',
                'getEmail',
            ]
        ]);
    }

    /**
     * Serves the account front page
     * Either a login page, the account config, or a thank you message
     *
     * @return mixed
     */
    public function getIndex()
    {
        if (!is_null($user = Auth::user())) {
            if (!$user->is_active) {
                $page = 'thank_you';
            } else if ($user->isBanned()) {
                $page = 'banned';
            } else {
                $page = 'home';
            }
        } else {
            $page = 'login';
        }

        return CMS::render("account.$page", [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Logs in to the account
     *
     * @param Requests\LoginRequest $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postIndex(Requests\LoginRequest $request)
    {
        if (Auth::check()) {
            return redirect()->back();
        }

        $name = $request->input('name');
        $password = $request->input('password');

        if (Auth::attempt(['name' => $name, 'password' => $password])) {
            if (!config('chat.allowLogins')) {
                if (Auth::id() != 1) {
                    Auth::logout();

                    return redirect()->back();
                }
            }

            Login::trace();

            return redirect()->back();
        }

        return redirect()->back()->withErrors([
            'login' => 'Invalid username or password.',
        ]);
    }

    /**
     * Displays the user chat settings
     *
     * @return bool|string
     */
    public function getSettings()
    {
        $auth = Auth::user();

        $settings = Settings::user($auth);

        $themes = collect(\File::directories(public_path('css')))->map(function($path) {
            return basename($path);
        });

        return CMS::render("account.settings", [
            'settings' => $settings,
            'timezones' => \DateTimeZone::listIdentifiers(\DateTimeZone::ALL),
            'themes' => $themes,
        ]);
    }

    /**
     * Stores the user chat settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSettings(Request $request)
    {
        $this->validate($request, [
            'maxMessages' => 'numeric',
            'timezone' => 'required',
            'theme' => 'required',
        ]);

        $channels = $request->input('channels');
        $highlight = $request->input('highlight');
        $notify = [
            'mentions' => $request->input('notify_mentions'),
            'invites' => $request->input('notify_invites'),
            'channel' => $request->input('notify_channel'),
        ];
        $maxMessages = $request->input('maxMessages');
        $interval = $request->input('interval');
        $timezone = $request->input('timezone');
        $theme = $request->input('theme');

        $auth = Auth::user();

        $settings = Settings::user($auth);

        $settings->channels = trim($channels);
        $settings->highlight = trim($highlight);
        $settings->notify = $notify;
        $settings->maxMessages = trim($maxMessages);
        $settings->interval = $interval >= config('chat.interval.minimum') ? trim($interval) : null;
        $settings->timezone = $timezone;
        $settings->theme = $theme;

        $auth->settings()->save($settings);

        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => "Your chat settings have been saved!",
            ]
        ]);
    }

    public function getRoles()
    {
        return CMS::render('account.roles', [
            'currentRole' => Auth::user()->publicRole,
            'roles' => Auth::user()->publicRoles,
        ]);
    }

    public function postRole(Request $request)
    {
        $user = Auth::user();

        if (empty($request->input('role'))) {
            $user->publicRole = null;
            $user->save();

            return redirect()->back()->with([
                'alert' => [
                    'type' => 'success',
                    'message' => "Public role removed.",
                ]
            ]);
        }

        $role = Role::find($request->input('role'));

        if ($role instanceof Role) {
            if ($user->hasPublicRole($role)) {
                $user->publicRole = $role;
                $user->save();

                return redirect()->back()->with([
                    'alert' => [
                        'type' => 'success',
                        'message' => "You now publicly belong to $role->title.",
                    ]
                ]);
            }

            return redirect()->back()->with([
                'alert' => [
                    'type' => 'danger',
                    'message' => "You do not have access to $role->title.",
                ]
            ]);
        }

        return redirect()->back()->with([
            'alert' => [
                'type' => 'danger',
                'message' => "The role was not found.",
            ]
        ]);
    }

    /**
     * Logs out the user
     *
     * @return \Illuminate\Http\RedirectResponse|Redirector
     */
    public function getLogout()
    {
        Login::logout();

        return redirect('account');
    }

    /**
     * Saves the account config
     *
     * @param Requests\AccountRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSave(Requests\AccountRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $email = $request->input('email');
            
            // Log changes
            if ($user->name != $request->input('name')) {
                $newName = $request->input('name');

                FrontLog::info("$user->name has changed their username to $newName.");
            }

            if ($user->email != $request->input('email')) {
                // Update dependencies
                Vouch::where('email', $user->email)->update([
                    'email' => $email
                ]);
                
                FrontLog::info("$user->name has changed their email.", [
                    'Old email' => $user->email,
                    'New email' => $request->input('email'),
                ]);
            }

            // Update user info
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));

            $user->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => "Your account information has been saved!",
            ]
        ]);
    }

    /**
     * Sends an invite email to a friend
     * and creates a voucher record
     *
     * @param Requests\InviteRequest $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postInvite(Requests\InviteRequest $request)
    {
        $protector = Auth::user();
        $email = $request->input('friend_email');

        if (!$protector->canVouch()) {
            FrontLog::error("$protector->name attempted to vouch without permission", [
                'User' => $protector->name,
                'Email' => $email,
            ]);

            return redirect()->back();
        }

        if ($protector->email == $email) {
            return redirect()->back()->withErrors([
                'friend_email' => 'You cannot vouch for yourself!',
            ]);
        }

        // Look for existing vouches
        $vouch = Vouch::where('email', $email)->where('user_id', $protector->id)->first();

        // Check if a vouch already exists
        if ($vouch instanceof Vouch) {
            $protegee = is_null($vouch->protegee) ? $email : $vouch->protegee->name;

            if ($vouch->protegee instanceof User) {
                return redirect()->back()->with([
                    'alert_friend' => [
                        'type' => 'info',
                        'message' => "You have already vouched for $protegee!",
                    ]
                ]);
            }

            // Resent the email if needed
            $this->sendInvitation($email, $protector);

            return redirect()->back()->with([
                'alert_friend' => [
                    'type' => 'success',
                    'message' => "Your invite to $protegee has been resent!",
                ]
            ]);
        }

        // Create a new vouch
        $vouch = $protector->vouches()->create([
            'email' => $email
        ]);

        // Look for existing users
        $existingUser = User::where('email', $email)->first();

        // Log the vouch
        $protegee = is_null($existingUser) ? $email : "$email ($existingUser->name)";

        FrontLog::info("$protector->name has vouched for $protegee.");

        if ($existingUser instanceof User) {
            // Update the protegee's tier...
            $vouch->protegee()->associate($existingUser)->save();

            $existingUser->updateTier();

            return redirect()->back()->with([
                'alert_friend' => [
                    'type' => 'success',
                    'message' => "You successfully vouched for $existingUser->name!",
                ]
            ]);
        } else {
            // ...or send an invitation email
            $this->sendInvitation($email, $protector);

            return redirect()->back()->with([
                'alert_friend' => [
                    'type' => 'success',
                    'message' => "An invite has been successfully sent to $email!",
                ]
            ]);
        }
    }

    /**
     * Removes a voucher
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUninvite(Request $request)
    {
        $id = $request->input('vouch_id');

        $vouch = Vouch::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $vouch->delete();

        if (!is_null($vouch->protegee)) {
            $vouch->protegee->updateTier();
        }

        $protector = Auth::user()->name;
        $protegee = is_null($vouch->protegee) ? $vouch->email : $vouch->protegee->name;

        FrontLog::notice("$protector no longer vouches for $protegee.", [
            'Former protector' => $protector,
            'Protegee' => $protegee,
        ]);

        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => "You no longer vouch for $protegee.",
            ]
        ]);
    }

    /**
     * Displays an account creation form
     *
     * @param Request $request
     * @return bool|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|Redirector|\Illuminate\View\View|string
     */
    public function getCreate(Request $request)
    {
        if (Auth::check()) {
            $auth = Auth::user();
            $id = $request->input('id');
            $token = $request->input('token');

            FrontLog::alert("$auth->name tried to create a duplicate account.", [
                'Vouch id' => $id,
                'Vouch token' => $token,
            ]);

            return redirect('account');
        }

        $verify = $this->verifyEmail($request);

        if ($verify instanceof Redirector) {
            return $verify;
        }

        if ($verify == true) {
            $id = $request->input('id');
            $token = $request->input('token');

            $data = base64_decode($id);
            $split = explode('#', $data);
            $email = $split[0];

            if (User::where('email', $email)->count() > 0
                || Vouch::where('email', $email)->whereNotNull('protegee_id')->count() > 0
            ) {
                return redirect('account');
            }

            return CMS::render('account.create', [
                'id' => $id,
                'token' => $token,
            ]);

        }

        return view_error(404);
    }

    /**
     * Creates a new account
     *
     * @param Requests\RegisterRequest $request
     * @return bool|\Illuminate\Http\RedirectResponse|Redirector
     * @throws \Exception
     */
    public function postCreate(Requests\RegisterRequest $request)
    {
        $verify = $this->verifyEmail($request);

        if ($verify instanceof Redirector) {
            return $verify;
        }

        if ($verify == true) {
            $id = $request->input('id');

            $data = base64_decode($id);
            $split = explode('#', $data);

            $email = $split[0];

            try {
                DB::beginTransaction();

                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $email,
                    'password' => bcrypt($request->input('password')),
                    'role' => 'member',
                    'public_key' => uniqid(),
                    'private_key' => bin2hex(random_bytes(15)),
                ]);

                Vouch::where('email', $email)->update([
                    'protegee_id' => $user->id,
                ]);

                $user->updateTier(false, false);

                $this->sendWelcome($user, $request->input('password'));

                Auth::loginUsingId($user->id);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw $e;
            }

        }

        return redirect('account');
    }

    /**
     * Used to test that emails arrive to the user
     */
    public function getEmail() {
        $this->sendTestEmail(Auth::user());

        return "An email was sent!";
    }

    /**
     * Returns bool or redirects an existing user
     *
     * @param Request $request
     * @return bool|Redirector
     */
    private function verifyEmail(Request $request)
    {
        $id = $request->input('id');
        $token = $request->input('token');

        $data = base64_decode($id);

        $split = explode('#', $data);

        try {
            $email = $split[0];
            $protector_id = $split[1];
            $public_key = $split[2];
        } catch (\ErrorException $e) {
            FrontLog::critical('Invalid vouch email data', $split);

            return false;
        }

        if (!is_null($vouch = Vouch::where('email', $email)->where('user_id', $protector_id)->firstOrFail())) {
            if (!is_null($vouch->protegee_id)) {
                return redirect('account');
            }

            if (!$vouch->protector->canVouch()) {
                return false;
            }

            $private_key = User::where('public_key', $public_key)->firstOrFail()->private_key;

            $secureToken = base64_encode(hash_hmac('md5', $email, $private_key));

            if ($token == $secureToken) {
                return true;
            }
        }

        FrontLog::alert('Invalid vouch email security token.', [
            'Id' => $id,
            'Token' => $token,
            'Data' => $split,
        ]);

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    |
    | Email sender methods
    |
    */

    private function sendInvitation($email, User $protector)
    {
        FrontLog::info("Sent invitation email to $email.");

        $public_key = $protector->public_key;
        $private_key = $protector->private_key;

        $id = base64_encode($email . "#" . $protector->id . "#" . $public_key);
        $token = base64_encode(hash_hmac('md5', $email, $private_key));

        $url = url("account/create/?id=$id&token=$token");

        Mail::send('emails.invite', [
            'user' => $protector->name,
            'app' => config('chat.name'),
            'link' => $url,
        ], function ($m) use ($email, $protector) {
            $m->subject($protector->name . ' has invited you to join ' . config('chat.name') . '!');

            $m->to($email);
        });
    }

    private function sendWelcome(User $user, $password)
    {
        FrontLog::info("Sent welcome email to $user->email.");

        Mail::send('emails.welcome', [
            'user' => $user->name,
            'password' => $password,
            'app' => config('chat.name'),
            'inactive' => !$user->is_active,
        ], function ($m) use ($user) {
            $m->subject('Thank you for registering to  ' . config('chat.name') . '!');

            $m->to($user->email);
        });
    }

    private function sendTestEmail(User $user)
    {
        try {
            Mail::raw("This is the test email you requested. Yay!", function ($m) use ($user) {
                $m->subject('This is a test email!');

                $m->to($user->email);
            });
        } catch (Exception $e) {
            dd($e);
        }
    }
}
