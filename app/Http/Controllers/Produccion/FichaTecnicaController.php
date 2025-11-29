<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFichaTecnicaRequest;
use App\Http\Requests\UpdateFichaTecnicaRequest;
use App\Services\FichaTecnicaBocetoService;
use App\Services\FichaTecnicaMaterialService;
use App\Services\FichaTecnicaProcesoService;
use App\Services\FichaTecnicaService;
use Exception;
use Illuminate\Http\Request;


class FichaTecnicaController extends Controller
{
    protected FichaTecnicaService $fichaTecnicaService;
    protected FichaTecnicaBocetoService $fichaTecnicaBocetoService;
    protected FichaTecnicaMaterialService $fichaTecnicaMaterialService;
    protected FichaTecnicaProcesoService $fichaTecnicaProcesoService;

    public function __construct(FichaTecnicaService $fichaTecnicaService,
                                FichaTecnicaBocetoService $fichaTecnicaBocetoService,
                                FichaTecnicaMaterialService $fichaTecnicaMaterialService,
                                FichaTecnicaProcesoService $fichaTecnicaProcesoService) {
        $this->fichaTecnicaService = $fichaTecnicaService;
        $this->fichaTecnicaBocetoService = $fichaTecnicaBocetoService;
        $this->fichaTecnicaMaterialService = $fichaTecnicaMaterialService;
        $this->fichaTecnicaProcesoService = $fichaTecnicaProcesoService;

    }



    public function index(Request $request)
    {

        try {

            //$usuarioLogeado = Auth::user()->id;

            $authUser = auth()->user();

            $datatable = $this->fichaTecnicaService->getDataTable($request);

            if ($datatable) {
                return $datatable; // respuesta JSON si es AJAX
            }
            return view('produccion.fichas_tecnicas.index');
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener las Fichas Técnicas ' . $e->getMessage()], 500);
        }
    }

    public function create()
    {
        return view('produccion.fichas_tecnicas.fichatecnica', ['fichaTecnica'=>[], 'statusFT'=>'create']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFichaTecnicaRequest $request)
    {
        try {
            $data = $request->validated();
            $fichaTecnica=$this->fichaTecnicaService->create($data);
            return response()->json(['message'=>'El registro se creó con éxito.', 'data'=>$fichaTecnica]);
        } catch (Exception $e) {
            return response()->json(['message'=>'Error al crear el registro. '. $e->getMessage()]);
        }
    }

    public function show($id){
        $fichaTecnica = $this->fichaTecnicaService->getById($id);
        return view('produccion.fichas_tecnicas.fichatecnica', ['fichaTecnica'=>$fichaTecnica, 'statusFT'=>'edit']);
    }

    /**
     * Display the specified resource.
     */
    public function getDataById(string $id)
    {
        try {
            $fichaTecnica = $this->fichaTecnicaService->getById($id);
            $fichaTecnicaBoceto = $this->fichaTecnicaBocetoService->getById($id);
            $fichaTecnicaMaterial = $this->fichaTecnicaMaterialService->getById($id);
            $fichaTecnicaProceso = $this->fichaTecnicaProcesoService->getById($id);
            $data = [
                'fichaTecnica'=>$fichaTecnica,
                'fichaTecnicaBoceto'=>$fichaTecnicaBoceto,
                'fichaTecnicaMaterial'=>$fichaTecnicaMaterial,
                'fichaTecnicaProceso'=>$fichaTecnicaProceso
            ];
            return response()->json(['status'=>'Ok', 'data'=>$data]);
        } catch (Exception $e) {
            return response()->json(['message'=>'Error al consulta la Ficha. '. $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFichaTecnicaRequest $request, string $id)
    {
        try {
            $data = $request->validated();
            $this->fichaTecnicaService->update($id, $data);
            return response()->json(['message'=>'Ficha técnica actualizada exitosamente.']);
        } catch (Exception $e) {
            return response()->json(['message'=>'Error al actualizar la ficha técnica: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->fichaTecnicaService->delete($id);
            return response()->json(['message'=>'El estado fue actualizado con éxito.', 'status'=>'Ok'],200);
        } catch (Exception $e) {
            return response()->json(['message'=>'Error al actualizar el estado: '. $e->getMessage()]);
        }
    }

}
