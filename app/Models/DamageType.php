<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageType extends Model
{
    protected $fillable = [
        'company_id', 'code', 'name', 'description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
