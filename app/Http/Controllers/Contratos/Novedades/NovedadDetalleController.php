<?php

namespace App\Http\Controllers\Contratos\Novedades;

use App\Http\Controllers\Controller;
use App\Models\NovedadDetalle;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class NovedadDetalleController extends Controller
{
    public function getByNovedad($id)
    {
        $query = NovedadDetalle::where('novedad_id', $id);

        return DataTables::of($query)
            ->addColumn('acciones', function ($row) {
                return '<button class="btn btn-danger btn-sm" onclick="eliminarDetalle(' . $row->id . ')">Eliminar</button>';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

}
