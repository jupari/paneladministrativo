<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCotizacionRequest;
use App\Http\Requests\UpdateCotizacionRequest;
use App\Services\CotizacionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Cotizacion;

class CotizacionController extends Controller
{
    protected $cotizacionService;

    public function __construct(CotizacionService $cotizacionService)
    {
        $this->cotizacionService = $cotizacionService;
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
                        if ($row->estado) {
                            return '<span class="badge bg-' . $row->estado->color . '">' . $row->estado->estado . '</span>';
                        }
                        return '<span class="badge badge-secondary">Sin estado</span>';
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
                    ->rawColumns(['id','num_documento','cliente','fecha','estado','total','actions'])
                    ->make(true);
            }
            // $estadisticas = $this->cotizacionService->obtenerEstadisticas();
            return view('cotizar.cotizaciones.index');
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
            $cotizacion = Cotizacion::with([
                'tercero.company',
                'terceroSucursal',
                'terceroContacto',
                'productos.producto',
                'items',
                'estado',
                'usuario',
            ])->findOrFail($id);

            Log::info('Generando PDF de cotización', [
                'cotizacion_id' => $id
            ]);

            // ================= LOGO =================
            $logoBase64 = null;

            // Logo por empresa
            if ($cotizacion->tercero && $cotizacion->tercero->company) {
                $slug = \Illuminate\Support\Str::slug(
                    $cotizacion->tercero->company->nombre
                );

                $companyLogo = storage_path("app/public/companies/logos/logo-{$slug}.png");

                if (is_file($companyLogo)) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(
                        file_get_contents($companyLogo)
                    );
                }
            }

            // Logo por defecto
            if (!$logoBase64) {
                $defaultLogo = storage_path('app/public/companies/logos/logo-minduval.png');

                if (is_file($defaultLogo)) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(
                        file_get_contents($defaultLogo)
                    );
                }
            }

            Log::info('Logo cargado', [
                'logo' => (bool) $logoBase64
            ]);

            // ================= PDF =================
            $pdf = Pdf::loadView('pdf.cotizacion', [
                'cotizacion' => $cotizacion,
                'logoBase64' => $logoBase64,
            ]);

            $pdf->setPaper('A4', 'portrait');

            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

            $filename = 'Cotizacion_' . ($cotizacion->num_documento ?? $id) . '.pdf';

            return $pdf->download($filename);

        } catch (\Throwable $e) {

            Log::error('Error al generar PDF de cotización', [
                'cotizacion_id' => $id,
                'message'       => $e->getMessage(),
                'file'          => $e->getFile(),
                'line'          => $e->getLine(),
            ]);

            return back()->with(
                'error',
                'No fue posible generar el PDF de la cotización.'
            );
        }
    }

    /**
     * Preview PDF de cotización
     */
    public function previewPdf($id)
    {
        try {
            $cotizacion = Cotizacion::with([
                'tercero.company',
                'terceroSucursal',
                'terceroContacto',
                'productos.producto',
                'items',
                'estado',
                'usuario',
            ])->findOrFail($id);

            Log::info("Generando preview PDF", ['cotizacion_id' => $id]);

            // ================= LOGO =================
            $logoBase64 = null;

            // 1. Logo por empresa (si existe)
            if ($cotizacion->tercero && $cotizacion->tercero->company) {
                $slug = \Illuminate\Support\Str::slug(
                    $cotizacion->tercero->company->nombre
                );

                $companyLogo = storage_path("app/public/companies/logos/logo-{$slug}.png");

                if (is_file($companyLogo)) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($companyLogo));
                }
            }

            // 2. Logo por defecto
            if (!$logoBase64) {
                $defaultLogo = storage_path('app/public/companies/logos/logo-minduval.png');

                if (is_file($defaultLogo)) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($defaultLogo));
                }
            }

            Log::info('Logo cargado', ['ok' => (bool) $logoBase64]);

            // ================= PDF =================
            $pdf = Pdf::loadView('pdf.cotizacion', [
                'cotizacion' => $cotizacion,
                'logoBase64' => $logoBase64,
            ]);

            $pdf->setPaper('A4', 'portrait');

            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

            return $pdf->stream(
                'Cotizacion_' . ($cotizacion->num_documento ?? $id) . '.pdf'
            );

        } catch (\Throwable $e) {

            Log::error('Error al previsualizar PDF de cotización', [
                'cotizacion_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with(
                'error',
                'No fue posible generar la vista previa del PDF.'
            );
        }
    }
}
