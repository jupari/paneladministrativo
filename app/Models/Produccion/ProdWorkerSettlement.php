<?php

namespace App\Models\Produccion;

use Illuminate\Database\Eloquent\Model;

class ProdWorkerSettlement extends Model
{
    protected $table = 'prod_worker_settlements';

    protected $fillable = [
        'company_id','order_id','order_operation_id','employee_id','qty','rate','gross_amount','status'
    ];
}
