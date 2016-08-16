<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use CMS;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('access:control.roles');
    }

    public function getIndex()
    {
        return CMS::render('admin.roles.main', [
            'roles' => Role::all()->sortBy('title'),
        ]);
    }

    public function postCreate(Requests\RoleRequest $request)
    {
        $role = new Role();
        $role->title = $request->input('title');
        $role->icon =$request->input('icon');

        $role->save();

        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => "$role->title was successfully created!",
            ]
        ]);
    }

    public function postUpdate(Requests\RoleRequest $request)
    {
        $role = Role::findOrFail($request->input('role'));
        $role->title = $request->input('title');
        $role->icon =$request->input('icon');

        $role->save();

        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => "$role->title was successfully updated!",
            ]
        ]);
    }

    public function getEdit($id)
    {
        return CMS::render('admin.roles.edit', [
            'role' => Role::findOrFail($id),
            'users' => User::active()->where('id', '!=', '1')->orderBy('name')->get()
        ]);
    }

    public function postAdd(Request $request)
    {
        $this->validate($request, [
            'role' => 'required|exists:roles,id',
            'user' => 'required|exists:users,id',
        ]);

        $role = Role::findOrFail($request->input('role'));
        $user = User::findOrFail($request->input('user'));

        if ($user instanceof User && $role instanceof Role) {
            if ($user->hasPublicRole($role)) {
                return redirect()->back()->with([
                    'alert' => [
                        'type' => 'warning',
                        'message' => "$user->name is already a member of $role->title.",
                    ]
                ]);
            }

            $role->users()->save($user);

            return redirect()->back()->with([
                'alert' => [
                    'type' => 'success',
                    'message' => "$user->name has been added to $role->title!",
                ]
            ]);
        }

        return redirect()->back()->with([
            'alert' => [
                'type' => 'error',
                'message' => 'Could not add user to the role.',
            ]
        ]);
    }

    public function postRemove(Request $request)
    {
        $this->validate($request, [
            'role' => 'required|exists:roles,id',
            'user' => 'required|exists:users,id',
        ]);

        $role = Role::findOrFail($request->input('role'));
        $user = User::findOrFail($request->input('user'));

        if ($user instanceof User && $role instanceof Role) {
            if (!$user->hasPublicRole($role)) {
                return redirect()->back()->with([
                    'alert' => [
                        'type' => 'warning',
                        'message' => "$user->name already wasn't a member of $role->title.",
                    ]
                ]);
            }

            $role->users()->detach($user);

            return redirect()->back()->with([
                'alert' => [
                    'type' => 'success',
                    'message' => "$user->name has been removed from $role->title!",
                ]
            ]);
        }

        return redirect()->back()->with([
            'alert' => [
                'type' => 'error',
                'message' => 'Could not remove user from the role.',
            ]
        ]);
    }
}
