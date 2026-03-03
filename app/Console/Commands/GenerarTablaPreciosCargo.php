<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerarTablaPreciosCargo extends Command
{
    protected $signature = 'nomina:tabla-precios-cargo {--export= : Ruta para exportar a xlsx (opcional)}';
    protected $description = 'Genera/actualiza la tabla de precios por cargo usando Parametrización + Novedades';

    public function handle(\App\Services\TablaPreciosCargoService $service): int
    {
        $data = $service->generar(true);

        $this->info("OK. Cargos procesados: " . count($data));

        $export = $this->option('export');
        if ($export) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('TablaPrecios');

            $headers = [
                'CARGO','HORA ORDINARIA','RECARGO NOCTURNO','HORA EXTRA DIURNA','HORA EXTRA NOCTURNA',
                'HORA DOMINICAL','HORA EXTRA DOM (DIURNA)','HORA EXTRA DOM (NOCTURNA)','VALOR DIA ORDINARIO'
            ];

            $sheet->fromArray($headers, null, 'A1');

            $row = 2;
            foreach ($data as $r) {
                $sheet->fromArray([
                    $r['cargo'],
                    $r['hora_ordinaria'],
                    $r['recargo_nocturno'],
                    $r['hora_extra_diurna'],
                    $r['hora_extra_nocturna'],
                    $r['hora_dominical'],
                    $r['hora_extra_dominical_diurna'],
                    $r['hora_extra_dominical_nocturna'],
                    $r['valor_dia_ordinario'],
                ], null, "A{$row}");
                $row++;
            }

            (new Xlsx($spreadsheet))->save($export);
            $this->info("Exportado en: {$export}");
        }

        return self::SUCCESS;
    }
}
