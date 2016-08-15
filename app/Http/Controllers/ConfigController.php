<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use File;
use CMS;
use FrontLog;

class ConfigController extends Controller
{
    protected $configPath;

    public function __construct()
    {
        $this->middleware('access:config');

        $this->configPath = config('chat.configPath');
    }

    public function getIndex()
    {
        return CMS::render('admin.config', [
            'config' => $this->loadConfig(),
            'levels' => FrontLog::getLevels(),
        ]);
    }

    public function postIndex(Requests\ConfigRequest $request)
    {
        $config = [];

        $config['name'] = $request->input('name');
        $config['allowLogins'] = $request->input('allowLogins') == 'true' ? true : false;
        $config['allowRegistration'] = $request->input('allowRegistration') == 'true' ? true : false;
        $config['vouching']['maxTier'] = intval($request->input('vouching_maxTier'));
        $config['errors']['minLevel'] = $request->input('errors_minLevel');
        $config['errors']['emails'] = $request->input('errors_emails');

        File::put($this->configPath, json_encode($config));

        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => "Config has been successfully saved!",
            ]
        ]);
    }

    private function loadConfig()
    {
        $file = File::get($this->configPath);

        return json_decode($file, true);
    }
}
