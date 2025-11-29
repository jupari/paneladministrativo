<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFichaTecnicaRequest extends FormRequest
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
            // 'codigo' is not updatable, so exclude it from validation rules
            'nombre' => 'required|string|max:255',
            'coleccion' => 'nullable|string|max:100',
            'fecha' => 'nullable|date',
            'observacion' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            // 'codigo.required' => 'El campo código es obligatorio.',
            // 'codigo.max' => 'El campo código no debe exceder los 50 caracteres.',

            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string' => 'El campo nombre debe ser una cadena de texto.',
            'nombre.max' => 'El campo nombre no debe exceder los 255 caracteres.',

            'coleccion.string' => 'El campo colección debe ser una cadena de texto.',
            'coleccion.max' => 'El campo colección no debe exceder los 100 caracteres.',

            'fecha.date' => 'El campo fecha debe ser una fecha válida.',

            'observacion.string' => 'El campo observación debe ser una cadena de texto.',
            'observacion.max' => 'El campo observación no debe exceder los 500 caracteres.',
        ];
    }
}
