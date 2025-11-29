<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFichaTecnicaRequest extends FormRequest
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
            'codigo' => 'required|string|max:50|unique:prd_fichas_tecnicas,codigo',
            'nombre' => 'required|string|max:150',
            'coleccion' => 'nullable|string|max:100',
            'fecha' => 'required|date',
            'observacion' => 'nullable|string|max:500',
            'codigo_barras' => 'nullable|string|max:100|unique:prd_fichas_tecnicas,codigo_barras',
            'estado_ficha_tecnica_id' => 'nullable|exists:estado_ficha_tecnicas,id',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'El código ya existe.',
            'nombre.required' => 'El nombre es obligatorio.',
            'fecha.required' => 'Debe seleccionar una fecha.',
            'fecha.date' => 'La fecha no tiene un formato válido.',
            'codigo_barras.unique' => 'El código de barras ya está registrado.',
        ];
    }
}
