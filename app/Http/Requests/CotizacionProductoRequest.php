<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CotizacionProductoRequest extends FormRequest
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
            'cotizacion_id' => 'required|exists:ord_cotizacion,id',
            'producto_id' => [
                'nullable',
                Rule::unique('ord_cotizacion_productos', 'producto_id')
                    ->where(fn ($query) => $query->where('cotizacion_id', $this->cotizacion_id))
                ],
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'codigo' => 'nullable|string|max:50',
            'unidad_medida' => 'required|string|max:20',
            'cantidad' => 'required|numeric|min:0.001|max:999999.999',
            'valor_unitario' => 'required|numeric|min:0|max:999999999.99',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'descuento_valor' => 'nullable|numeric|min:0|max:999999999.99',
            'observaciones' => 'nullable|string|max:1000',
            'orden' => 'nullable|integer|min:1|max:999',
            'active' => 'boolean',
            // Campos de configuración de costos
            'categoria_id' => 'nullable|exists:categorias,id',
            'cargo_id' => 'nullable|exists:cargos,id',
            'tipo_costo' => 'nullable|string|in:unitario,hora,dia',
            'costo_dia' => 'nullable|numeric|min:0|max:999999999.99',
            'costo_hora' => 'nullable|numeric|min:0|max:999999999.99',
            'costo_unitario' => 'nullable|numeric|min:0|max:999999999.99',
            'dias_diurnos' => 'nullable|integer|min:0|max:365',
            'dias_nocturnos' => 'nullable|integer|min:0|max:365',
            'dias_remunerados_diurnos' => 'nullable|integer|min:0|max:365',
            'dias_remunerados_nocturnos' => 'nullable|integer|min:0|max:365',
            'dominicales_diurnos' => 'nullable|integer|min:0|max:52',
            'dominicales_nocturnos' => 'nullable|integer|min:0|max:52',
            'horas_diurnas' => 'nullable|integer|min:0|max:24',
            'horas_remuneradas' => 'nullable|integer|min:0|max:8760',
            'incluir_dominicales' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cotizacion_id.required' => 'La cotización es obligatoria.',
            'cotizacion_id.exists' => 'La cotización seleccionada no existe.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
            'unidad_medida.required' => 'La unidad de medida es obligatoria.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'cantidad.max' => 'La cantidad no puede exceder 999,999.999.',
            'valor_unitario.required' => 'El valor unitario es obligatorio.',
            'valor_unitario.min' => 'El valor unitario debe ser mayor o igual a 0.',
            'valor_unitario.max' => 'El valor unitario no puede exceder 999,999,999.99.',
            'descuento_porcentaje.max' => 'El descuento porcentual no puede ser mayor al 100%.',
            'descuento_valor.max' => 'El descuento en valor no puede exceder 999,999,999.99.',
            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres.',
            'orden.min' => 'El orden debe ser mayor a 0.',
            'orden.max' => 'El orden no puede exceder 999.',
            // Mensajes para campos de configuración de costos
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'cargo_id.exists' => 'El cargo seleccionado no existe.',
            'tipo_costo.in' => 'El tipo de costo debe ser: unitario, hora o día.',
            'costo_dia.min' => 'El costo por día debe ser mayor o igual a 0.',
            'costo_dia.max' => 'El costo por día no puede exceder 999,999,999.99.',
            'costo_hora.min' => 'El costo por hora debe ser mayor o igual a 0.',
            'costo_hora.max' => 'El costo por hora no puede exceder 999,999,999.99.',
            'costo_unitario.min' => 'El costo unitario debe ser mayor o igual a 0.',
            'costo_unitario.max' => 'El costo unitario no puede exceder 999,999,999.99.',
            'dias_diurnos.min' => 'Los días diurnos deben ser mayor o igual a 0.',
            'dias_diurnos.max' => 'Los días diurnos no pueden exceder 365.',
            'dias_nocturnos.min' => 'Los días nocturnos deben ser mayor o igual a 0.',
            'dias_nocturnos.max' => 'Los días nocturnos no pueden exceder 365.',
            'horas_diurnas.min' => 'Las horas diurnas deben ser mayor o igual a 0.',
            'horas_diurnas.max' => 'Las horas diurnas no pueden exceder 24.',
            'horas_remuneradas.min' => 'Las horas remuneradas deben ser mayor o igual a 0.',
            'horas_remuneradas.max' => 'Las horas remuneradas no pueden exceder 8,760.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'active' => $this->has('active') ? (bool) $this->active : true,
            'descuento_porcentaje' => $this->descuento_porcentaje ?? 0,
            'descuento_valor' => $this->descuento_valor ?? 0,
            'incluir_dominicales' => $this->has('incluir_dominicales') ? (bool) $this->incluir_dominicales : false,
            'dias_diurnos' => $this->dias_diurnos ?? 0,
            'dias_nocturnos' => $this->dias_nocturnos ?? 0,
            'dias_remunerados_diurnos' => $this->dias_remunerados_diurnos ?? 0,
            'dias_remunerados_nocturnos' => $this->dias_remunerados_nocturnos ?? 0,
            'dominicales_diurnos' => $this->dominicales_diurnos ?? 0,
            'dominicales_nocturnos' => $this->dominicales_nocturnos ?? 0,
            'horas_diurnas' => $this->horas_diurnas ?? 0,
            'horas_remuneradas' => $this->horas_remuneradas ?? 0,
            'costo_dia' => $this->costo_dia ?? 0,
            'costo_hora' => $this->costo_hora ?? 0,
            'costo_unitario' => $this->costo_unitario ?? 0,
        ]);
    }
}
