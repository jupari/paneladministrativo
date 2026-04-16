<?php

namespace App\Services;

use App\Models\Cotizacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CotizacionPdfService
{
    /**
     * Carga la cotización con todas las relaciones necesarias para el PDF.
     */
    public function cargar(int $id): Cotizacion
    {
        return Cotizacion::with([
            'tercero.company',
            'terceroSucursal',
            'terceroContacto',
            'productos.producto',
            'productos.cotizacionItem',
            'productos.cotizacionSubItem',
            'items',
            'conceptos.concepto',
            'viaticos',
            'condicionesComerciales',
            'estado',
            'usuario',
        ])->findOrFail($id);
    }

    /**
     * Construye el objeto PDF (DomPDF) a partir de una cotización ya cargada.
     */
    public function build(Cotizacion $cotizacion): \Barryvdh\DomPDF\PDF
    {
        $logoBase64 = $this->resolverLogoBase64($cotizacion);

        $pdf = Pdf::loadView('pdf.cotizacion', [
            'cotizacion' => $cotizacion,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'defaultFont'          => 'DejaVu Sans',
        ]);

        return $pdf;
    }

    /**
     * Genera el PDF y retorna su contenido como string (para adjuntar a emails).
     */
    public function buildContent(Cotizacion $cotizacion): string
    {
        return $this->build($cotizacion)->output();
    }

    /**
     * Devuelve el nombre de archivo estándar para una cotización.
     */
    public function filename(Cotizacion $cotizacion): string
    {
        return 'Cotizacion_' . ($cotizacion->num_documento ?? $cotizacion->id) . '.pdf';
    }

    /**
     * Resuelve el logo en base64: primero el de la empresa, luego el por defecto.
     */
    public function resolverLogoBase64(Cotizacion $cotizacion): ?string
    {
        if ($cotizacion->tercero && $cotizacion->tercero->company) {
            $slug = Str::slug($cotizacion->tercero->company->nombre);
            $companyLogo = storage_path("app/public/companies/logos/logo-{$slug}.png");

            if (is_file($companyLogo)) {
                return 'data:image/png;base64,' . base64_encode(file_get_contents($companyLogo));
            }
        }

        $defaultLogo = storage_path('app/public/companies/logos/logo-minduval.png');

        if (is_file($defaultLogo)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($defaultLogo));
        }

        return null;
    }
}
