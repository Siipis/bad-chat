<?php


namespace App\Traits;


trait HasRoles
{
    protected $ranks = [
        'admin' => 2,
        'moderator' => 1,
        'member' => 0,
        'suspended' => -1,
    ];

    public function getRoleAttribute($role)
    {
        switch ($role) {
            case $this->ranks['admin']:
                return 'admin';
            case $this->ranks['moderator']:
                return 'moderator';
        }

        return 'member';
    }

    public function getRoleNumAttribute()
    {
        return $this->attributes['role'];
    }

    public function setRoleAttribute($role)
    {
        switch ($role) {
            case 'admin':
                $this->attributes['role'] = $this->ranks['admin'];
                return;
            case 'moderator':
                $this->attributes['role'] = $this->ranks['moderator'];
                return;
            default:
                $this->attributes['role'] = $this->ranks['member'];
                return;
        }
    }

    public function promote()
    {
        if (!isset($this->attributes['role'])) {
            $this->attributes['role'] = $this->ranks['member'];
        }

        if ($this->attributes['role'] == $this->ranks['admin']) {
            return false;
        }

        $this->attributes['role']++;

        return true;
    }

    public function demote()
    {
        if (!isset($this->attributes['role'])) {
            $this->attributes['role'] = $this->ranks['member'];
        }

        if ($this->attributes['role'] == $this->ranks['member']) {
            return false;
        }

        $this->attributes['role']--;

        return true;
    }
}