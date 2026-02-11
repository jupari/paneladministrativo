<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CotizacionCondicionComercialRequest extends FormRequest
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
            'cotizacion_id' => 'required|integer|exists:ord_cotizacion,id',
            'tiempo_entrega' => 'nullable|string|max:255',
            'lugar_obra' => 'nullable|string|max:500',
            'duracion_oferta' => 'nullable|string|max:255',
            'garantia' => 'nullable|string|max:500',
            'forma_pago' => 'nullable|string|max:500'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cotizacion_id.required' => 'La cotización es requerida.',
            'cotizacion_id.exists' => 'La cotización seleccionada no existe.',
            'tiempo_entrega.max' => 'El tiempo de entrega no puede exceder 255 caracteres.',
            'lugar_obra.max' => 'El lugar de obra no puede exceder 500 caracteres.',
            'duracion_oferta.max' => 'La duración de la oferta no puede exceder 255 caracteres.',
            'garantia.max' => 'La garantía no puede exceder 500 caracteres.',
            'forma_pago.max' => 'La forma de pago no puede exceder 500 caracteres.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cotizacion_id' => 'cotización',
            'tiempo_entrega' => 'tiempo de entrega',
            'lugar_obra' => 'lugar de obra',
            'duracion_oferta' => 'duración de la oferta',
            'garantia' => 'garantía',
            'forma_pago' => 'forma de pago'
        ];
    }
}
