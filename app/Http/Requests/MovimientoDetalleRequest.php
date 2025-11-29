<?php

namespace App\Http\Requests;

use App\Models\MovimientoDetalle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class MovimientoDetalleRequest extends FormRequest
{
     public function authorize(): bool
    {
        return true; // Maneja permisos según tu sistema
    }

    public function rules(): array
    {
        return [
            'producto_id'     => ['required', 'exists:inv_productos,id'],
            'codigo_producto' => ['nullable'],
            'talla'           => ['nullable', 'string', 'max:50'],
            'color'           => ['nullable', 'string', 'max:50'],
            'bodega_id'       => ['required', 'exists:inv_bodegas,id'],
            'tipo'            => ['required', 'in:entrada,salida,ajuste'],
            'cantidad'        => ['required', 'numeric', 'min:0.01'],
            'costo_unitario'  => ['nullable', 'numeric', 'min:0'],
            'movimiento_id'   => ['nullable', 'exists:inv_movimientos,id'],
            'num_doc'         => ['required', 'numeric', 'max:100'],
        ];
    }

    // public function withValidator(Validator $validator)
    // {
    //     $validator->after(function ($validator) {
    //         if ($this->filled('movimiento_id')) {
    //             $movimiento = MovimientoDetalle::where('movimiento_id',$this->movimiento_id)->first();

    //             if ($movimiento) {
    //                 $fechaHoy = Carbon::now('America/Bogota')->toDateString();

    //                 if ($movimiento->created_at->toDateString() !== $fechaHoy) {
    //                     $validator->errors()->add('movimiento_id', 'Solo puede modificar movimientos creados hoy.');
    //                 }
    //             }
    //         }
    //     });
    // }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('movimiento_id')) {
                $movimiento = MovimientoDetalle::where('movimiento_id', $this->movimiento_id)->first();

                if ($movimiento) {
                    $fechaHoy = Carbon::now('America/Bogota');

                    // 🔥 compara solo la fecha (sin importar hora)
                    if (!Carbon::parse($movimiento->created_at)->isSameDay($fechaHoy)) {
                        $validator->errors()->add('movimiento_id', 'Solo puede modificar movimientos creados hoy.');
                    }
                }
            }
        });
    }
}
