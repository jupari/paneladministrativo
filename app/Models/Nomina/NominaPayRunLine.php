<?php

namespace App\Models\Nomina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NominaPayRunLine extends Model
{
    protected $table = 'nomina_pay_run_lines';

    protected $fillable = [
        'pay_run_id',
        'participant_type',
        'participant_id',
        'link_type',          // LABORAL | CONTRATISTA
        'nomina_concept_id',
        'quantity',
        'base_amount',
        'rate',
        'amount',
        'direction',          // ADD | SUB
        'source',             // ENGINE | NOVELTY | MANUAL
        'notes',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function payRun(): BelongsTo
    {
        return $this->belongsTo(NominaPayRun::class, 'pay_run_id');
    }

    public function concept(): BelongsTo
    {
        return $this->belongsTo(NominaConcept::class, 'nomina_concept_id');
    }
}
