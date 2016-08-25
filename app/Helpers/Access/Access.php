<?php


namespace App\Helpers\Access;


class Access
{
    private $user;

    const control = [
        'registration' => 'moderator',
        'bans' => 'moderator',
        'discouragement' => 'admin',
        'users' => 'admin',
        'roles' => 'admin',
    ];

    const view = [
        'users' => 'moderator',
        'discouragement' => 'admin',
        'logs' => 'moderator',
        'visits' => 'moderator',
        'events' => 'moderator',
        'errors' => 'admin',
    ];

    const config = 'admin';

    const messaging = 'moderator';

    public function __construct()
    {
        $this->user = \Auth::user();
    }

    /**
     * Returns true if the user can perform an action
     *
     * @param $code
     * @return bool
     */
    public function can($code)
    {
        if (is_null($this->user)) {
            return false;
        }

        $require = $this->getRequired($code);

        switch ($require) {
            case 'admin':
                return $this->user->isAdmin();
            case 'moderator':
                return $this->user->isStaff();
            default:
                return true;
        }
    }

    /**
     * Gets the required role
     *
     * @param $const
     * @return false|string
     */
    private function getRequired($code)
    {
        $ref = $this->normalizeKey($code);

        $const = is_string($ref) ? $ref : array_keys($ref)[0];

        if ($const == '*') {
            return true;
        }

        $var = constant("self::$const");

        if (!is_null($var)) {
            if (is_string($ref)) {
                return $var;
            } else {
                $key = $ref[$const];

                if ($key == '*') {
                    return true;
                }

                return $var[$key];
            }
        }

        return false;
    }

    /**
     * Parses a code into either a string or an array
     *
     * @param $string
     * @return string|array
     */
    private function normalizeKey($string)
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException("Input needs to be string, " . gettype($string) . " given.");
        }

        if (str_contains($string, '.')) {
            $split = explode('.', $string);

            return [
                $split[0] => $split[1]
            ];
        }

        return $string;
    }
}