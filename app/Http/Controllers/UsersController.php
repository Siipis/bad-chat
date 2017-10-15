<?php

namespace App\Http\Controllers;

use App\Ban;
use App\Role;
use App\User;
use App\Vouch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use CMS;
use Auth;
use FrontLog;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('access:view.users', [
            'only' => [
                'getIndex',
                'getView',
            ]
        ]);

        $this->middleware('access:control.discouragement', [
            'only' => [
                'postDiscouragement',
            ]
        ]);

        $this->middleware('access:control.registration', [
            'only' => [
                'getPending',
                'postActivate',
                'postDelete'
            ]
        ]);

        $this->middleware('access:control.bans', [
            'only' => [
                'getBanned',
                'postBan',
            ]
        ]);

        $this->middleware('access:control.users', [
            'only' => [
                'postRole',
                'postRetier',
            ]
        ]);

        $this->middleware('access:messaging', [
            'only' => [
                'getMessage',
                'postMessage',
            ]
        ]);
    }

    public function getIndex()
    {
        return CMS::render('users.main', [
            'users' => User::active()->where('id', '!=', '1')->orderBy('name')->get(),
        ]);
    }

    public function getView($name)
    {
        return CMS::render('users.view', [
            'user' => User::findByNameOrFail($name),
        ]);
    }

    public function postRole(Request $request)
    {
        $this->validate($request, [
            'action' => 'required|in:promote,demote',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        $auth = Auth::user();
        $action = $request->input('action');

        if ($action == 'promote') {
            if ($user->promote($auth)) {
                FrontLog::notice("$user->name has been promoted by $auth->name.", [
                    'Promoting user' => $auth->name,
                    'Promoter role' => $auth->role,
                    'Target user' => $user->name,
                ]);

                return redirect()->back()->with([
                    'alert' => [
                        'type' => 'success',
                        'message' => "$user->name has been successfully promoted!",
                    ]
                ]);
            } else {
                FrontLog::error("$auth->name attempted to promote beyond the allowed level.", [
                    'Promoting user' => $auth->name,
                    'Promoter role' => $auth->role,
                    'Target user' => $user->name,
                ]);

                return redirect()->back()->with([
                    'alert' => [
                        'type' => 'danger',
                        'message' => "$user->name cannot be promoted any higher!",
                    ]
                ]);
            }
        } else if ($action == 'demote') {
            if ($user->demote($auth)) {
                FrontLog::notice("$user->name has been demoted by $auth->name.", [
                    'Demoting user' => $auth->name,
                    'Demoter role' => $auth->role,
                    'Target user' => $user->name,
                ]);

                return redirect()->back()->with([
                    'alert' => [
                        'type' => 'warning',
                        'message' => "$user->name has been successfully demoted!",
                    ]
                ]);
            } else {
                FrontLog::error("$auth->name attempted to demote under the allowed level.", [
                    'Demoting user' => $auth->name,
                    'Demoter role' => $auth->role,
                    'Target user' => $user->name,
                ]);

                return redirect()->back()->with([
                    'alert' => [
                        'type' => 'danger',
                        'message' => "$user->name cannot be demoted any lower!",
                    ]
                ]);
            }
        } else {
            throw new \Exception("Fatal error: Unknown action [$action].");
        }
    }

    public function postDiscouragement(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        $user->discouraged = !$user->discouraged;
        $user->save();

        return redirect()->back();
    }

    public function getMessage()
    {
        return CMS::render('users.message');
    }

    public function postMessage(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required',
        ]);

        $auth = Auth::user();
        $title = $request->input('title');
        $message = $request->input('message');

        try {
            foreach (User::active()->where('id', '!=', '1')->get() as $user) {
                $user->sendEmail($title, $message);
            }

            FrontLog::notice("$auth->name has emailed all members: $title.", $message);

            return redirect()->back()->with([
                'alert' => [
                    'type' => 'success',
                    'message' => 'Your email was successfully sent!',
                ]
            ]);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return redirect()->back()->with([
                'alert' => [
                    'type' => 'danger',
                    'message' => 'An error occurred! Please try again later.',
                ]
            ]);
        }
    }

    public function getPending()
    {
        return CMS::render('users.pending', [
            'users' => User::inactive()->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function postActivate(Request $request)
    {
        $user = User::findOrFail($request->input('user_id'));

        $user->activate();

        FrontLog::info("User $user->name has been activated.", [
            'Acceptor' => Auth::user()->name,
            'Target user' => $user->name,
        ]);

        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => "User $user->name has been activated!",
            ]
        ]);
    }

    public function postDelete(Request $request)
    {
        $user = User::findOrFail($request->input('user_id'));

        if (!$user->is_active) {
            $user->delete();

            FrontLog::notice("Pending user $user->name has been removed.", [
                'Remover' => Auth::user()->name,
                'Target user' => $user->name,
            ]);

            return redirect()->back()->with([
                'alert' => [
                    'type' => 'info',
                    'message' => "User $user->name has been removed!",
                ]
            ]);
        }

        FrontLog::warning("$user->name tried to remove an active account.", [
            'Remover' => Auth::user()->name,
            'Target user' => $user->name,
        ]);

        abort(403);
        return null;
    }

    public function getBanned()
    {
        $role = Auth::user()->roleNum;

        return CMS::render('users.banned', [
            'bans' => Ban::active()->withTrashed()->orderBy('created_at', 'desc')->get(),
            'users' => User::where('role', '<', $role)->orderBy('name')->get(),
            'ban' => config('chat.ban'),
        ]);
    }

    public function postBan(Requests\BanRequest $request)
    {
        $user = User::findByNameOrFail($request->input('name'));
        $auth = Auth::user();

        if ($user->roleNum > $auth->roleNum) {
            FrontLog::warning("$auth->name attempted to ban someone above their level.", [
                'Banning user' => $auth->name,
                'Banner role' => $auth->role,
                'Target user' => $user->name,
            ]);

            return redirect()->back()->with([
                'alert_ban' => [
                    'type' => 'danger',
                    'message' => "You don't have permission to ban $user->name!",
                ]
            ]);
        }

        $duration = intval($request->input('duration'));
        $units = $request->input('units');
        $revoke_vouches = $request->input('revoke_vouches');

        if ($user instanceof User) {
            if (!is_null($ban = Ban::getExisting($user))) {
                return redirect()->back()->with([
                    'alert_ban' => [
                        'type' => 'info',
                        'message' => "$user->name has already been banned until $ban->until.",
                    ]
                ]);
            }

            if($revoke_vouches == 'true') {
                Vouch::where('email', $user->email)->delete();

                $user->updateTier();
            }

            $expires = Ban::createTimestamp($duration, $units);

            $user->bans()->save(new Ban([
                'expires' => $expires
            ]))->save();

            FrontLog::notice("$user->name has been banned for $duration $units.", [
                'Banning user' => $auth->name,
                'Banner role' => $auth->role,
                'Target user' => $user->name,
                'Ban duration' => "$duration $units",
                'Ban expires' => $expires->toDayDateTimeString(),
            ]);
        }

        return redirect()->back()->with([
            'alert_' => [
                'type' => 'success',
                'message' => "$user->name has successfully been banned for $duration $units!",
            ]
        ]);
    }

    public function postUnban(Request $request)
    {
        $auth = Auth::user();

        $this->validate($request, [
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if ($user->roleNum > $auth->roleNum) {
            FrontLog::error("$auth->name attempted to unban without permission.", [
                'Revoking user' => $auth->name,
                'Revoker role' => $auth->role,
                'Target user' => $user->name,
            ]);

            return redirect()->back()->with([
                'alert' => [
                    'type' => 'danger',
                    'message' => "You don't have permission to unban $user->name!",
                ]
            ]);
        }

        Ban::target($user)->delete();

        FrontLog::notice("$user->name's ban has been revoked.", [
            'Revoking user' => $auth->name,
            'Revoker role' => $auth->role,
            'Target user' => $user->name,
        ]);

        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => "$user->name has been unbanned!",
            ]
        ]);
    }

    public function postRetier()
    {
        $root = User::find(1);

        foreach($root->protegees as $protegee) {
            $protegee->updateTier(true);
        }

        FrontLog::info('User tiers have been reloaded.');

        return redirect()->back();
    }

    public function getVouched()
    {
        if (!\Auth::check()) {
            return redirect('account');
        }

        return CMS::render('users.vouched', [
            'vouches' => \Auth::user()->vouches
        ]);
    }

    public function getPatrons()
    {
        return CMS::render('users.patrons', [
            'users' => Role::find(1)->users->sortBy('name')
        ]);
    }
}
