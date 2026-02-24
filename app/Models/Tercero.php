<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $tercerotipo_id
 * @property integer $tipoidentificacion_id
 * @property string $identificacion
 * @property string $dv
 * @property integer $tipopersona_id
 * @property string $nombres
 * @property string $apellidos
 * @property string $nombre_estableciemiento
 * @property string $telefono
 * @property string $celular
 * @property string $correo
 * @property string $correo_fe
 * @property integer $ciudad_id
 * @property string $direccion
 * @property string $vendedor
 * @property string $created_at
 * @property string $updated_at
 * @property integer $user_id
 */
class Tercero extends Model
{
    /**
     * @var array
     */

     use HasFactory;

    protected $table='terceros';
    public $timestamps=false;

    protected $fillable = [
          'company_id'
        , 'tercerotipo_id'
        , 'tipoidentificacion_id'
        , 'identificacion'
        , 'dv'
        , 'tipopersona_id'
        , 'nombres'
        , 'apellidos'
        , 'nombre_establecimiento'
        , 'telefono'
        , 'celular'
        , 'correo'
        , 'correo_fe'
        , 'ciudad_id'
        , 'direccion'
        , 'vendedor_id'
        , 'created_at'
        , 'updated_at'
        , 'user_id'
        , 'active'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function terceroTipo()
    {
        return $this->belongsTo(TerceroTipo::class, 'tercerotipo_id');
    }

    public function tipoIdentificacion(){
        return $this->belongsTo(TipoIdentificacion::class, 'tipoidentificacion_id');
    }

    public function tipoPersona(){
        return $this->belongsTo(TipoPersona::class, 'tipopersona_id');
    }

    public function ciudad(){
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    public function vendedores(){
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function contactos(){
        return $this->hasMany(TerceroContacto::class, 'tercero_id');
    }

    public function sucursales(){
        return $this->hasMany(TerceroSucursal::class, 'tercero_id');
    }

    // Scopes para filtrar por tipo de tercero
    public function scopeClientes($query)
    {
        return $query->where('tercerotipo_id', 1);
    }

    public function scopeProveedores($query)
    {
        return $query->where('tercerotipo_id', 2);
    }

    public function scopeEmpleados($query)
    {
        return $query->where('tercerotipo_id', 3);
    }

    // Scope para filtrar por empresa
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function nominaPayRunParticipants()
    {
        return $this->morphMany(\App\Models\Nomina\NominaPayRunParticipant::class, 'participant', 'participant_type', 'participant_id');
    }

}
