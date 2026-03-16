<?php

namespace App\Http\Resources\Api;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ProductionOrderResource extends JsonResource
{
    private const STATUS_MAP = [
        'DRAFT'       => 'pending',
        'IN_PROGRESS' => 'in_progress',
        'CLOSED'      => 'completed',
        'CANCELLED'   => 'cancelled',
    ];

    public function toArray(Request $request): array
    {
        $damagedUnits = (int) $this->damagedGarments()->sum('quantity');

        // Progreso por actividad
        $progressByActivity = DB::table('production_operations')
            ->select('activity_id', DB::raw('SUM(quantity) as total'))
            ->where('production_order_id', $this->id)
            ->groupBy('activity_id')
            ->pluck('total', 'activity_id');

        // Actividades requeridas (asignadas explícitamente)
        $requiredActivities = $this->requiredActivities;
        $hasExplicit = $requiredActivities !== null && $requiredActivities->isNotEmpty();

        if ($hasExplicit) {
            $activitiesList = $requiredActivities->map(fn ($activity) => [
                'activity_id'   => $activity->id,
                'code'          => $activity->code,
                'name'          => $activity->name,
                'position'      => $activity->pivot->position,
                'completed_qty' => (int) ($progressByActivity[$activity->id] ?? 0),
                'total_qty'     => (int) $this->total_units,
            ]);
        } else {
            // Sin actividades asignadas: mostrar todas las que tienen operaciones registradas
            $activityIds = $progressByActivity->keys();
            $activities  = $activityIds->isNotEmpty()
                ? Activity::whereIn('id', $activityIds)->get()->keyBy('id')
                : collect();

            $pos = 0;
            $activitiesList = $activityIds->map(function ($actId) use ($activities, $progressByActivity, &$pos) {
                $activity = $activities[$actId] ?? null;
                return [
                    'activity_id'   => (int) $actId,
                    'code'          => $activity?->code ?? '',
                    'name'          => $activity?->name ?? "Actividad #{$actId}",
                    'position'      => $pos++,
                    'completed_qty' => (int) ($progressByActivity[$actId] ?? 0),
                    'total_qty'     => (int) $this->total_units,
                ];
            })->values();
        }

        return [
            'id'                    => $this->id,
            'workshop_id'           => $this->workshop_id,
            'order_code'            => $this->order_code,
            'garment_type'          => $this->garment_type,
            'garment_reference'     => $this->garment_reference,
            'color'                 => $this->color,
            'total_units'           => (int) $this->total_units,
            'completed_units'       => (int) $this->completed_units,
            'damaged_units'         => $damagedUnits,
            'status'                => self::STATUS_MAP[$this->status] ?? $this->status,
            'cost_per_unit'         => (float) $this->cost_per_unit,
            'start_date'            => $this->start_date?->toIso8601String(),
            'deadline'              => $this->deadline?->toIso8601String(),
            'completed_at'          => $this->completed_at?->toIso8601String(),
            'required_activities'   => $activitiesList,
        ];
    }
}
