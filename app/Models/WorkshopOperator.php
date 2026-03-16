<?php

namespace App\Models;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopOperator extends Model
{
    protected $fillable = ['workshop_id', 'name', 'code', 'is_active', 'employee_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'employee_id');
    }
}
