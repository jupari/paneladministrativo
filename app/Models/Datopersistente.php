<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datopersistente extends Model
{
    use HasFactory;

    protected $table = 'datopersistente';
    public $timestamps=false;
    protected $fillable=[
            'dato',
            'oauthState'
    ];
}
