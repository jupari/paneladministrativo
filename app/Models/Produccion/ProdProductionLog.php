<?php

namespace App\Models\Produccion;

use Illuminate\Database\Eloquent\Model;

class ProdProductionLog extends Model
{
    protected $table = 'prod_production_logs';

    protected $fillable = [
        'company_id','order_id','order_operation_id','employee_id','work_date','shift',
        'qty','rejected_qty','notes','created_by'
    ];
}
