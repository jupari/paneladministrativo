<?php

namespace App\Http\Controllers\Contratos\Plantillas;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Models\Plantilla;
use App\Services\ZamzarService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\PseudoTypes\True_;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Yajra\Datatables\Datatables;


class PlantillaController extends Controller
{
    /**
     * Muestra la lista de plantillas.
     */

     protected $zamzarService;

    public function __construct(ZamzarService $zamzarService)
    {
         $this->zamzarService = $zamzarService;
    }

    public function index(Request $request)
    {
        try {
            $plantillas = Plantilla::orderBy('created_at', 'desc')->get();

            if ($request->ajax()) {
                return Datatables::of($plantillas)
                    ->addIndexColumn()
                    ->addColumn('plantilla', function ($plantilla) {
                        return $plantilla->plantilla;
                    })
                    ->addColumn('nombre_archivo', function ($plantilla) {
                        return $plantilla->nombre_archivo;
                    })
                    ->addColumn('archivo', function ($plantilla) {
                        return '<a href="' . asset('storage/' . $plantilla->archivo) . '" target="_blank">' . basename($plantilla->archivo) . '</a>';
                    })
                    ->addColumn('campos', function ($plantilla) {
                        return $plantilla->campos ? json_encode($plantilla->campos) : 'N/A';
                    })
                    ->addColumn('active', function ($plantilla) {
                        return $plantilla->active == 1
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-danger">Inactivo</span>';
                    })
                    ->addColumn('acciones', function ($plantilla) {
                        return '<button type="button" onclick="upPlantilla(' . $plantilla->id . ')" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar"><i class="fas fa-edit"></i></button>
                                <button type="button" onclick="deletePlantilla(' . $plantilla->id . ')" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar"><i class="fas fa-trash"></i></button>';
                    })
                    ->rawColumns(['archivo', 'active', 'acciones'])
                    ->make(true);
            }
            $authUser= auth()->user();
            return view('contratos.plantillas.index',['user_id'=>$authUser->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar el empleado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Almacena una nueva plantilla.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plantilla' => 'required|string|max:255',
            'archivo' => 'required|file|mimes:docx|max:2048',
            'campos' => 'nullable|array',
            'active' => 'required|in:1,0',
        ], [
            'plantilla.required' => 'El campo plantilla es obligatorio.',
            'archivo.required' => 'Debe seleccionar un archivo.',
            'archivo.file' => 'El archivo debe ser válido.',
            'archivo.mimes' => 'El archivo debe ser de tipo .docx.',
            'archivo.max' => 'El archivo no debe superar los 2MB.',
            'active.required' => 'El campo estado es obligatorio.',
            'active.in' => 'El campo estado debe ser 1 (activo) o 0 (inactivo).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        try {

            // Subir el archivo al almacenamiento
            //$rutaArchivo = $request->file('archivo')->store('plantillas', 'public');
            $archivo = $request->file('archivo');
            $rutaArchivo = $archivo->store('plantillas', 'public');
            $nombreOriginal = $archivo->getClientOriginalName();

            // Crear la plantilla
            $plantilla = Plantilla::create([
                'plantilla' => $request->plantilla,
                'archivo' => $rutaArchivo,
                'nombre_archivo'=>$nombreOriginal,
                //'campos' => $request->campos ? json_encode($request->campos) : null,
                'active' => $request->active,
            ]);

            return response()->json(['success' => true, 'message' => 'Plantilla creada exitosamente.','plantilla'=>$plantilla], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error en la plantilla: ' . $e->getMessage()], 500);
        }
    }

    public function getPlaceHolders($plantillaId){
        try {
            $plantilla= Plantilla::where('id', $plantillaId)->first();

            $pathArchivo = storage_path("app/public/{$plantilla->archivo}");
            if (!file_exists($pathArchivo)) {
                return response()->json(['error' => "El archivo no existe en: {$pathArchivo}"], 404);
            }
            $templateProcessor = new TemplateProcessor($pathArchivo);
            $placeholders = $templateProcessor->getVariables();
            //preg_match_all('/{{(.*?)}}/', file_get_contents($pathArchivo), $matches);
            // dd(file_get_contents($pathArchivo));
            // $placeholders = array_unique($matches[1]);

            $empleados = Empleado::query()
                    ->leftJoin('cargos', 'empleados.cargo_id', '=', 'cargos.id')
                    ->leftJoin('terceros', 'empleados.cliente_id', '=', 'terceros.id')
                    ->leftJoin('terceros_sucursales', 'empleados.sucursal_id', '=', 'terceros_sucursales.id')
                    ->leftJoin('users', 'empleados.user_id', '=', 'users.id')
                    ->leftJoin('tipo_identificacion', 'tipo_identificacion.id','=','empleados.tipo_identificacion_id')
                    ->select([
                        'empleados.*',
                        'tipo_identificacion.nombre as tipoIdentificacion',
                        'cargos.nombre as cargo_nombre',
                        'terceros.nombres as cliente_nombre',
                        'terceros_sucursales.direccion as sucursal_direccion',
                        'users.name as user_nombre'
                    ])
                    ->first();
            $columnas = Schema::getColumnListing((new Empleado())->getTable());
            $columnas = array_merge($columnas, [
                'tipoIdentificacion',
                'cargo_nombre',
                'cliente_nombre',
                'sucursal_direccion',
                'manual',
                'automatico'
            ]);
            return response()->json(['success' => true, 'data' => ['plantilla'=>$plantilla,'placeholders'=>$placeholders,'columnas'=>$columnas ]], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error en la plantilla: ' . $e->getMessage()], 500);
        }
    }

    public function saveMapping(Request $request, $plantillaId)
    {
        $plantilla = Plantilla::findOrFail($plantillaId);

        $mapaJson = $request->input('mapa'); // Recupera el string JSON
        $mapa = json_decode($mapaJson, true); // Decodifica a un array

        // Validar que la decodificación fue exitosa
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'El formato del JSON es inválido.'], 422);
        }
        $mapaProcesado = [];
        foreach ($mapa as $key => $value) {
            $claveLimpia = preg_replace('/^mapa\[(.*)\]$/', '$1', $key); // Limpia "mapa[" y "]"
            $mapaProcesado[$claveLimpia] = $value;
        }

        $plantilla->update([
            'campos' => json_encode($mapaProcesado), // Actualizar mapeo
        ]);

        return response()->json(['success' => true, 'message' => 'Plantilla creada exitosamente.','plantilla'=>$plantilla], 201);
    }

    public function mapPlaceholders($plantillaId)
    {
        try {
            $plantilla = Plantilla::findOrFail($plantillaId);
            $placeholders = json_decode($plantilla->campos);


            $empleados = Empleado::query()
                    ->leftJoin('cargos', 'empleados.cargo_id', '=', 'cargos.id')
                    ->leftJoin('terceros', 'empleados.cliente_id', '=', 'terceros.id')
                    ->leftJoin('terceros_sucursales', 'empleados.sucursal_id', '=', 'terceros_sucursales.id')
                    ->leftJoin('users', 'empleados.user_id', '=', 'users.id')
                    ->leftJoin('tipo_identificacion', 'tipo_identificacion.id','=','empleados.tipo_identificacion_id')
                    ->select([
                        'empleados.*',
                        'tipo_identificacion.nombre as tipoIdentificacion',
                        'cargos.nombre as cargo_nombre',
                        'terceros.nombres as cliente_nombre',
                        'terceros_sucursales.direccion as sucursal_direccion',
                        'users.name as user_nombre'
                    ])
                    ->first();
            $columnas = Schema::getColumnListing((new Empleado())->getTable());
            $columnas = array_merge($columnas, [
                'tipoIdentificacion',
                'cargo_nombre',
                'cliente_nombre',
                'sucursal_direccion',
                'manual',
                'automatico'
            ]);
            return response()->json(['success' => true, 'data'=> ['plantilla'=>$plantilla,'placeholders'=>$placeholders,'columnas'=>$columnas]], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al traer la información: ' . $e->getMessage()], 500);
        }
    }

    public function updateColumns(Request $request, $plantillaId)
    {
        try {
            //code...
              $plantilla = Plantilla::findOrFail($plantillaId);

            $request->validate([
                'campos' => 'required|array',
            ]);

            $plantilla->update([
                'campos' => json_encode($request->campos),
            ]);
            return response()->json(['success' => true, 'data'=> [], 'message'=>'Los campos se actualizarón con éxito'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar los capos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de una plantilla específica.
     */
    public function edit($id)
    {
        try {
            $plantilla = Plantilla::findOrFail($id);
            if($plantilla->campos){
                $campos = collect(json_decode($plantilla->campos, true))->toArray();
            }else{
                $campos=[];
            }

            $empleados = Empleado::query()
                    ->leftJoin('cargos', 'empleados.cargo_id', '=', 'cargos.id')
                    ->leftJoin('terceros', 'empleados.cliente_id', '=', 'terceros.id')
                    ->leftJoin('terceros_sucursales', 'empleados.sucursal_id', '=', 'terceros_sucursales.id')
                    ->leftJoin('users', 'empleados.user_id', '=', 'users.id')
                    ->leftJoin('tipo_identificacion', 'tipo_identificacion.id','=','empleados.tipo_identificacion_id')
                    ->select([
                        'empleados.*',
                        'tipo_identificacion.nombre as tipoIdentificacion',
                        'cargos.nombre as cargo_nombre',
                        'terceros.nombres as cliente_nombre',
                        'terceros_sucursales.direccion as sucursal_direccion',
                        'users.name as user_nombre'
                    ])
                    ->first();
            $columnas = Schema::getColumnListing((new Empleado())->getTable());
            $columnas = array_merge($columnas, [
                'tipoIdentificacion',
                'cargo_nombre',
                'cliente_nombre',
                'sucursal_direccion',
                'manual',
                'automatico'
            ]);
            return response()->json([
                'success' => true,
                'data' => $plantilla,
                'campos' => $campos,
                'columnas'=>$columnas,
                'placeholders'=>$campos,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar la plantilla: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza una plantilla existente.
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'plantilla' => 'required|string|max:255',
            'campos' => 'nullable|array',
            'active' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $plantilla = Plantilla::findOrFail($id);

            $nombreOriginal=$plantilla->nombre_archivo ? $plantilla->nombre_archivo:null;
            // Subir nuevo archivo si se proporciona
            if ($request->hasFile('archivo')) {
                // Eliminar archivo anterior
                Storage::disk('public')->delete($plantilla->archivo);

                // Subir nuevo archivo
                $archivo = $request->file('archivo');
                $rutaArchivo = $archivo->store('plantillas', 'public');
                $plantilla->archivo = $rutaArchivo;
                $nombreOriginal = $archivo->getClientOriginalName();
            }

            // Actualizar otros campos
            $plantilla->update([
                'plantilla' => $request->plantilla,
                'nombre_archivo'=>$nombreOriginal,
                'campos' => $request->campos ? json_encode($request->campos) : null,
                'active' => $request->active,
            ]);

            return response()->json(['success' => true, 'message' => 'Plantilla actualizada exitosamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar la plantilla: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una plantilla.
     */
    public function destroy($id)
    {
        try {
            $plantilla = Plantilla::findOrFail($id);

            // Eliminar archivo del almacenamiento
            Storage::disk('public')->delete($plantilla->archivo);

            // Eliminar la plantilla
            $plantilla->delete();

            return response()->json(['success' => true, 'message' => 'Plantilla eliminada exitosamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la plantilla: ' . $e->getMessage()], 500);
        }
    }

    public function generarContratos(Request $request){
        try {
            $empleadosIds = $request->input('empleados');
            $documentos = [];
            foreach ($empleadosIds as $empleadoId) {
                $documento = $this->generateDocument($request, $empleadoId);
                if($documento->original['success']){
                    $documentos[] = $documento->original['data'];
                }else{
                    return response()->json([
                        'data'=>[],
                        'message' => $documento->original
                    ], 500);
                }
            }
            return response()->json([
                'data'=>$documentos,
                'message' => 'Documentos generados con éxito.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error en generarContratos: ' . $e->getMessage()], 500);
        }
    }

    //Genera word y despues el PDF
    public function generateDocument(Request $request, $empleadoId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'plantillaId' => 'required|string|max:255',
                'campos' => 'nullable|string',
            ], [
                'plantillaId.required' => 'El campo plantilla es obligatorio.',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $plantilla = Plantilla::findOrFail($request->plantillaId);
            $mapeo = json_decode($plantilla->campos, true);

            $datosUsuarios=Empleado::query()
                            ->leftJoin('cargos', 'empleados.cargo_id', '=', 'cargos.id')
                            ->leftJoin('terceros', 'empleados.cliente_id', '=', 'terceros.id')
                            ->leftJoin('terceros_sucursales', 'empleados.sucursal_id', '=', 'terceros_sucursales.id')
                            ->leftJoin('users', 'empleados.user_id', '=', 'users.id')
                            ->leftJoin('tipo_identificacion', 'tipo_identificacion.id','=','empleados.tipo_identificacion_id')
                            ->where('empleados.id', $empleadoId)
                            ->select([
                                'empleados.*',
                                'tipo_identificacion.nombre as tipoIdentificacion',
                                'cargos.nombre as cargo_nombre',
                                DB::raw("CONCAT_WS(' ', terceros.nombres, terceros.apellidos, terceros.nombre_establecimiento) as cliente_nombre"),
                                'terceros_sucursales.direccion as sucursal_direccion',
                                'users.name as user_nombre'
                            ])
                            ->get();

            $rutaPlantilla = storage_path("app/public/{$plantilla->archivo}");
            $carpetaSalida = storage_path("app/public/documentos_generados/");
            if (!file_exists($carpetaSalida)) {
                mkdir($carpetaSalida, 0777, true);
            }

            $cmanuales = json_decode($request->campos,true);
            $documentos='';

            foreach ($datosUsuarios as $dato) {
                $templateProcessor = new TemplateProcessor($rutaPlantilla);
                foreach ($mapeo as $placeholder => $columna) {
                    if ($columna === 'manual') {
                        foreach ($cmanuales as $key => $value) {
                            $templateProcessor->setValue($key, strtoupper($value));
                        }
                    } elseif ($columna === 'salario') {
                        $valorTexto = $this->numeroATexto($dato->salario);
                        $plantillaFinal = $valorTexto;
                        $templateProcessor->setValue($placeholder, $plantillaFinal);
                    } elseif ($columna === 'automatico') {
                        $templateProcessor->setValue($placeholder, $this->fechaActualEnTexto());
                    } elseif($columna === 'identificacion'){
                        $templateProcessor->setValue($placeholder, strtoupper(number_format($dato->$columna, 0, ',', '.')));
                    }
                    else  {
                        $templateProcessor->setValue($placeholder, $this->formatearSiEsFecha($dato->$columna));
                    }
                }

                $nombreArchivo = $carpetaSalida . str_replace(' ', '_', $plantilla->plantilla ."_". $datosUsuarios[0]->nombres .".docx");
                $nombreArchivopdf = $carpetaSalida . str_replace(' ', '_', $plantilla->plantilla ."_". $datosUsuarios[0]->nombres .".pdf");

                if (Storage::exists($nombreArchivo)) {
                    Storage::delete($nombreArchivo);
                    Log::info("Archivo eliminado: " . $nombreArchivo);
                }

                if (Storage::exists($nombreArchivopdf)) {
                    Storage::delete($nombreArchivopdf);
                    Log::info("Archivo eliminado: " . $nombreArchivopdf);
                }

                $templateProcessor->saveAs($nombreArchivo);

                $rutaArchivo = $nombreArchivo;

                // Verificar que el archivo existe antes de continuar
                if (!file_exists($rutaArchivo)) {
                    return response()->json(['success' => false, 'message' => 'El archivo PDF no se ha encontrado en la ruta especificada. '.$rutaArchivo]);
                }
                // Leer el archivo como contenido binario
                $contenidoArchivo = file_get_contents($rutaArchivo);

                $archivo = new \Illuminate\Http\UploadedFile(
                    $rutaArchivo,
                    $nombreArchivo,
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    //mime_content_type($rutaArchivo),
                    null,
                    true
                );

                // Llamar a ZamzarService con el archivo en lugar de la ruta
                $outputPath = $this->zamzarService->convertirArchivo($archivo, 'pdf');

                if (!$outputPath || !isset($outputPath['id'])) {
                    return response()->json(['success' => false, 'message' => 'Error al convertir el archivo.'], 500);
                }

                $fileId = $outputPath['id'];

                $statusJob =  $this->zamzarService->obtenerEstadoTrabajo($fileId);

                $fileIdPDF = $statusJob['target_files'][0]['id'];
                $rutaDestino = $carpetaSalida.str_replace(' ', '_', $plantilla->plantilla ."_". $datosUsuarios[0]->nombres .".pdf");
                $resultadoPDF = $this->zamzarService->descargarArchivo($fileIdPDF,$rutaDestino);

                return response()->json(['success'=>true, 'data'=>$resultadoPDF, 'message'=>'El documento fue creado con éxito.']);

            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error en generateDocument: ' . $e->getMessage()], 500);
        }

    }

    //Borrar
    public function generateDocumentAnt(Request $request, $empleadoId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'plantillaId' => 'required|string|max:255',
                'campos' => 'nullable|string',
            ], [
                'plantillaId.required' => 'El campo plantilla es obligatorio.',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $plantilla = Plantilla::findOrFail($request->plantillaId);
            $mapeo = json_decode($plantilla->campos, true);

            $datosUsuarios = Empleado::where('id', $empleadoId)->get();

            $rutaPlantilla = storage_path("app/public/{$plantilla->archivo}");
            $carpetaSalida = storage_path("app/public/documentos_generados/");
            if (!file_exists($carpetaSalida)) {
                mkdir($carpetaSalida, 0777, true);
            }

            $cmanuales = json_decode($request->campos,true);
            $documentos='';

            foreach ($datosUsuarios as $dato) {
                $templateProcessor = new TemplateProcessor($rutaPlantilla);

                foreach ($mapeo as $placeholder => $columna) {
                    if ($columna === 'manual') {
                        foreach ($cmanuales as $key => $value) {
                            $templateProcessor->setValue($key,strtoupper($value));
                        }
                    } else {
                        $templateProcessor->setValue($placeholder, strtoupper($dato->$columna));
                    }
                }

                $nombreArchivo = $carpetaSalida . str_replace(' ', '_', $plantilla->plantilla ."_". $datosUsuarios[0]->nombres .".docx");
                if (Storage::exists($nombreArchivo)) {
                    Storage::delete($nombreArchivo);
                    Log::info("Archivo eliminado: " . $nombreArchivo);
                }

                $nombreArchivopdf = $carpetaSalida . str_replace(' ', '_', $plantilla->plantilla ."_". $datosUsuarios[0]->nombres .".pdf");

                //dd(Storage::exists($nombreArchivopdf));
                if (Storage::exists($nombreArchivopdf)) {
                    Storage::delete($nombreArchivopdf);
                    Log::info("Archivo eliminado: " . $nombreArchivopdf);
                }

                //Guardar doc Word
                $templateProcessor->saveAs($nombreArchivo);

                $rutaArchivo = $carpetaSalida.str_replace(' ', '_', $plantilla->plantilla ."_". $datosUsuarios[0]->nombres .".docx");

                //dd($rutaArchivo);
                //dd(file_exists($rutaArchivo));

                // Verificar que el archivo existe antes de continuar
                if (!file_exists($rutaArchivo)) {
                    return response()->json(['success' => false, 'message' => 'El archivo PDF no se ha encontrado en la ruta especificada. '.$rutaArchivo]);
                }

                // Leer el archivo como contenido binario
                $contenidoArchivo = file_get_contents($rutaArchivo);

                // Crear una instancia de `UploadedFile` en memoria
                $archivo = new \Illuminate\Http\UploadedFile(
                    $rutaArchivo,                     // Ruta real del archivo
                    $nombreArchivo,                   // Nombre del archivo
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    //mime_content_type($rutaArchivo),  // Tipo MIME (ejemplo: 'application/pdf')
                    null,                              // Error (por defecto, ninguno)
                    true                               // Marcar como prueba (para evitar validaciones estrictas)
                );

                // Llamar a ZamzarService con el archivo en lugar de la ruta
                $outputPath = $this->zamzarService->convertirArchivo($archivo, 'pdf');

                $fileId = $outputPath['id'];

                $statusJob =  $this->zamzarService->obtenerEstadoTrabajo($fileId);

                $fileIdPDF = $statusJob['target_files'][0]['id'];
                $rutaDestino = $carpetaSalida.str_replace(' ', '_', $plantilla->plantilla ."_". $datosUsuarios[0]->nombres .".pdf");
                $resultadoPDF = $this->zamzarService->descargarArchivo($fileIdPDF,$rutaDestino);

                // $carpetaSalidaPdf = $carpetaSalida.'/'.'documentos_generados';
                // $command = 'soffice --headless --convert-to pdf --outdir ' . escapeshellarg(dirname($carpetaSalidaPdf)) . ' ' . escapeshellarg($nombreArchivo);

                // if (Storage::exists($carpetaSalidaPdf)) {
                //     Storage::delete($carpetaSalidaPdf);
                //     Log::info("Archivo eliminado: " . $nombreArchivo);
                // }

                //Genera el PDF
                //exec($command, $output, $resultCode);




                // $documentos ="documentos_generados/". str_replace(' ', '_', $plantilla->plantilla ."_". $datosUsuarios[0]->nombres .".docx");
                // Verificar si la conversión fue exitosa
                // if ($resultCode !== 0) {
                //     return response()->json(['success' => false, 'message' => 'Error al convertir el archivo.', 'details' => $output]);
                // }

                // $pdfPath=$carpetaSalida.  str_replace(' ', '_', $plantilla->plantilla ."_". $datosUsuarios[0]->nombres .".pdf");

                // if (!file_exists($pdfPath)) {
                //     return response()->json(['success' => false, 'message' => 'El archivo PDF no se encuentra en la ruta especificada.']);
                // }

                // if (!is_readable($pdfPath)) {
                //     return response()->json(['success' => false, 'message' => 'El archivo PDF no tiene permisos de lectura.']);
                // }
                // Log::info('Ruta del archivo PDF: ' . $pdfPath);
                //sleep(8);
                //$resultadoPDF=[];
                return response()->json(['success'=>true, 'data'=>$resultadoPDF, 'message'=>'El documento fue creado con éxito.']);

            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error en generar documento: ' . $e->getMessage()], 500);
        }

    }

    public function generateDocumentOld(Request $request, $empleadoId)
    {
        try {
            // Validar la solicitud
            $validator = Validator::make($request->all(), [
                'plantillaId' => 'required|string|max:255',
                'campos' => 'nullable|string',
            ], [
                'plantillaId.required' => 'El campo plantilla es obligatorio.',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // Obtener la plantilla
            $plantilla = Plantilla::findOrFail($request->plantillaId);
            $mapeo = json_decode($plantilla->campos, true);

            // Obtener datos del empleado
            $empleado = Empleado::findOrFail($empleadoId);
            $cmanuales = json_decode($request->campos, true);

            // Construir los datos dinámicos para la plantilla
            $datos = [
                'NOMBRES_APELLIDOS' => strtoupper($empleado->nombres . ' ' . $empleado->apellidos),
                'CEDULA' => strtoupper($empleado->cedula),
                'CIUDAD_EXPEDICION' => strtoupper($empleado->ciudad_expedicion),
                'DEPARTAMENTO' => strtoupper($empleado->departamento),
                'DIRECCION' => strtoupper($empleado->direccion),
                'FECHA_NACIMIENTO' => strtoupper($empleado->fecha_nacimiento),
                'CARGO' => strtoupper($empleado->cargo),
                'FECHA_INICIO_LABOR' => strtoupper($empleado->fecha_inicio_labor),
                'DESCRIPCION_SERVICIO' => strtoupper($empleado->descripcion_servicio)
            ];


            // Si hay datos manuales, sobrescribir los valores dinámicos
            if (!empty($cmanuales)) {
                foreach ($cmanuales as $key => $value) {
                    $datos[$key] = strtoupper($value);
                }
            }
            // Renderizar la plantilla HTML con los datos
            try {
                //code...
                $pdf = Pdf::loadView('contratos/contrato-plantilla', $datos);
            } catch (\Exception $ex) {
                throw $ex;
            }


            // Definir la ruta del archivo PDF
            $carpetaSalida = 'public/documentos_generados/';
            $nombreArchivo = str_replace(' ', '_', $plantilla->plantilla . "_" . $empleado->nombres . ".pdf");
            $rutaArchivo = $carpetaSalida . $nombreArchivo;

            // Guardar el PDF en storage
            Storage::put($rutaArchivo, $pdf->output());

            // Obtener la URL del archivo para descargar
            $urlArchivo = Storage::url($rutaArchivo);

            // Log para depuración
            Log::info('Documento generado en: ' . $urlArchivo);

            dd($urlArchivo);

            return response()->json([
                'success' => true,
                'data' => $urlArchivo,
                'message' => 'El documento fue creado con éxito.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateDocumentmalo1(Request $request, $empleadoId)
    {
        try {
            // Validar la solicitud
            $validator = Validator::make($request->all(), [
                'plantillaId' => 'required|string|max:255',
                'campos' => 'nullable|string',
            ], [
                'plantillaId.required' => 'El campo plantilla es obligatorio.',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // Obtener la plantilla
            $plantilla = Plantilla::findOrFail($request->plantillaId);
            $mapeo = json_decode($plantilla->campos, true);

            // Obtener datos del empleado
            $datosUsuarios = Empleado::where('id', $empleadoId)->get();
            $cmanuales = json_decode($request->campos, true);

            // Definir carpeta de salida para los archivos generados
            $carpetaSalida = storage_path("app/public/documentos_generados/");
            if (!file_exists($carpetaSalida)) {
                mkdir($carpetaSalida, 0777, true);
            }

            foreach ($datosUsuarios as $dato) {
                // Cargar plantilla .docx para reemplazar los valores
                $rutaPlantilla = storage_path("app/public/{$plantilla->archivo}");
                $templateProcessor = new TemplateProcessor($rutaPlantilla);

                foreach ($mapeo as $placeholder => $columna) {
                    if ($columna === 'manual') {
                        foreach ($cmanuales as $key => $value) {
                            $templateProcessor->setValue($key, strtoupper($value));
                        }
                    } else {
                        $templateProcessor->setValue($placeholder, strtoupper($dato->$columna));
                    }
                }

                // Guardar el archivo .docx temporalmente
                $nombreArchivo = str_replace(' ', '_', $plantilla->plantilla ."_". $dato->nombres .".docx");
                $rutaDocx = $carpetaSalida . $nombreArchivo;
                $templateProcessor->saveAs($rutaDocx);

                // Generar datos para la plantilla Blade (contrato.blade.php)
                $datos = [
                    'NOMBRES_APELLIDOS' => strtoupper($dato->nombres . ' ' . $dato->apellidos),
                    'CEDULA' => strtoupper($dato->cedula),
                    'CIUDAD_EXPEDICION' => strtoupper($dato->ciudad_expedicion),
                    'DEPARTAMENTO' => strtoupper($dato->departamento),
                    'DIRECCION' => strtoupper($dato->direccion),
                    'FECHA_NACIMIENTO' => strtoupper($dato->fecha_nacimiento),
                    'CARGO' => strtoupper($dato->cargo),
                    'FECHA_INICIO_LABOR' => strtoupper($dato->fecha_inicio_labor),
                    'DESCRIPCION_SERVICIO' => strtoupper($dato->descripcion_servicio),
                ];

                // Renderizar plantilla en PDF
                $pdf = Pdf::loadView('contratos/contrato-plantilla', $datos)->setPaper('A4', 'portrait');

                // Definir la ruta del PDF generado
                $nombreArchivoPDF = str_replace(' ', '_', $plantilla->plantilla ."_". $dato->nombres .".pdf");
                $rutaPDF = 'public/documentos_generados/' . $nombreArchivoPDF;

                // Guardar el PDF en el almacenamiento de Laravel
                Storage::put($rutaPDF, $pdf->output());

                // Obtener la URL del archivo para descargar
                $urlArchivo = Storage::url($rutaPDF);

                // Log para depuración
                Log::info('Documento generado en: ' . $urlArchivo);

                return response()->json([
                    'success' => true,
                    'data' => $urlArchivo,
                    'message' => 'El documento fue creado con éxito.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateDocumentMejorando(Request $request, $empleadoId)
    {
        try {
            // Validar la solicitud
            $validator = Validator::make($request->all(), [
                'plantillaId' => 'required|string|max:255',
                'campos' => 'nullable|string',
            ], [
                'plantillaId.required' => 'El campo plantilla es obligatorio.',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // Obtener la plantilla
            $plantilla = Plantilla::findOrFail($request->plantillaId);
            $mapeo = json_decode($plantilla->campos, true);

            // Obtener datos del empleado
            $datosUsuarios = Empleado::where('id', $empleadoId)->get();
            $cmanuales = json_decode($request->campos, true);

            // Definir carpeta de salida para los archivos generados
            $carpetaSalida = storage_path("app/public/documentos_generados/");
            if (!file_exists($carpetaSalida)) {
                mkdir($carpetaSalida, 0777, true);
            }

            foreach ($datosUsuarios as $dato) {
                // Generar datos dinámicos desde la tabla Empleado
                $datos = [
                    'NOMBRES_APELLIDOS' => strtoupper($dato->nombres . ' ' . $dato->apellidos),
                    'CEDULA' => strtoupper($dato->cedula),
                    'CIUDAD_EXPEDICION' => strtoupper($dato->ciudad_expedicion),
                    'DEPARTAMENTO' => strtoupper($dato->departamento),
                    'DIRECCION' => strtoupper($dato->direccion),
                    'FECHA_NACIMIENTO' => strtoupper($dato->fecha_nacimiento),
                    'CARGO' => strtoupper($dato->cargo),
                    'FECHA_INICIO_LABOR' => strtoupper($dato->fecha_inicio_labor),
                    'DESCRIPCION_SERVICIO' => strtoupper($dato->descripcion_servicio),
                ];

                // Si hay datos manuales, sobrescribir los valores dinámicos
                if (!empty($cmanuales)) {
                    foreach ($cmanuales as $key => $value) {
                        $datos[$key] = strtoupper($value);
                    }
                }

                // 🔹 Renderizar la plantilla Blade (asegurando que reciba datos correctamente)
                $pdf = Pdf::loadView('contratos.contrato-plantilla', compact('datos'))->setPaper('A4', 'portrait');

                // Definir la ruta del PDF generado
                $nombreArchivoPDF = str_replace(' ', '_', $plantilla->plantilla ."_". $dato->nombres .".pdf");
                $rutaPDF = 'public/documentos_generados/' . $nombreArchivoPDF;

                // Guardar el PDF en el almacenamiento de Laravel
                Storage::put($rutaPDF, $pdf->output());

                // Obtener la URL del archivo para descargar
                $urlArchivo = Storage::url($rutaPDF);

                // Log para depuración
                Log::info('Documento generado en: ' . $urlArchivo);

                return response()->json([
                    'success' => true,
                    'data' => $urlArchivo,
                    'message' => 'El documento fue creado con éxito.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateDocumentFuncionaHTML(Request $request, $empleadoId)
    {
        try {
            // Validar la solicitud
            $validator = Validator::make($request->all(), [
                'plantillaId' => 'required|string|max:255',
                'campos' => 'nullable|string',
            ], [
                'plantillaId.required' => 'El campo plantilla es obligatorio.',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // Obtener la plantilla
            $plantilla = Plantilla::findOrFail($request->plantillaId);
            $mapeo = json_decode($plantilla->campos, true); // Mapeo de variables de la plantilla

            // Obtener datos del empleado
            $empleado = Empleado::findOrFail($empleadoId);
            $cmanuales = json_decode($request->campos, true); // Datos adicionales

            // Definir carpeta de salida
            $carpetaSalida = storage_path("app/public/documentos_generados/");
            if (!file_exists($carpetaSalida)) {
                mkdir($carpetaSalida, 0777, true);
            }

            // Cargar plantilla .docx y reemplazar valores dinámicos
            $rutaPlantilla = storage_path("app/public/{$plantilla->archivo}");
            $templateProcessor = new TemplateProcessor($rutaPlantilla);


            $datos = []; // Array dinámico de datos

            foreach ($mapeo as $placeholder => $columna) {
                if ($columna === 'manual') {
                    foreach ($cmanuales as $key => $value) {
                        $templateProcessor->setValue($key, strtoupper($value));
                        $datos[$key] = strtoupper($value); // Guardamos también en el array dinámico
                    }
                } else {
                    $valor = strtoupper($empleado->$columna);
                    $templateProcessor->setValue($placeholder, $valor);
                    $datos[$placeholder] = $valor; // Guardamos en el array dinámico
                }
            }

            // Guardar el archivo .docx temporalmente
            // $nombreArchivo = str_replace(' ', '_', $plantilla->plantilla ."_". $empleado->nombres .".docx");
            // $rutaDocx = $carpetaSalida . $nombreArchivo;
            // $templateProcessor->saveAs($rutaDocx);

            $pdf = Pdf::loadView('contratos.contrato-plantilla', compact('datos'))->setPaper('A4', 'portrait');

            // Definir la ruta del PDF generado
            $nombreArchivoPDF = str_replace(' ', '_', $plantilla->plantilla ."_". $empleado->nombres .".pdf");
            $rutaPDF = 'public/documentos_generados/' . $nombreArchivoPDF;


            if (Storage::exists($rutaPDF)) {
                Storage::delete($rutaPDF);
                Log::info("Archivo eliminado: " . $rutaPDF);
            }


            // Guardar el PDF en el almacenamiento de Laravel
            Storage::put($rutaPDF, $pdf->output());

            // Obtener la URL del archivo para descargar
            $urlArchivo = Storage::url($rutaPDF);

            // Log para depuración
            Log::info('Documento generado en: ' . $urlArchivo);

            return response()->json([
                'success' => true,
                'data' => $urlArchivo,
                'message' => 'El documento fue creado con éxito.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar documento: ' . $e->getMessage()
            ], 500);
        }
    }


    public function convertWordToPdf($wordFilePath)
    {
        // Cargar el archivo Word (.docx)
        $phpWord = IOFactory::load($wordFilePath);
        // Convertir el archivo Word a HTML
        $htmlContent = $this->convertWordToHtml($phpWord);
        // Usar DomPDF para generar el PDF desde HTML
        $pdf = PDF::loadHTML($htmlContent);

        // Guardar el archivo PDF
        $pdf->save(storage_path('app/public/documentos_generados/documento.pdf'));

        return true;
    }

    private function convertWordToHtml($phpWord)
    {
        ob_start();
        // Convertir el archivo Word a HTML
        $objWriter = IOFactory::createWriter($phpWord, 'HTML');
        $objWriter->save('php://output');
        $htmlContent=ob_get_clean();

        return $htmlContent;
    }

    function numeroATexto($numero)
    {
         // Convertir a texto en español
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        $texto = mb_strtoupper($formatter->format($numero), 'UTF-8');
        // Formatear número con puntos como separador de miles
        $numero_formateado = '$' . number_format($numero, 0, ',', '.');
        // Si es múltiplo exacto de 1 millón, usar “DE PESOS”
        $sufijo = ($numero % 1000000 === 0) ? 'DE PESOS' : 'PESOS';

        return "{$texto} {$sufijo} ({$numero_formateado})";
    }

    function fechaActualEnTexto()
    {
       $formatter = new \IntlDateFormatter(
            'es_ES',
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE,
            null,
            null,
            "d 'día(s) de' MMMM 'de' y"
        );

        return ucfirst($formatter->format(new \DateTime()));
    }

    function formatearSiEsFecha($valor)
    {

        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('d/m/Y');
        }

        if (is_string($valor) && strtotime($valor)) {
            try {
                $fecha = new \DateTime($valor);
                return $fecha->format('d/m/Y');
            } catch (\Exception $e) {
                // No es una fecha válida
            }
        }

        return strtoupper($valor);
    }

}
