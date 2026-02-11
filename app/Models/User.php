<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'identificacion',
        'company_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relación con Company
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Métodos para verificar empresa y licencia
    public function hasValidCompanyLicense(): bool
    {
        return $this->company && $this->company->isLicenseValid();
    }

    public function getCompanyName(): ?string
    {
        return $this->company ? $this->company->name : null;
    }

    public function getCompanyLogo(): ?string
    {
        return $this->company ? $this->company->getLogoUrl() : null;
    }

    public function getCompanyPrimaryColor(): string
    {
        return $this->company ? $this->company->primary_color : '#007bff';
    }

    // Scopes
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeWithValidLicense($query)
    {
        return $query->whereHas('company', function($q) {
            $q->withValidLicense();
        });
    }

    public function adminlte_image(){
        return 'https://picsum.photos/300/300';
    }

    public function adminlte_desc(){
        // Obtén los nombres de los roles del usuario
        $roles = $this->getRoleNames();

        // Asumiendo que un usuario tiene un solo rol, puedes obtener el primer rol
        $roleName = $roles->isNotEmpty() ? $roles->first() : 'Sin rol asignado';

        // Agregar nombre de empresa si existe
        $companyName = $this->getCompanyName();
        if ($companyName) {
            $roleName .= ' - ' . $companyName;
        }

        return $roleName;
    }

    public function adminlte_profile_url(){
        return 'user/profile';
    }
}
