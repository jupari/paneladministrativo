<?php

namespace App\Http\Requests\Cotizar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CotizacionItemRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'cotizacion_id' => 'required|exists:ord_cotizacion,id',
            'items' => 'nullable|array',
            'items.*.nombre' => 'required|string|max:255',
            'items.*.active' => 'nullable|boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cotizacion_id.required' => 'El ID de la cotización es obligatorio',
            'cotizacion_id.exists' => 'La cotización especificada no existe',
            'items.array' => 'Los items deben ser un array válido',
            'items.*.nombre.required' => 'El nombre del item es obligatorio',
            'items.*.nombre.string' => 'El nombre del item debe ser texto',
            'items.*.nombre.max' => 'El nombre del item no puede exceder 255 caracteres',
            'items.*.active.boolean' => 'El estado activo debe ser verdadero o falso'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cotizacion_id' => 'cotización',
            'items' => 'items',
            'items.*.nombre' => 'nombre del item',
            'items.*.active' => 'estado activo'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Errores de validación en los datos enviados',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Limpiar y preparar datos si es necesario
        if ($this->has('items') && is_array($this->items)) {
            $items = collect($this->items)->map(function ($item, $index) {
                return array_merge($item, [
                    'active' => $item['active'] ?? true,
                ]);
            })->toArray();

            $this->merge(['items' => $items]);
        }
    }
}
