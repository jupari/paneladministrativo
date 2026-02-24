<?php

namespace App\Models\Nomina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NominaPayRun extends Model
{
    protected $table = 'nomina_pay_runs';

    protected $fillable = [
        'company_id',
        'run_type',      // MIXTO | NOMINA | CONTRATISTAS
        'period_start',
        'period_end',
        'pay_date',
        'status',        // DRAFT | CALCULATED | APPROVED | PAID | CLOSED
        'created_by',
        'approved_by',
        'notes',
        'meta',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'pay_date' => 'date',
        'meta' => 'array',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(NominaPayRunParticipant::class, 'pay_run_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(NominaPayRunLine::class, 'pay_run_id');
    }
}
