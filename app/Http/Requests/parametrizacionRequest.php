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
            'parametrizacion'                          => ['required', 'array'],
            'parametrizacion.*.categoria_id'           => ['required', 'integer'],
            'parametrizacion.*.cargo_id'               => ['required', 'integer'],
            'parametrizacion.*.novedad_detalle_id'     => ['required', 'integer'],
            'parametrizacion.*.valor_porcentaje'       => ['nullable', 'numeric'],
            'parametrizacion.*.valor_admon'            => ['nullable', 'boolean'],
            'parametrizacion.*.valor_obra'             => ['nullable', 'boolean'],
        ];
    }
}
