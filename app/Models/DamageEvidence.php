<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DamageEvidence extends Model
{
    protected $fillable = ['damaged_garment_id', 'path', 'disk', 'size_bytes'];

    public function damagedGarment(): BelongsTo
    {
        return $this->belongsTo(DamagedGarment::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
