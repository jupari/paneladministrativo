<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs_files extends Model
{
    use HasFactory;

    protected $table = "logs_files";
    protected $fillable =  [
        'dato1',
        'dato2',
        'dato3',
        'dato4',
        'dato5',
        'dato6',
        'dato7',
        'dato8',
        'dato9',
        'dato10',
        'lote',
        'accused',
    ];

    public $timestamps = false;

}
