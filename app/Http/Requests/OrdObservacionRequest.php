<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrdObservacionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'texto' => 'required|string|max:1000',
            'active' => 'boolean'
        ];
    }
}
