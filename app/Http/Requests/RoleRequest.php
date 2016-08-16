<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class RoleRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Access::can('control.roles');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|unique:roles',
            'icon' => 'alpha_num',
        ];

        if ($this->has('role')) {
            $rules['role'] = 'exists:roles,id';
        }

        return $rules;
    }
}
