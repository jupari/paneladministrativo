<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelLang\Publisher\Concerns\Has;

/**
 * @property integer $id
 * @property string $texto
 * @property boolean $active
 */
class OrdObservacion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    use HasFactory;
    protected $table = 'ord_observaciones';

    /**
     * @var array
     */
    protected $fillable = ['texto', 'active'];
}
