<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCotizacionRequest extends FormRequest
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
            // Datos básicos
            'num_documento' => 'required|string|max:50|unique:ord_cotizacion,num_documento',
            'fecha' => 'required|date',
            'tipo' => 'nullable|string|max:50',
            'proyecto' => 'required|string|max:255',
            'autorizacion_id' => 'nullable|exists:autorizaciones,id',
            'doc_origen' => 'nullable|string|max:100',
            'version' => 'nullable|integer|min:1',
            
            // Relaciones con terceros
            'tercero_id' => 'required|exists:terceros,id',
            'tercero_sucursal_id' => 'nullable|exists:terceros_sucursales,id',
            'tercero_contacto_id' => 'nullable|exists:terceros_contactos,id',
            
            // Información financiera
            'observacion' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'total_impuesto' => 'nullable|numeric|min:0',
            
            // Estado y control
            'estado_id' => 'nullable|exists:estados_cotizacion,id',
            // user_id se agrega automáticamente en el controlador
            'vendedor_id' => 'nullable|exists:vendedores,id',
            
            // Fechas importantes
            'fecha_vencimiento' => 'nullable|date|after:fecha',
            'fecha_envio' => 'nullable|date',
            'fecha_respuesta' => 'nullable|date',
            
            // Items (validación de array)
            'items' => 'nullable|array',
            'items.*.nombre' => 'required_with:items|string|max:255',
            'items.*.descripcion' => 'nullable|string',
            'items.*.codigo' => 'nullable|string|max:50',
            'items.*.unidad_medida' => 'nullable|string|max:20',
            'items.*.cantidad' => 'required_with:items|numeric|min:0.001',
            'items.*.valor_unitario' => 'required_with:items|numeric|min:0',
            'items.*.descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'items.*.descuento_valor' => 'nullable|numeric|min:0',
            'items.*.valor_total' => 'nullable|numeric|min:0',
            'items.*.observaciones' => 'nullable|string',
            'items.*.orden' => 'nullable|integer|min:1',
            
            // Conceptos (impuestos, descuentos)
            'conceptos' => 'nullable|array',
            'conceptos.*.concepto_id' => 'required_with:conceptos|exists:conceptos,id',
            'conceptos.*.porcentaje' => 'nullable|numeric|min:0|max:100',
            'conceptos.*.valor' => 'nullable|numeric|min:0',
            'conceptos.*.base_calculo' => 'nullable|numeric|min:0',
            'conceptos.*.incluido_precio' => 'nullable|boolean',
            
            // Observaciones
            'observaciones' => 'nullable|array',
            'observaciones.*.tipo' => 'required_with:observaciones|string|max:50',
            'observaciones.*.titulo' => 'nullable|string|max:100',
            'observaciones.*.observacion' => 'required_with:observaciones|string',
            'observaciones.*.orden' => 'nullable|integer|min:1',
            'observaciones.*.mostrar_cliente' => 'nullable|boolean',
            
            // Condiciones comerciales
            'condiciones_comerciales' => 'nullable|array',
            'condiciones_comerciales.*.tipo' => 'required_with:condiciones_comerciales|string|max:50',
            'condiciones_comerciales.*.titulo' => 'required_with:condiciones_comerciales|string|max:100',
            'condiciones_comerciales.*.descripcion' => 'required_with:condiciones_comerciales|string',
            'condiciones_comerciales.*.valor' => 'nullable|string|max:100',
            'condiciones_comerciales.*.orden' => 'nullable|integer|min:1'
         ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'num_documento.required' => 'El número de documento es obligatorio.',
            'num_documento.unique' => 'El número de documento ya existe.',
            'num_documento.max' => 'El número de documento no puede tener más de 50 caracteres.',
            'tercero_id.required' => 'Debe seleccionar un cliente.',
            'proyecto.required' => 'El proyecto es obligatorio.',
            'tercero_id.exists' => 'El cliente seleccionado no existe.',
            'tercero_sucursal_id.exists' => 'La sede seleccionada no existe.',
            'tercero_contacto_id.exists' => 'El contacto seleccionado no existe.',
            'proyecto.max' => 'El proyecto no puede tener más de 255 caracteres.',
            'autorizacion.max' => 'La autorización no puede tener más de 100 caracteres.',
            'observacion.max' => 'La observación no puede tener más de 1000 caracteres.',
            'total.numeric' => 'El total debe ser un número.',
            'total.min' => 'El total no puede ser negativo.',
            'estado_id.required' => 'Debe seleccionar un estado.',
            'estado_id.exists' => 'El estado seleccionado no existe.',
            'version.integer' => 'La versión debe ser un número entero.',
            'version.min' => 'La versión debe ser mayor a 0.',
            'subtotal.numeric' => 'El subtotal debe ser un número.',
            'subtotal.min' => 'El subtotal no puede ser negativo.',
            'descuento.numeric' => 'El descuento debe ser un número.',
            'descuento.min' => 'El descuento no puede ser negativo.',
            'total_impuesto.numeric' => 'El total de impuesto debe ser un número.',
            'total_impuesto.min' => 'El total de impuesto no puede ser negativo.',
            'fecha.date' => 'La fecha no es válida.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'num_documento' => 'número de documento',
            'tercero_id' => 'cliente',
            'tercero_sucursal_id' => 'sede',
            'tercero_contacto_id' => 'contacto',
            'estado_id' => 'estado',
            'total_impuesto' => 'total impuesto',
            'autorizacion' => 'autorización',
            'doc_origen' => 'documento origen',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validación personalizada: el descuento no puede ser mayor al subtotal
            if ($this->subtotal && $this->descuento && $this->descuento > $this->subtotal) {
                $validator->errors()->add('descuento', 'El descuento no puede ser mayor al subtotal.');
            }

            // Validación personalizada: verificar que la sede pertenezca al cliente
            if ($this->cliente_id && $this->sede) {
                $sucursal = \App\Models\TerceroSucursal::where('id', $this->sede)
                                                      ->where('tercero_id', $this->cliente_id)
                                                      ->first();
                if (!$sucursal) {
                    $validator->errors()->add('sede', 'La sede seleccionada no pertenece al cliente.');
                }
            }

            // Validación personalizada: verificar que el contacto pertenezca al cliente
            if ($this->cliente_id && $this->tercero_contacto_id) {
                $contacto = \App\Models\TerceroContacto::where('id', $this->tercero_contacto_id)
                                                      ->where('tercero_id', $this->cliente_id)
                                                      ->first();
                if (!$contacto) {
                    $validator->errors()->add('tercero_contacto_id', 'El contacto seleccionado no pertenece al cliente.');
                }
            }
        });
    }
}
