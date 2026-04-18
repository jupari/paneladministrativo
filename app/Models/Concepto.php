<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    use HasFactory;

    protected $table = 'conceptos';

    protected $fillable = [
        'nombre',
        'tipo',
        'porcentaje_defecto',
        'active'
    ];

    protected $casts = [
        'porcentaje_defecto' => 'decimal:2',
        'activo' => 'boolean',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Aliases de tipo — fuente única de verdad para clasificar conceptos
    // ─────────────────────────────────────────────────────────────────────────

    public const ALIASES_DESCUENTO = ['DESCUENTO', 'DISCOUNT', 'DES', 'DESC'];
    public const ALIASES_IMPUESTO  = ['IMPUESTO', 'IVA', 'TAX', 'IMP'];
    public const ALIASES_RETENCION = ['RETENCION', 'RETENTION', 'RET', 'RETE'];

    public static function esDescuento(string $tipo): bool
    {
        return in_array(strtoupper(trim($tipo)), self::ALIASES_DESCUENTO, true);
    }

    public static function esImpuesto(string $tipo): bool
    {
        return in_array(strtoupper(trim($tipo)), self::ALIASES_IMPUESTO, true);
    }

    public static function esRetencion(string $tipo): bool
    {
        return in_array(strtoupper(trim($tipo)), self::ALIASES_RETENCION, true);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relaciones
    // ─────────────────────────────────────────────────────────────────────────

    public function cotizacionConceptos()
    {
        return $this->hasMany(CotizacionConcepto::class, 'concepto_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
