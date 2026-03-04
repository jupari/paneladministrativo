<?php

namespace App\Models\Nomina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NominaConcept extends Model
{
    protected $table = 'nomina_concepts';

    protected $fillable = [
        'code',
        'name',
        'scope',        // LABORAL | CONTRATISTA | AMBOS
        'kind',         // DEVENGADO | DEDUCCION | APORTE | INFORMATIVO
        'tax_nature',   // SALARIAL | NO_SALARIAL | N_A
        'calc_method',  // FIJO | PORCENTAJE | FORMULA | MANUAL
        'base_code',    // IBC | HON_BASE | etc
        'priority',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(NominaConceptRule::class, 'nomina_concept_id');
    }
}
