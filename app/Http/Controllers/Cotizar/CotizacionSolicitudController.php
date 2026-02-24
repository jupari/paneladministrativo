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

class CotizacionSolicitudController extends Controller
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
                $cotizaciones = $this->cotizacionService->obtenerCotizacionesBorradorTerminado();
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
                    ->addColumn('autorizacion', function ($row) {
                        if ($row->autorizacion->nombre =='Autorizado') {
                            return '<span class="badge badge-success d-flex align-items-center" style="display: inline-flex !important; align-items: center; gap: 4px;">
                                        <i class="fas fa-lock"></i>
                                        <span>' . $row->autorizacion->nombre . '</span>
                                    </span>';
                        }
                        return '<span class="badge badge-warning d-flex align-items-center" style="display: inline-flex !important; align-items: center; gap: 4px;">
                                    <i class="fas fa-lock-open"></i>
                                    <span>' . $row->autorizacion->nombre . '</span>
                                </span>';
                    })
                    ->addColumn('total', function ($row) {
                        return '$' . number_format($row->total, 2);
                    })
                    ->addColumn('actions', function ($row) {
                        return '<div class="btn-group" role="group">
                                    <a type="button" class="btn btn-sm btn-info" href="' . route('admin.cotizaciones.show', $row->id) . '" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="autorizarCotizacion(' . $row->id . ')" title="Eliminar">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                </div>';
                    })
                    ->rawColumns(['id','num_documento','cliente','fecha','estado','autorizacion','total','actions'])
                    ->make(true);
            }
            // $estadisticas = $this->cotizacionService->obtenerEstadisticas();
            return view('cotizar.solicitudes.index');
        } catch (Exception $e) {
            Log::error('Error al obtener cotizaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las cotizaciones: ' . $e->getMessage()
            ], 500);
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

    public function authorizeCotizacion($id)
    {
        try {

            $this->cotizacionService->autorizarCotizacion($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización autorizada exitosamente.'
                ]);
            }

            return redirect()->route('cotizar.solicitudes.index')
                           ->with('success', 'Cotización autorizada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al autorizar cotización: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al autorizar la cotización: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('cotizar.solicitudes.index')
                           ->withErrors(['error' => 'Error al autorizar la cotización.']);
        }
    }
}
