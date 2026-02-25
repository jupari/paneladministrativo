<?php

namespace App\Models\Nomina;

use Illuminate\Database\Eloquent\Model;

class NominaNovelty extends Model
{
    protected $table = 'nomina_novelties';

    protected $fillable = [
        'company_id',
        'participant_type',
        'participant_id',
        'link_type',
        'nomina_concept_id',
        'period_start',
        'period_end',
        'quantity',
        'amount',
        'description',
        'status',          // PENDING | APPLIED | CANCELLED
        'support_file_id',
        'meta',
        'pay_run_id',     // nullable, se llena al aplicar a un payrun específico
        'source_ref',     // referencia al origen de la novedad (ej: id de una incidencia, o un registro externo)
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'meta' => 'array',
    ];
}
