<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Access;

class ConfigRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Access::can('config');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'allowLogin' => 'in:true,null',
            'allowRegistration' => 'in:true,false',
            'vouching_maxTier' => 'required|numeric',
            'errors_minLevel' => 'required',
            'errors_emails' => 'required',
        ];
    }
}
