<?php

namespace App\Services;

use App\Models\Movimiento;
use App\Models\MovimientoDetalle;
use App\Models\Producto;
use App\Models\Saldo;
use Illuminate\Support\Facades\DB;
use Exception;

class InventarioService
{
    public function registrarMovimientoDetalle(array $data, $movimientoId = null)
    {

        DB::beginTransaction();

        try {
            $productoCodigo = Producto::where('id', $data['producto_id'] ?? 0)->first();
            $fechaHoy = \Carbon\Carbon::now();
            if ($movimientoId) {
                $movimiento = MovimientoDetalle::where('id', $movimientoId)->first();
                if (!\Carbon\Carbon::parse($movimiento->created_at)->isSameDay($fechaHoy)) {
                    throw new Exception("No puede modificar movimientos de fechas anteriores.");
                }
                $movimiento->update([
                    'talla'          => $data['talla'] ?? $movimiento->talla,
                    'color'          => $data['color'] ?? $movimiento->color,
                    'bodega_id'      => $data['bodega_id'] ?? $movimiento->bodega_id,
                    'tipo'           => $data['tipo'] ?? $movimiento->tipo,
                    'cantidad'       => $data['cantidad'] ?? $movimiento->cantidad,
                    'costo_unitario' => $data['costo_unitario'] ?? $movimiento->costo_unitario,
                    'updated_at'     => now(),
                ]);
            } else {
                $movimiento = MovimientoDetalle::create([
                    'movimiento_id'  => $data['movimiento_id'] ?? null,
                    'producto_id'    => $data['producto_id']?? null,
                    'codigo_producto'=> $productoCodigo?$productoCodigo->codigo:'',
                    'num_doc'        => $data['num_doc'] ?? 0,
                    'talla'          => $data['talla'] ?? null,
                    'color'          => $data['color'] ?? null,
                    'bodega_id'      => $data['bodega_id'] ?? 1,
                    'tipo'           => $data['tipo'] ?? null,
                    'cantidad'       => $data['cantidad'],
                    'costo_unitario' => $data['costo_unitario'] ?? 0,
                    'usuario_id'     => auth()->id(),
                ]);
            }
            $saldo = Saldo::firstOrNew([
                'producto_id' => $data['producto_id']?? null,
                'codigo_producto' => $productoCodigo?$productoCodigo->codigo:'',
                'talla'       => $data['talla'] ?? null,
                'color'       => $data['color'] ?? null,
                'bodega_id'   => $data['bodega_id'] ?? 1,
                'ultimo_costo'=> $data['costo_unitario'] ?? 0,
            ]);
            if (!$saldo->exists) {
                $saldo->saldo = 0;
                $saldo->ultimo_costo = 0;
            }
            if ($data['tipo'] == 'entrada') {
                $nuevo_total_entrada = $saldo->saldo + $data['cantidad'];
                if ($nuevo_total_entrada > 0) {
                    $saldo->ultimo_costo = (
                        ($saldo->saldo * $saldo->ultimo_costo) +
                        ($data['cantidad'] * $data['costo_unitario'] ?? 0)
                    ) / $nuevo_total_entrada;
                }
                $saldo->saldo = $nuevo_total_entrada;
            }

            if ($movimiento->tipo == 'salida') {
                $nuevo_total_salida = $saldo->saldo - $data['cantidad'];
                if ($nuevo_total_salida < 0) {
                    throw new Exception("Stock insuficiente para este producto/talla/color.");
                }
                $saldo->saldo = $nuevo_total_salida;
            }
            if ($movimiento->tipo == 'ajuste') {
                $saldo->saldo = $data['cantidad'];
                $saldo->ultimo_costo = $data['costo_unitario'] ?? $saldo->ultimo_costo;
            }
            $saldo->save();
            DB::commit();
            return $movimiento;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Error en movimiento de inventario: " . $e->getMessage());
        }
    }


    public function actualizarMovimiento(Movimiento $movimiento, array $data)
    {
        DB::beginTransaction();
        try {
            $movimiento = Movimiento::findOrFail($movimiento);
            $movimiento->update($data);
            DB::commit();
            return $movimiento;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new Exception("Error al actualizar movimiento: " . $th->getMessage());
        }
    }
}
