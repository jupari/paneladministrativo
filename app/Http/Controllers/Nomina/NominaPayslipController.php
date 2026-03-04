<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Nomina\NominaPayRun;
use App\Models\Nomina\NominaPayRunLine;
use App\Models\Nomina\NominaPayRunParticipant;
use App\Models\Tercero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NominaPayslipController extends Controller
{
    public function show(Request $request, NominaPayRun $payRun, string $participantType, int $participantId)
    {

        try {
            $companyId = (int) session('company_id');
            // Seguridad: payrun de la empresa actual
            if ((int)$payRun->company_id !== $companyId) {
                abort(403);
            }
            if($participantType=='CONTRATISTAS'){
                $participantType = 'CONTRATISTA';
            }
            // Resolver participant_type a lo que guardas en DB
            $dbParticipantType = $this->mapParticipantType($participantType);
            //dd($dbParticipantType);
            $participant = NominaPayRunParticipant::query()
                ->where('pay_run_id', $payRun->id)
                ->where('participant_type', $dbParticipantType)
                ->where('participant_id', $participantId)
                ->where('link_type', $participantType)
                ->firstOrFail();
            //dd($participant->toArray());
            $lines = NominaPayRunLine::query()
                ->where('pay_run_id', $payRun->id)
                ->where('participant_type', $dbParticipantType)
                ->where('participant_id', $participantId)
                ->with('concept:id,code,name,kind,tax_nature') // ajusta relación si existe
                // ->orderBy('direction') // ADD / SUB
                // ->orderBy('nomina_concept_id')
                ->get();
            // dd($lines->toArray());
            $devengados = $lines->where('direction','ADD');
            $deducciones = $lines->where('direction','SUB');

            Log::info('Líneas de nómina para comprobante de pago', [
                'devengados_count' => json_encode($devengados->toArray()),
                'deducciones_count' => json_encode($deducciones->toArray()),
            ]);

            $devSalarial = $devengados->filter(function($l){
                return optional($l->concept)->tax_nature === 'SALARIAL';
            });

            $devNoSalarial = $devengados->filter(function($l){
                return optional($l->concept)->tax_nature === 'NO_SALARIAL';
            });
            Log::info('Cálculo de comprobante de pago', [
                'pay_run_id' => $payRun->id,
                'participant_type' => $participantType,
                'participant_id' => $participantId,
                'devengados_count' => $devengados->count(),
                'deducciones_count' => $deducciones->count(),
                'devSalarial_count' => $devSalarial->count(),
                'devNoSalarial_count' => $devNoSalarial->count(),
            ]);
            $totDevSal = round((float)$devSalarial->sum('amount'), 2);
            $totDevNoSal = round((float)$devNoSalarial->sum('amount'), 2);
            $totDev = round($totDevSal + $totDevNoSal, 2);
            $totDed = round((float)$deducciones->sum('amount'), 2);
            $neto = round($totDev - $totDed, 2);
            // Participante (Empleado)
            $empleado = null;
            if ($dbParticipantType === Empleado::class) {
                $empleado = Empleado::find($participantId);
            }

            if ($dbParticipantType === Tercero::class) {
                $empleado = Empleado::find($participantId);
            }
            Log::info('Generando comprobante de pago', [
                'pay_run_id' => $payRun->id,
                'participant_type' => $participantType,
                'participant_id' => $participantId,
                'totDev' => $totDev,
                'totDed' => $totDed,
                'neto' => $neto,
                'participant_exists' => $participant !== null,
                'empleado_exists' => $empleado !== null,
            ]);

            return view('nomina.payslips.show', [
                    'payRun' => $payRun,
                    'participant' => $participant,
                    'empleado' => $empleado,
                    'devSalarial' => $devSalarial,
                    'devNoSalarial' => $devNoSalarial,
                    'deducciones' => $deducciones,
                    'totDevSal' => $totDevSal,
                    'totDevNoSal' => $totDevNoSal,
                    'totDev' => $totDev,
                    'totDed' => $totDed,
                    'neto' => $neto,
                ]);

        } catch (\Throwable $th) {
            Log::error('Error al generar comprobante de pago', [
                'pay_run_id' => $payRun->id,
                'participant_type' => $participantType,
                'participant_id' => $participantId,
                'error' => $th->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error al generar el comprobante de pago',
                'error' => $th->getMessage()
            ], 500);
        }

    }

    private function mapParticipantType(string $type): string
    {
        return match (strtolower($type)) {
            'empleado', 'employee' => \App\Models\Empleado::class,
            'tercero', 'contractor', 'contratista','CONTRATISTA' => \App\Models\Tercero::class,
            default => $type, // por si ya viene como clase completa
        };
    }
}
