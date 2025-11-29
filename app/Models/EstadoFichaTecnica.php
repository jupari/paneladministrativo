<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoFichaTecnica extends Model
{
    use HasFactory;
    protected $table = 'estados_fichas_tecnicas';
    protected $fillable = ['nombre','color', 'active'];
}
