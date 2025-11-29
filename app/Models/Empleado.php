<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class
Empleado extends Model
{
    /**
     * @var array
     */

     use HasFactory;

     protected $table ='empleados';
     public $timestamps=false;

    protected $fillable = [
        'nombres'
        ,'apellidos'
        ,'tipo_identificacion_id'
        ,'identificacion'
        ,'expedida_en'
        ,'fecha_nacimiento'
        ,'fecha_inicio_labor'
        ,'fecha_finalizacion_contrato'
        ,'direccion'
        ,'ciudad_residencia'
        ,'telefono'
        ,'celular'
        ,'correo'
        ,'cliente_id'
        ,'sucursal_id'
        ,'cargo_id'
        ,'tipo_contrato'
        ,'ubicacion'
        ,'salario'
        ,'active'
        ,'created_at'
        ,'updated_at'
        ,'user_id'];

    public function cargo(){
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function tipoContrato(){
        return $this->belongsTo(TipoContrato::class, 'tipo_contrato_id');
    }

    public function cliente(){
        return $this->belongsTo(Tercero::class, 'cliente_id');
    }

    protected $casts = [
        'created_at' => 'datetime', // Esto asegura que Laravel trate el campo como un objeto Carbon.
        'updated_at' => 'datetime',
        'fecha_nacimiento'=>'datetime',
        'fecha_inicio_labor'=>'datetime',
        'fecha_finalizacion_contrato'=>'datetime',
    ];
}
