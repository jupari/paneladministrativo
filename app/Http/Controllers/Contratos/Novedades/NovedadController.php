<?php

namespace App\Http\Controllers\Contratos\Novedades;

use App\Http\Controllers\Controller;
use App\Models\Novedad;
use App\Services\NovedadService;
use Illuminate\Http\Request;

class NovedadController extends Controller
{

    protected NovedadService $novedadService;

    public function __construct(NovedadService $novedadService) {
        $this->novedadService = $novedadService;
    }

    public function index(Request $request)
    {
        return $this->novedadService->listar($request);
    }

    public function create()
    {
        return view('contratos.novedades.create');
    }


    public function store(Request $request)
    {

        $detalles = json_decode($request->input('detalles'), true);
        $request->merge(['detalles' => $detalles]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'active' => 'required|boolean',
            'detalles' => 'required|array|min:1',
            'detalles.*.nombre' => 'required|string|max:255',
        ]);

        $novedad = Novedad::create([
            'nombre' => $request->nombre,
            'active' => $request->active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($request->detalles as $detalle) {
            $novedad->detalles()->create([
                'nombre' => $detalle['nombre'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Novedad registrada con éxito']);
    }

    public function edit($id)
    {
        $novedad = Novedad::with('detalles')->findOrFail($id);
        return view('contratos.novedades.edit', ['novedad'=>$novedad]);
    }

    public function update(Request $request, $id)
    {
        $detalles = json_decode($request->input('detalles'), true);
        $request->merge(['detalles' => $detalles]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'active' => 'required|boolean',
            'detalles' => 'required|array|min:1',
            'detalles.*.nombre' => 'required|string|max:255',
        ]);

        $novedad = Novedad::findOrFail($id);
        $novedad->update([
            'nombre' => $request->nombre,
            'active' => $request->active,
            'updated_at' => now()
        ]);

        // Eliminar detalles antiguos y crear los nuevos
        $novedad->detalles()->delete();

        foreach ($detalles as $detalle) {
            $novedad->detalles()->create([
                'nombre' => $detalle['nombre'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Novedad actualizada con éxito']);
    }


}
