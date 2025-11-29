<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemPropioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $itemId = $this->route('item_propio')?->id ?? $this->route('id');

        return [
            'categoria_id'  => ['required','integer','exists:categorias,id'],
            'nombre'        => ['required','string','max:255'],
            'codigo'        => [
                'required','string','max:50',
                Rule::unique('items_propios','codigo')->ignore($itemId)
            ],
            'unidad_medida' => ['required','string','max:20'],
            'active'        => ['nullable','boolean'],
        ];
    }
}
