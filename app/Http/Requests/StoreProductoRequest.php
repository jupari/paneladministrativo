<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        return true; // cámbialo si tienes control de permisos
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'codigo'        => ['required', 'string', 'max:50', 'unique:inv_productos,codigo,' . $this->id],
            'nombre'        => ['required', 'string', 'max:255'],
            'tipo_producto' => ['required'],
            'descripcion'   => ['nullable', 'string'],
            'unidad_medida' => ['required'],
            'stock'         => ['nullable', 'integer', 'min:0'],
            'precio'        => ['nullable', 'numeric', 'min:0'],
            'marca'         => ['nullable'],
            'categoria'     => ['nullable'],
            'subcategoria'  => ['nullable'],
            'active'        => ['boolean'],
        ];
    }

    /**
     * Mensajes personalizados (opcional).
     */
    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique'   => 'El código ya está registrado.',
            'nombre.required' => 'El nombre es obligatorio.',
            'tipo_producto.required' => 'Debe seleccionar un tipo de producto.',
            'unidad_medida.required' => 'Debe seleccionar una unidad de medida.',
        ];
    }
}
