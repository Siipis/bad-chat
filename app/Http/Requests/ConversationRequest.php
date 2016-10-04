<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ConversationRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $usersExist = '';

        $users = [];

        foreach (explode(' ', $this->input('participants')) as $participant) {
            $name = trim($participant, ', ');

            array_push($users, $name);
        }

        $users = array_unique($users);

        foreach ($users as $user) {
            $usersExist .= '|user:'. $user;
        }

        return [
            'title' => 'required',
            'participants' => 'required'. $usersExist,
            'message' => 'required',
        ];
    }
}
