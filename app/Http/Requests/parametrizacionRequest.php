<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class parametrizacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
            'tablaCostos.*.categoria_id'           => ['required','integer'],
            'tablaCostos.*.cargo_id'               => ['required','integer'],
            'tablaCostos.*.novedad_detalle_id'     => ['required','integer'],
            'tablaCostos.*.costo_dia'              => ['nullable','numeric'],
            'tablaCostos.*.costo_hora'             => ['nullable','numeric'],
        ];
    }
}
