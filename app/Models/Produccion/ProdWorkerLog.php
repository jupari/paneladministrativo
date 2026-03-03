<?php

namespace App\Models\Produccion;

use Illuminate\Database\Eloquent\Model;

class ProdWorkerLog extends Model
{
    protected $table = 'prod_worker_logs';
    public $timestamps = false;
    protected $fillable = [
        'company_id',
        'order_id' ,
        'order_operation_id',
        'operation_id' ,
        'employee_id' ,
        'qty' ,
        'worked_at' ,
        'notes' ,
        'created_by' ,
        'created_at' ,
        'updated_at'
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'worked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(ProdOrder::class, 'order_id');
    }

    public function settlement()
    {
        return $this->hasOne(ProdWorkerSettlement::class, 'order_operation_id', 'order_operation_id');
    }
}
