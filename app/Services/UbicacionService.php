<?php

namespace App\Services;

use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Pais;
use App\Models\Tercero;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UbicacionService
{
    // Países

    public function listarPaises(Request $request)
    {
        $paises = Pais::withCount('departamentos')->orderBy('nombre')->get();

        return DataTables::of($paises)
            ->addIndexColumn()
            ->addColumn('id', fn ($pais) => $pais->id)
            ->addColumn('nombre', fn ($pais) => $pais->nombre)
            ->addColumn('departamentos_count', fn ($pais) => $pais->departamentos_count)
            ->addColumn('active', fn ($pais) => $pais->active
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>')
            ->addColumn('acciones', function ($pais) {
                $html = '';
                if (auth()->user()->can('ciudades.edit')) {
                    $html .= '<button type="button" onclick="upPais('.$pais->id.')" class="btn btn-warning btn-circle btn-sm" title="Editar País"><i class="fas fa-pencil-alt"></i></button>&nbsp;';
                }
                if (auth()->user()->can('ciudades.destroy')) {
                    $html .= '<button type="button" onclick="deletePais('.$pais->id.')" class="btn btn-danger btn-circle btn-sm" title="Eliminar País"><i class="fas fa-trash"></i></button>';
                }

                return $html;
            })
            ->rawColumns(['id', 'nombre', 'departamentos_count', 'active', 'acciones'])
            ->make(true);
    }

    public function guardarPais(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:paises,nombre',
            'active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors(), 'code' => 422];
        }

        try {
            Pais::create($validator->validated());

            return ['success' => true, 'message' => 'País creado exitosamente.', 'code' => 201];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al crear el país: '.$e->getMessage(), 'code' => 500];
        }
    }

    public function editarPais($id)
    {
        try {
            $pais = Pais::findOrFail($id);

            return ['success' => true, 'data' => $pais];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al cargar el país: '.$e->getMessage(), 'code' => 404];
        }
    }

    public function actualizarPais(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => "required|string|max:100|unique:paises,nombre,{$id}",
            'active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors(), 'code' => 422];
        }

        try {
            $pais = Pais::findOrFail($id);
            $pais->update($validator->validated());

            return ['success' => true, 'message' => 'País actualizado exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar el país: '.$e->getMessage(), 'code' => 500];
        }
    }

    public function eliminarPais($id)
    {
        try {
            $pais = Pais::findOrFail($id);

            if (Departamento::where('pais_id', $id)->exists()) {
                return ['success' => false, 'message' => 'No se puede eliminar el país porque tiene departamentos asociados.', 'code' => 422];
            }

            $pais->delete();

            return ['success' => true, 'message' => 'País eliminado exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al eliminar el país: '.$e->getMessage(), 'code' => 500];
        }
    }

    // Departamentos

    public function listarDepartamentos(Request $request)
    {
        $departamentos = Departamento::with('pais')->withCount('ciudades')->orderBy('nombre')->get();

        return DataTables::of($departamentos)
            ->addIndexColumn()
            ->addColumn('id', fn ($departamento) => $departamento->id)
            ->addColumn('pais', fn ($departamento) => $departamento->pais->nombre)
            ->addColumn('nombre', fn ($departamento) => $departamento->nombre)
            ->addColumn('ciudades_count', fn ($departamento) => $departamento->ciudades_count)
            ->addColumn('active', fn ($departamento) => $departamento->active
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>')
            ->addColumn('acciones', function ($departamento) {
                $html = '';
                if (auth()->user()->can('ciudades.edit')) {
                    $html .= '<button type="button" onclick="upDepartamento('.$departamento->id.')" class="btn btn-warning btn-circle btn-sm" title="Editar Departamento"><i class="fas fa-pencil-alt"></i></button>&nbsp;';
                }
                if (auth()->user()->can('ciudades.destroy')) {
                    $html .= '<button type="button" onclick="deleteDepartamento('.$departamento->id.')" class="btn btn-danger btn-circle btn-sm" title="Eliminar Departamento"><i class="fas fa-trash"></i></button>';
                }

                return $html;
            })
            ->rawColumns(['id', 'pais', 'nombre', 'ciudades_count', 'active', 'acciones'])
            ->make(true);
    }

    public function guardarDepartamento(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pais_id' => 'required|exists:paises,id',
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('departamentos')->where(fn ($query) => $query->where('pais_id', $request->pais_id)),
            ],
            'active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors(), 'code' => 422];
        }

        try {
            Departamento::create($validator->validated());

            return ['success' => true, 'message' => 'Departamento creado exitosamente.', 'code' => 201];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al crear el departamento: '.$e->getMessage(), 'code' => 500];
        }
    }

    public function editarDepartamento($id)
    {
        try {
            $departamento = Departamento::findOrFail($id);

            return ['success' => true, 'data' => $departamento];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al cargar el departamento: '.$e->getMessage(), 'code' => 404];
        }
    }

    public function actualizarDepartamento(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'pais_id' => 'required|exists:paises,id',
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('departamentos')->where(fn ($query) => $query->where('pais_id', $request->pais_id))->ignore($id),
            ],
            'active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors(), 'code' => 422];
        }

        try {
            $departamento = Departamento::findOrFail($id);
            $departamento->update($validator->validated());

            return ['success' => true, 'message' => 'Departamento actualizado exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar el departamento: '.$e->getMessage(), 'code' => 500];
        }
    }

    public function eliminarDepartamento($id)
    {
        try {
            $departamento = Departamento::findOrFail($id);

            if (Ciudad::where('departamento_id', $id)->exists()) {
                return ['success' => false, 'message' => 'No se puede eliminar el departamento porque tiene ciudades asociadas.', 'code' => 422];
            }

            $departamento->delete();

            return ['success' => true, 'message' => 'Departamento eliminado exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al eliminar el departamento: '.$e->getMessage(), 'code' => 500];
        }
    }

    // Ciudades

    public function listarCiudades(Request $request)
    {
        $ciudades = Ciudad::with('departamento.pais')->orderBy('nombre')->get();

        return DataTables::of($ciudades)
            ->addIndexColumn()
            ->addColumn('id', fn ($ciudad) => $ciudad->id)
            ->addColumn('pais', fn ($ciudad) => $ciudad->departamento->pais->nombre)
            ->addColumn('departamento', fn ($ciudad) => $ciudad->departamento->nombre)
            ->addColumn('ciudad', fn ($ciudad) => $ciudad->nombre)
            ->addColumn('active', fn ($ciudad) => $ciudad->active
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>')
            ->addColumn('acciones', function ($ciudad) {
                $html = '';
                if (auth()->user()->can('ciudades.edit')) {
                    $html .= '<button type="button" onclick="upCiudad('.$ciudad->id.')" class="btn btn-warning btn-circle btn-sm" title="Editar Ciudad"><i class="fas fa-pencil-alt"></i></button>&nbsp;';
                }
                if (auth()->user()->can('ciudades.destroy')) {
                    $html .= '<button type="button" onclick="deleteCiudad('.$ciudad->id.')" class="btn btn-danger btn-circle btn-sm" title="Eliminar Ciudad"><i class="fas fa-trash"></i></button>';
                }

                return $html;
            })
            ->rawColumns(['id', 'pais', 'departamento', 'ciudad', 'active', 'acciones'])
            ->make(true);
    }

    public function guardarCiudad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pais_id' => 'required|exists:paises,id',
            'departamento_id' => 'required|exists:departamentos,id',
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('ciudades')->where(fn ($query) => $query->where('departamento_id', $request->departamento_id)),
            ],
            'active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors(), 'code' => 422];
        }

        try {
            Ciudad::create($validator->validated());

            return ['success' => true, 'message' => 'Ciudad creada exitosamente.', 'code' => 201];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al crear la ciudad: '.$e->getMessage(), 'code' => 500];
        }
    }

    public function editarCiudad($id)
    {
        try {
            $ciudad = Ciudad::findOrFail($id);

            return ['success' => true, 'data' => $ciudad];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al cargar la ciudad: '.$e->getMessage(), 'code' => 404];
        }
    }

    public function actualizarCiudad(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'pais_id' => 'required|exists:paises,id',
            'departamento_id' => 'required|exists:departamentos,id',
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('ciudades')->where(fn ($query) => $query->where('departamento_id', $request->departamento_id))->ignore($id),
            ],
            'active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors(), 'code' => 422];
        }

        try {
            $ciudad = Ciudad::findOrFail($id);
            $ciudad->update($validator->validated());

            return ['success' => true, 'message' => 'Ciudad actualizada exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar la ciudad: '.$e->getMessage(), 'code' => 500];
        }
    }

    public function eliminarCiudad($id)
    {
        try {
            $ciudad = Ciudad::findOrFail($id);

            if (Tercero::where('ciudad_id', $id)->exists()) {
                return ['success' => false, 'message' => 'No se puede eliminar la ciudad porque tiene terceros asociados.', 'code' => 422];
            }

            $ciudad->delete();

            return ['success' => true, 'message' => 'Ciudad eliminada exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al eliminar la ciudad: '.$e->getMessage(), 'code' => 500];
        }
    }

    // Auxiliar

    public function paisesParaSelect()
    {
        return Pais::with('departamentos')->orderBy('nombre')->get();
    }
}
