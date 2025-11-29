<?php

namespace App\Services;

use App\Models\Novedad;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;


class NovedadService
{
    public function listar(Request $request)
    {
        $novedades = Novedad::orderBy('nombre', 'asc')->get();
        if ($request->ajax()) {
            return DataTables::of($novedades)
                ->addIndexColumn()
                ->addColumn('id', fn($novedad) => $novedad->id)
                ->addColumn('nombre', fn($novedad) => $novedad->nombre)
                ->addColumn('active', fn($novedad) =>
                    $novedad->active == 1
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>'
                )
                ->addColumn('created_at', fn($novedad) =>
                    $novedad->created_at
                            ? Carbon::parse($novedad->created_at)->format('d-m-Y H:i:s')
                            : 'N/A'
                )
                ->addColumn('acciones', fn($novedad) =>
                    '<a href="admin.novedad.edit/'. $novedad->id.'" class="btn btn-warning btn-sm" title="Editar">
                        <i class="fas fa-edit"></i></button>'
                )
                ->rawColumns(['id','nombre','active', 'acciones'])
                ->make(true);
        }
        return view('contratos.novedades.index');
    }

}
