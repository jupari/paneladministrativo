<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'company_id', 'code', 'name', 'description', 'unit_price', 'is_active',
        'legacy_prod_operation_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active'  => 'boolean',
    ];
}
