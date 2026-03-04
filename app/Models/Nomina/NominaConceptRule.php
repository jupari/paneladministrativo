<?php

namespace App\Models\Nomina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NominaConceptRule extends Model
{
    protected $table = 'nomina_concept_rules';

    protected $fillable = [
        'nomina_concept_id',
        'link_type',   // LABORAL | CONTRATISTA
        'conditions',  // JSON
        'parameters',  // JSON { rate: 0.04 ... }
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'conditions' => 'array',
        'parameters' => 'array',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function concept(): BelongsTo
    {
        return $this->belongsTo(NominaConcept::class, 'nomina_concept_id');
    }
}
