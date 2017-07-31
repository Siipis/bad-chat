<?php

namespace App\Http\Controllers;

use App\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

use CMS;
use File;
use App\User;
use App\Http\Requests;

class StatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getIndex(Request $request)
    {
        if ($request->exists('user') && !$request->has('user')) {
            return redirect('/stats');
        }

        if (config('app.env') == 'local') {
            \DB::setDefaultConnection('remote');
        }

        if ($request->has('user')) {
            $user = User::findByName($request->get('user'));

            if (!$user instanceof User) {
                abort(404);
            }
        }

        $name = isset($user) ? $user->name : 'all';
        $filename = storage_path("app/statistics/statistics-$name.yaml");

        $found = File::exists($filename);
        $stats = [];

        if ($found) {
            $stats = \YAML::parse(File::get($filename));

            $updated = Carbon::parse($stats['updated']);

            $stats['updated'] = $updated->diffForHumans();

            unset($stats['data'][0]); // Temporary workaround
        }

        return CMS::render('stats.main', [
            'found' => $found,
            'users' => User::active()->where('id', '!=', '1')->orderBy('name')->get(),
            'selected' => $request->get('user'),
            'stats' => $stats,
        ]);
    }
}
