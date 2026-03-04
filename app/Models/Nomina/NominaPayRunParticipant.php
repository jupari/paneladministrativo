<?php

namespace App\Models\Nomina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NominaPayRunParticipant extends Model
{
    protected $table = 'nomina_pay_run_participants';

    protected $fillable = [
        'pay_run_id',
        'participant_type', // App\Models\Empleado | App\Models\Tercero
        'participant_id',
        'link_type',        // LABORAL | CONTRATISTA
        'status',           // PENDING | CALCULATED | EXCLUDED
        'gross_total',
        'deductions_total',
        'net_total',
        'employer_total',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function payRun(): BelongsTo
    {
        return $this->belongsTo(NominaPayRun::class, 'pay_run_id');
    }

    public function participant(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'participant_type', 'participant_id');
    }
}
