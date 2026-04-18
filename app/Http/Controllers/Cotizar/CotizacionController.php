<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCotizacionRequest;
use App\Http\Requests\UpdateCotizacionRequest;
use App\Mail\CotizacionEnviada;
use App\Models\Cotizacion;
use App\Models\EstadoCotizacion;
use App\Services\CotizacionPdfService;
use App\Services\CotizacionService;
use App\Services\ParametroService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class CotizacionController extends Controller
{
    protected $cotizacionService;
    protected $pdfService;
    protected $parametros;

    public function __construct(
        CotizacionService $cotizacionService,
        CotizacionPdfService $pdfService,
        ParametroService $parametros
    ) {
        $this->cotizacionService = $cotizacionService;
        $this->pdfService = $pdfService;
        $this->parametros = $parametros;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $cotizaciones = $this->cotizacionService->obtenerCotizaciones();
                return DataTables::of($cotizaciones)
                    ->addIndexColumn()
                    ->addColumn('id', function ($row) {
                        return $row->id;
                    })
                    ->addColumn('num_documento', function ($row) {
                        return $row->num_documento;
                    })
                    ->addColumn('version', function ($row) {
                        return $row->version;
                    })
                    ->addColumn('cliente', function ($row) {
                        if ($row->tercero) {
                            return $row->tercero->nombres && $row->tercero->apellidos
                                ? $row->tercero->nombres . ' ' . $row->tercero->apellidos
                                : $row->tercero->nombre_establecimiento;
                        }
                        return 'Sin cliente';
                    })
                    ->addColumn('sede', function ($row) {
                        if ($row->terceroSucursal) {
                            return $row->terceroSucursal->nombre_sucursal . ' - ' . $row->terceroSucursal->direccion;
                        }
                        return 'Sin sede';
                    })
                    ->addColumn('proyecto', function ($row) {
                        return $row->proyecto;
                    })
                    ->addColumn('fecha', function ($row) {
                        if ($row->fecha) {
                            return \Carbon\Carbon::parse($row->fecha)->format('d-m-Y');
                        } elseif ($row->created_at) {
                            return \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i');
                        }
                        return 'N/A';
                    })
                    ->addColumn('estado', function ($row) {
                        $colorMap = [
                            'primary'   => '#0d6efd',
                            'prymari'   => '#0d6efd',
                            'secondary' => '#6c757d',
                            'success'   => '#28a745',
                            'danger'    => '#dc3545',
                            'warning'   => '#e0a800',
                            'info'      => '#17a2b8',
                            'dark'      => '#343a40',
                            'light'     => '#adb5bd',
                        ];
                        if ($row->estado) {
                            $raw   = $row->estado->color ?? '';
                            $color = str_starts_with($raw, '#')
                                ? $raw
                                : ($colorMap[strtolower($raw)] ?? '#6c757d');
                            return '<span class="badge" style="background-color:' . $color . ';color:#fff;">'
                                . e($row->estado->estado) . '</span>';
                        }
                        return '<span class="badge" style="background-color:#6c757d;color:#fff;">Sin estado</span>';
                    })
                    ->addColumn('total', function ($row) {
                        return '$' . number_format($row->total, 2);
                    })
                    ->addColumn('actions', function ($row) {
                        return '<div class="btn-group" role="group">
                                    <a type="button" class="btn btn-sm btn-info" href="' . route('admin.cotizaciones.show', $row->id) . '" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a type="button" class="btn btn-sm btn-warning" href="' . route('admin.cotizaciones.edit', $row->id) . '" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" onclick="duplicateCotizacion(' . $row->id . ')" title="Duplicar">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteCotizacion(' . $row->id . ')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>';
                    })
                    ->rawColumns(['id','num_documento','version','cliente','fecha','estado','total','actions'])
                    ->make(true);
            }
            // $estadisticas = $this->cotizacionService->obtenerEstadisticas();
            $estadisticas = $this->cotizacionService->obtenerEstadisticas();
            return view('cotizar.cotizaciones.index', compact('estadisticas'));
        } catch (Exception $e) {
            Log::error('Error al obtener cotizaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las cotizaciones: ' . $e->getMessage()
            ], 500);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = $this->cotizacionService->obtenerClientes();
        $estados = $this->cotizacionService->obtenerEstados();
        $consecutivo = $this->cotizacionService->obtenerConsecutivoDocumento('COT');
        $variable = 'crear';
        $cotizacion = null;
        return view('cotizar.cotizaciones.documento', compact('clientes', 'estados', 'consecutivo', 'variable', 'cotizacion'));
    }

    public function obtenerSucursales($terceroId)
    {
        try {
            $sucursales = $this->cotizacionService->obtenerSucursalesPorTercero($terceroId);
            return response()->json([
                'success' => true,
                'data' => $sucursales
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar sucursales: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las sucursales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCotizacionRequest $request)
    {
        try {
            $data = $request->validated();

            // Agregar automáticamente el user_id del usuario autenticado
            $data['user_id'] = auth()->id();

            $cotizacion = $this->cotizacionService->crearCotizacion($data);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización creada exitosamente.',
                    'data' => $cotizacion
                ]);
            }

            return redirect()->route('admin.cotizaciones.index')
                           ->with('success', 'Cotización creada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al crear cotización: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la cotización: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                        ->withErrors(['error' => 'Error al crear la cotización: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $cotizacion = $this->cotizacionService->obtenerCotizacionPorId($id);

            if (!$cotizacion) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cotización no encontrada.'
                    ], 404);
                }

                return redirect()->route('admin.cotizaciones.index')
                               ->withErrors(['error' => 'Cotización no encontrada.']);
            }

            $clientes = $this->cotizacionService->obtenerClientes();
            $estados = $this->cotizacionService->obtenerEstados();
            $consecutivo = '';
            $variable = 'ver';
            return view('cotizar.cotizaciones.documento', compact('clientes', 'estados', 'cotizacion', 'consecutivo', 'variable'));
        } catch (\Exception $e) {
            Log::error('Error al mostrar cotización: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar la cotización: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.cotizaciones.index')
                           ->withErrors(['error' => 'Error al cargar la cotización.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $cotizacion = $this->cotizacionService->obtenerCotizacionPorId($id);

            if (!$cotizacion) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cotización no encontrada.'
                    ], 404);
                }
                return redirect()->route('admin.cotizaciones.index')
                               ->withErrors(['error' => 'Cotización no encontrada.']);
            }

            $clientes = $this->cotizacionService->obtenerClientes();
            $estados = $this->cotizacionService->obtenerEstados();
            $variable = 'editar';
            $consecutivo = '';
            return view('cotizar.cotizaciones.documento', compact('cotizacion', 'clientes', 'estados', 'variable', 'consecutivo','cotizacion'));

        } catch (\Exception $e) {
            Log::error('Error al cargar cotización para editar: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar la cotización: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.cotizaciones.index')
                           ->withErrors(['error' => 'Error al cargar la cotización.']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCotizacionRequest $request, $id)
    {
        try {
            $cotizacion = $this->cotizacionService->actualizarCotizacion($id, $request->validated());

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización actualizada exitosamente.',
                    'data' => $cotizacion
                ]);
            }

            return redirect()->route('admin.cotizaciones.index')
                           ->with('success', 'Cotización actualizada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar cotización: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la cotización: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                        ->withErrors(['error' => 'Error al actualizar la cotización: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->cotizacionService->eliminarCotizacion($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización eliminada exitosamente.'
                ]);
            }

            return redirect()->route('admin.cotizaciones.index')
                           ->with('success', 'Cotización eliminada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar cotización: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la cotización: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.cotizaciones.index')
                           ->withErrors(['error' => 'Error al eliminar la cotización.']);
        }
    }

    /**
     * Get sucursales by tercero ID
     */
    public function getSucursales($terceroId)
    {
        try {
            $sucursales = $this->cotizacionService->obtenerSucursalesPorTercero($terceroId);

            return response()->json([
                'success' => true,
                'data' => $sucursales
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar sucursales: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las sucursales: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAutorizaciones()
    {
        try {
            $autorizaciones = $this->cotizacionService->obtenerAutorizaciones();

            return response()->json([
                'success' => true,
                'data' => $autorizaciones
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar autorizaciones: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las autorizaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get contactos by tercero ID
     */
    public function getContactos($terceroId)
    {
        try {
            $contactos = $this->cotizacionService->obtenerContactosPorTercero($terceroId);

            return response()->json([
                'success' => true,
                'data' => $contactos
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar contactos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los contactos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate next document number
     */
    public function generateNextNumber()
    {
        try {
            $nextNumber = $this->cotizacionService->generarNumeroDocumento();

            return response()->json([
                'success' => true,
                'number' => $nextNumber
            ]);

        } catch (\Exception $e) {
            Log::error('Error al generar número de documento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el número de documento.'
            ], 500);
        }
    }

    /**
     * Duplicate cotizacion
     */
    public function duplicate($id)
    {
        try {
            $nuevaCotizacion = $this->cotizacionService->duplicarCotizacion($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización duplicada exitosamente.',
                    'data' => $nuevaCotizacion
                ]);
            }

            return redirect()->route('admin.cotizaciones.edit', $nuevaCotizacion->id)
                           ->with('success', 'Cotización duplicada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al duplicar cotización: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al duplicar la cotización: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.cotizaciones.index')
                           ->withErrors(['error' => 'Error al duplicar la cotización.']);
        }
    }

    /**
     * Search cotizaciones
     */
    public function search(Request $request)
    {
        try {
            $criterios = $request->only(['num_documento', 'tercero_id', 'estado_id', 'fecha_desde', 'fecha_hasta']);
            $cotizaciones = $this->cotizacionService->buscarCotizaciones($criterios);

            return response()->json([
                'success' => true,
                'data' => $cotizaciones
            ]);

        } catch (\Exception $e) {
            Log::error('Error al buscar cotizaciones: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar cotizaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar PDF de cotización
     */
    public function generatePdf($id)
    {
        try {
            $cotizacion = $this->pdfService->cargar($id);
            Log::info('Generando PDF de cotización', ['cotizacion_id' => $id]);
            return $this->pdfService->build($cotizacion)->download($this->pdfService->filename($cotizacion));
        } catch (\Throwable $e) {
            Log::error('Error al generar PDF de cotización', ['cotizacion_id' => $id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible generar el PDF de la cotización.');
        }
    }

    /**
     * Preview PDF de cotización
     */
    public function previewPdf($id)
    {
        try {
            $cotizacion = $this->pdfService->cargar($id);
            Log::info('Generando preview PDF', ['cotizacion_id' => $id]);
            return $this->pdfService->build($cotizacion)->stream($this->pdfService->filename($cotizacion));
        } catch (\Throwable $e) {
            Log::error('Error al previsualizar PDF de cotización', ['cotizacion_id' => $id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible generar la vista previa del PDF.');
        }
    }

    /**
     * Enviar cotización por correo al cliente y cambiar estado a "Enviado".
     */
    public function enviarPorCorreo($id)
    {
        try {
            $cotizacion = $this->pdfService->cargar($id);

            // Determinar destinatario
            $destinatario = $cotizacion->terceroContacto->correo
                ?? $cotizacion->tercero->correo
                ?? null;

            if (!$destinatario) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente no tiene correo electrónico registrado.',
                ], 422);
            }

            // Generar token único para aprobación
            $token = Str::random(64);

            // Calcular expiración: fecha_vencimiento de la cotización o parámetro COT_TOKEN_DIAS
            $diasToken = $this->parametros->getInt('COT_TOKEN_DIAS', 30);
            $expiracion = $cotizacion->fecha_vencimiento
                ? $cotizacion->fecha_vencimiento->endOfDay()
                : now()->addDays($diasToken);

            // Obtener estado "Enviado"
            $estadoEnviado = EstadoCotizacion::where('estado', 'Enviado')->first();

            // Actualizar cotización
            $cotizacion->update([
                'token_aprobacion' => $token,
                'token_expira_en'  => $expiracion,
                'fecha_envio'      => now(),
                'estado_id'        => $estadoEnviado?->id ?? $cotizacion->estado_id,
            ]);

            // Enviar email con PDF adjunto (aplica config SMTP de la empresa)
            $this->parametros->configurarMailer($cotizacion->company_id);
            Mail::to($destinatario)->send(new CotizacionEnviada($cotizacion, $this->pdfService));

            Log::info('Cotización enviada por correo', [
                'cotizacion_id' => $id,
                'destinatario'  => $destinatario,
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Cotización enviada exitosamente a ' . $destinatario,
                'destinatario' => $destinatario,
                'estado'       => $estadoEnviado?->estado ?? 'Enviado',
                'estado_color' => $estadoEnviado?->color ?? '#17a2b8',
            ]);

        } catch (\Throwable $e) {
            Log::error('Error al enviar cotización por correo', ['cotizacion_id' => $id, 'message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la cotización: ' . $e->getMessage(),
            ], 500);
        }
    }
}
