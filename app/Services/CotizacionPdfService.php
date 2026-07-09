<?php

namespace App\Services;

use App\Models\Cotizacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Illuminate\Support\Str;

class CotizacionPdfService
{
    public function __construct(
        protected ParametroService $parametros,
    ) {}

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
            'items',
            'items.subitems.productos.listas',
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
        $diasVencimiento = $this->parametros->getInt('COT_DIAS_VENCIMIENTO', 30, $cotizacion->company_id);

        $pdf = Pdf::loadView('pdf.cotizacion', [
            'cotizacion' => $cotizacion,
            'logoBase64' => $logoBase64,
            'diasVencimiento' => $diasVencimiento,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        // El total de páginas solo se conoce una vez renderizado el documento
        // completo, así que hay que renderizar aquí antes de poder dibujar la
        // numeración. output()/stream() detectan que ya está renderizado y no
        // vuelven a hacerlo (ver Barryvdh\DomPDF\PDF::output()).
        $pdf->render();
        $this->agregarNumeracionPaginas($pdf);

        return $pdf;
    }

    /**
     * Dibuja "Página X de Y" en cada página del PDF usando el mecanismo nativo
     * de dompdf (Canvas::page_script), que debe invocarse DESPUÉS de renderizar
     * el documento para que recorra todas las páginas ya generadas. No se puede
     * resolver con CSS: dompdf no implementa los contadores `counter(page)` /
     * `counter(pages)` de CSS Paged Media (por eso el PDF mostraba "de 0").
     */
    private function agregarNumeracionPaginas(\Barryvdh\DomPDF\PDF $pdf): void
    {
        $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $size = 8;

        $pdf->getDomPDF()->getCanvas()->page_script(
            function (int $pageNumber, int $pageCount, Canvas $canvas, FontMetrics $fontMetrics) use ($font, $size) {
                $texto = "Página {$pageNumber} de {$pageCount}";
                $anchoTexto = $fontMetrics->getTextWidth($texto, $font, $size);

                // Margen derecho ~25px de la hoja (igual que @page margin) y
                // dentro del área reservada para el pie de página.
                $x = $canvas->get_width() - $anchoTexto - 25;
                $y = $canvas->get_height() - 30;

                $canvas->text($x, $y, $texto, $font, $size, [0, 0, 0]);
            }
        );
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
        return 'Cotizacion_'.($cotizacion->num_documento ?? $cotizacion->id).'.pdf';
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
                return 'data:image/png;base64,'.base64_encode(file_get_contents($companyLogo));
            }
        }

        $defaultLogo = storage_path('app/public/companies/logos/logo-minduval.png');

        if (is_file($defaultLogo)) {
            return 'data:image/png;base64,'.base64_encode(file_get_contents($defaultLogo));
        }

        return null;
    }
}
