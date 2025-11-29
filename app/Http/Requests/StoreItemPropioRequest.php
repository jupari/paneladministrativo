<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemPropioRequest extends FormRequest
{
     public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'categoria_id'  => ['required','integer','exists:categorias,id'],
            'nombre'        => ['required','string','max:255'],
            'codigo'        => ['required','string','max:50','unique:items_propios,codigo'],
            'unidad_medida' => ['required','string','max:20'], // ej: sigla "HR", "DIA"
            'active'        => ['nullable','boolean'],
        ];
    }
}
