<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Auth;
use Access;

class BanRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!Auth::check()) {
            return false;
        }

        return Access::can('control.bans');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|exists:users,name',
            'duration' => 'required|numeric',
            'units' => 'required|in:hours,days,years',
        ];
    }
}
