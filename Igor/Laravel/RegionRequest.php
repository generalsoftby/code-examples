<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\Request;

class RegionRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
	{
        $id = $this->input('id', false);

        $rules = [
            'name'  => 'required|unique:regions,name,' . $id ? $id : 'NULL,id,deleted_at,NULL',
        ];

        return $rules;
    }

    public function messages()
	{
        return [
            'name.required'         => 'Введите название.',
            'name.unique'           => 'Введённый Регион уже существует.',
        ];
    }

}
