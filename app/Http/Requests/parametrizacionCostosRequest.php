<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class parametrizacionCostosRequest extends FormRequest
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
            'tablaCostos.*.item'            => ['required','string','max:100'],
            'tablaCostos.*.categoria_id'    => ['required','numeric'],
            'tablaCostos.*.item_nombre'     => ['nullable','string','max:255'],
            // Puede venir como sigla (FK) o como nombre legible; lo normalizamos en el servicio
            'tablaCostos.*.unidad_medida'   => ['required','string','max:20'],
            'tablaCostos.*.costo_dia'       => ['required','numeric'],
            'tablaCostos.*.active'          => ['nullable','boolean'],
            // (opcionales si los mandas desde el front)
            'tablaCostos.*.created_at'      => ['nullable','date'],
            'tablaCostos.*.updated_at'      => ['nullable','date'],
        ];
    }
}
