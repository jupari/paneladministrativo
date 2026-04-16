<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\EstadoCotizacion;
use App\Services\CotizacionPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CotizacionRespuestaController extends Controller
{
    public function __construct(protected CotizacionPdfService $pdfService) {}

    /**
     * Muestra la cotización en modo lectura con botones Aprobar / Rechazar.
     */
    public function mostrar(string $token)
    {
        $cotizacion = $this->buscarPorToken($token);

        if (!$cotizacion) {
            return view('public.cotizacion-respuesta', ['estado' => 'invalido']);
        }

        if ($cotizacion->fecha_respuesta) {
            return view('public.cotizacion-respuesta', [
                'estado'      => 'respondida',
                'cotizacion'  => $cotizacion,
            ]);
        }

        if ($cotizacion->token_expira_en && now()->isAfter($cotizacion->token_expira_en)) {
            return view('public.cotizacion-respuesta', [
                'estado'     => 'expirada',
                'cotizacion' => $cotizacion,
            ]);
        }

        return view('public.cotizacion-respuesta', [
            'estado'     => 'pendiente',
            'cotizacion' => $cotizacion,
        ]);
    }

    /**
     * Procesa la respuesta del cliente (aprobación o rechazo).
     */
    public function responder(Request $request, string $token)
    {
        $validated = $request->validate([
            'accion'         => ['required', 'in:aprobar,rechazar'],
            'motivo_rechazo' => ['required_if:accion,rechazar', 'nullable', 'string', 'max:1000'],
            'respondido_por' => ['required', 'string', 'max:200'],
        ]);

        $cotizacion = $this->buscarPorToken($token);

        if (!$cotizacion) {
            return redirect()->back()->withErrors(['error' => 'El enlace no es válido.']);
        }

        if ($cotizacion->fecha_respuesta) {
            return view('public.cotizacion-respondida', [
                'cotizacion' => $cotizacion,
                'mensaje'    => 'Esta cotización ya fue respondida anteriormente.',
            ]);
        }

        if ($cotizacion->token_expira_en && now()->isAfter($cotizacion->token_expira_en)) {
            return view('public.cotizacion-respondida', [
                'cotizacion' => $cotizacion,
                'mensaje'    => 'El plazo para responder esta cotización ha vencido.',
            ]);
        }

        $estadoNombre = $validated['accion'] === 'aprobar' ? 'Aprobado' : 'Rechazado';
        $estado = EstadoCotizacion::where('estado', $estadoNombre)->first();

        $cotizacion->update([
            'estado_id'      => $estado?->id ?? $cotizacion->estado_id,
            'fecha_respuesta' => now(),
            'respondido_por'  => $validated['respondido_por'],
            'motivo_rechazo'  => $validated['accion'] === 'rechazar' ? $validated['motivo_rechazo'] : null,
        ]);

        Log::info('Cliente respondió cotización', [
            'cotizacion_id'  => $cotizacion->id,
            'accion'         => $validated['accion'],
            'respondido_por' => $validated['respondido_por'],
        ]);

        return view('public.cotizacion-respondida', [
            'cotizacion' => $cotizacion,
            'accion'     => $validated['accion'],
            'mensaje'    => $validated['accion'] === 'aprobar'
                ? '¡Cotización aprobada! Nos pondremos en contacto con usted pronto.'
                : 'Cotización rechazada. Gracias por informarnos.',
        ]);
    }

    private function buscarPorToken(string $token): ?Cotizacion
    {
        return Cotizacion::with([
            'tercero',
            'terceroSucursal',
            'terceroContacto',
            'productos.cotizacionItem',
            'productos.cotizacionSubItem',
            'conceptos.concepto',
            'viaticos',
            'condicionesComerciales',
            'estado',
            'usuario',
        ])->where('token_aprobacion', $token)->first();
    }
}
