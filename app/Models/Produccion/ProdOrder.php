<?php

namespace App\Models\Produccion;

use Illuminate\Database\Eloquent\Model;

class ProdOrder extends Model
{
    protected $table = 'prod_orders';

    protected $fillable = [
        'company_id','code','product_id','objective_qty','start_date','end_date','status','notes','created_by'
    ];
}
