<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmpleadoRequest extends FormRequest
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
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'tipo_identificacion_id'=>'required|exists:tipo_identificacion,id',
            'identificacion' => 'required|string|max:20|regex:/^\d{1,20}$/|unique:empleados,identificacion,' . $this->route('id'),
            'expedida_en' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date|before:today',
            'fecha_inicio_labor' => 'required|date|after_or_equal:fecha_nacimiento',
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'celular' => 'required|string|max:255',
            'correo' => 'required|string|max:255',
            'cargo_id' => 'required|exists:cargos,id',
            'salario' => 'required|numeric|min:0',
            'ciudad_residencia'=>'required|string|max:255',
            'tipo_contrato' => ['required', Rule::in(['obra_labor', 'termino_fijo', 'indefinido'])], // Asegura que el tipo de contrato sea válido

            // Reglas condicionales para fecha_finalizacion_contrato y cliente_id
            'fecha_finalizacion_contrato' => [
                Rule::requiredIf(fn () => in_array($this->tipo_contrato, ['termino_fijo'])),
                'nullable',
                'date'
            ],
            'cliente_id' => [
                Rule::requiredIf(fn () => $this->tipo_contrato == 'obra_labor'),
                // 'exists:terceros,id'
                Rule::when(
                    $this->tipo_contrato == 'obra_labor',
                    ['exists:terceros,id']
                ),
            ],
            'sucursal_id' => [
                Rule::requiredIf(fn () => $this->tipo_contrato == 'obra_labor'),
                Rule::when(
                    $this->tipo_contrato == 'obra_labor',
                    ['exists:terceros_sucursales,id']
                ),
            ],
            'ubicacion' => [
                Rule::requiredIf(fn () => $this->tipo_contrato == 'obra_labor'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_finalizacion_contrato.required' => 'La fecha de finalización del contrato es obligatoria si el contrato es de obra labor o a término fijo.',
            'cliente_id.required' => 'El campo cliente es obligatorio si el contrato es de obra labor.',
            'sucursal_id.required' => 'El campo sucursal es obligatorio si el contrato es de obra labor.',
            'tipo_contrato_id'=>'El campo tipo de contrato es obligatorio.',
        ];
    }
}
