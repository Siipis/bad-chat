<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Auth;

class AccountRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|alpha_dash|unique:users,name,'. Auth::id(),
            'email' => 'required|email|unique:users,email,'. Auth::id() . '|unique:vouches,email,' . Auth::id() .',protegee_id',
            'password' => 'required|confirmed|min:8',
        ];
    }
}
