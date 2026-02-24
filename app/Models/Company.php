<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_name',
        'nit',
        'email',
        'phone',
        'address',
        'logo_path',
        'primary_color',
        'secondary_color',
        'theme_settings',
        'is_active',
        'license_expires_at',
        'license_type',
        'max_users',
        'features',
        'settings',
        'notes'
    ];

    protected $casts = [
        'theme_settings' => 'array',
        'features' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'license_expires_at' => 'date',
    ];

    // Relaciones
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function terceros(): HasMany
    {
        return $this->hasMany(Tercero::class);
    }

    // Métodos para gestión de licencias
    public function isLicenseValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->license_expires_at && $this->license_expires_at->lt(Carbon::now())) {
            return false;
        }

        return true;
    }

    public function daysUntilExpiration(): ?int
    {
        if (!$this->license_expires_at) {
            return null;
        }

        return Carbon::now()->diffInDays($this->license_expires_at, false);
    }

    public function isLicenseExpiringSoon(int $days = 30): bool
    {
        $daysUntilExpiration = $this->daysUntilExpiration();

        return $daysUntilExpiration !== null && $daysUntilExpiration <= $days && $daysUntilExpiration >= 0;
    }

    public function canAddMoreUsers(): bool
    {
        return $this->users()->count() < $this->max_users;
    }

    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return in_array($feature, $features);
    }

    // Métodos para configuración visual
    public function getLogoUrl(): ?string
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }

        return null;
    }

    public function getThemeSetting(string $key, $default = null)
    {
        $settings = $this->theme_settings ?? [];
        return $settings[$key] ?? $default;
    }

    public function getSetting(string $key, $default = null)
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithValidLicense($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('license_expires_at')
                          ->orWhere('license_expires_at', '>=', Carbon::now());
                    });
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('is_active', true)
                    ->whereNotNull('license_expires_at')
                    ->whereBetween('license_expires_at', [
                        Carbon::now(),
                        Carbon::now()->addDays($days)
                    ]);
    }

    // Mutadores
    public function setThemeSettingsAttribute($value)
    {
        $this->attributes['theme_settings'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setFeaturesAttribute($value)
    {
        $this->attributes['features'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = is_array($value) ? json_encode($value) : $value;
    }
}
