<?php

namespace App\Services;

use App\Exceptions\AppValidationException;
use App\Models\Categoria;
use App\Models\ItemPropio;
use App\Models\ParametrizacionCosto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ItemPropioService
{

    public function listar()
    {
        return ItemPropio::with('categoria:id,nombre')
            ->orderBy('nombre')
            ->get();
    }

    public function crear(array $data): ItemPropio
    {
        try {
            return DB::transaction(function () use ($data) {
                $data['active']     = isset($data['active']) ? (int) !!$data['active'] : 1;
                return ItemPropio::create($data);
            });
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function actualizar(ItemPropio $item, array $data): ItemPropio
    {
        try {
            return DB::transaction(function () use ($item, $data) {
                $data['active']     = isset($data['active']) ? (int) !!$data['active'] : $item->active;

                $item->update($data);
                return $item->fresh('categoria:id,nombre');
            });
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminar(ItemPropio $item): void
    {
        DB::transaction(function () use ($item) {
            // Normaliza por si tu código se guarda en mayúscula
            $codigo = strtoupper(trim($item->codigo));

            // Bloquea las filas candidatas mientras decides (evita condiciones de carrera)
            $enUso = ParametrizacionCosto::where('item', $codigo)
                ->lockForUpdate()
                ->exists();

            if ($enUso) {
                throw new AppValidationException(
                    "No se puede eliminar el ítem '{$codigo}' porque está en uso en parametrización de costos."
                );
            }

            // Si no está en uso, elimina
            $item->delete();
        });
    }
}
