<?php

namespace App\Services\Nomina;

use App\Models\Empleado;
use App\Models\Tercero;
use App\Models\Nomina\NominaPayRun;
use App\Models\Nomina\NominaPayRunParticipant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NominaPayRunService
{
    public function create(array $data, int $companyId, ?int $userId): NominaPayRun
    {
        try {
            return NominaPayRun::create([
                'company_id' => $companyId,
                'run_type' => $data['run_type'],
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'pay_date' => $data['pay_date'],
                'status' => 'DRAFT',
                'created_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (Exception $e) {
            Log::error('Error creando NominaPayRun', [
                'error' => $e->getMessage(),
                'data' => $data,
                'company_id' => $companyId,
                'user_id' => $userId
            ]);
            throw new Exception("Error creando NominaPayRun: " . $e->getMessage());
        }

    }

    public function update(NominaPayRun $payRun, array $data, ?int $userId): NominaPayRun
    {
        try {
            $payRun->update([
                'run_type' => $data['run_type'],
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'pay_date' => $data['pay_date'],
                'notes' => $data['notes'] ?? null,
                'updated_by' => $userId,
            ]);

            return $payRun;
        } catch (Exception $e) {
            log::error('Error actualizando NominaPayRun', [
                'error' => $e->getMessage(),
                'pay_run_id' => $payRun->id,
                'data' => $data,
                'user_id' => $userId
            ]);
            throw new Exception("Error actualizando NominaPayRun: " . $e->getMessage());
        }
    }

    // public function attachParticipants(NominaPayRun $payRun, bool $includeLaboral = true, bool $includeContratistas = true): int
    // {
    //     $inserted = 0;

    //     DB::transaction(function () use ($payRun, $includeLaboral, $includeContratistas, &$inserted) {

    //         if ($includeLaboral) {
    //             // Empleados activos (según tu tabla empleados: active=1)
    //             $empleados = Empleado::where('active', 1)
    //                 // (Opcional) filtrar por company/cliente si aplica
    //                 ->pluck('id');

    //             foreach ($empleados as $id) {
    //                 $row = NominaPayRunParticipant::firstOrCreate(
    //                     [
    //                         'pay_run_id' => $payRun->id,
    //                         'participant_type' => Empleado::class,
    //                         'participant_id' => $id,
    //                     ],
    //                     [
    //                         'link_type' => 'LABORAL',
    //                         'status' => 'PENDING',
    //                     ]
    //                 );
    //                 if ($row->wasRecentlyCreated) $inserted++;
    //             }
    //         }

    //         if ($includeContratistas) {
    //             // Contratistas: en tu proyecto lo natural es Tercero tipo PROVEEDOR (scopeProveedores())
    //             $terceros = Tercero::proveedores()
    //                 ->where('active', 1)
    //                 ->where('company_id', $payRun->company_id)
    //                 ->pluck('id');

    //             foreach ($terceros as $id) {
    //                 $row = NominaPayRunParticipant::firstOrCreate(
    //                     [
    //                         'pay_run_id' => $payRun->id,
    //                         'participant_type' => Tercero::class,
    //                         'participant_id' => $id,
    //                     ],
    //                     [
    //                         'link_type' => 'CONTRATISTA',
    //                         'status' => 'PENDING',
    //                     ]
    //                 );
    //                 if ($row->wasRecentlyCreated) $inserted++;
    //             }
    //         }
    //     });

    //     return $inserted;
    // }

    public function attachParticipants(NominaPayRun $payRun, bool $includeLaboral, bool $includeContratistas): int
    {
        $inserted = 0;
        Log::info('Iniciando attachParticipants', [
            'pay_run_id' => $payRun->id,
            'include_laboral' => $includeLaboral,
            'include_contratistas' => $includeContratistas
        ]);
        // ✅ EMPLEADOS (LABORAL)
        if ($includeLaboral) {
            $empleados = Empleado::query()
                ->where('active', 1)
                ->where('company_id', $payRun->company_id)
                ->pluck('id');

            foreach ($empleados as $id) {
                $row = NominaPayRunParticipant::firstOrCreate(
                    [
                        'pay_run_id' => $payRun->id,
                        'participant_type' => Empleado::class,
                        'participant_id' => $id,
                    ],
                    [
                        'link_type' => 'LABORAL',
                        'status' => 'PENDING',
                    ]
                );

                if ($row->wasRecentlyCreated) $inserted++;
            }
        }

        // ✅ CONTRATISTAS: Tercero con tercerotipo_id = 4
        if ($includeContratistas) {
            $terceros = Tercero::query()
                ->where('active', 1)
                ->where('company_id', $payRun->company_id)
                ->where('tercerotipo_id', 4)
                ->pluck('id');

            foreach ($terceros as $id) {
                $row = NominaPayRunParticipant::firstOrCreate(
                    [
                        'pay_run_id' => $payRun->id,
                        'participant_type' => Tercero::class,
                        'participant_id' => $id,
                    ],
                    [
                        'link_type' => 'CONTRATISTA',
                        'status' => 'PENDING',
                    ]
                );

                if ($row->wasRecentlyCreated) $inserted++;
            }
        }

        return $inserted;
    }
}
