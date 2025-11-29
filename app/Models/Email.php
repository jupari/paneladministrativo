<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    // Indica que este modelo no usa las convenciones de tabla de base de datos de Laravel
    public $incrementing = false;
    public $timestamps = false;

    // Definir las propiedades del mensaje de correo
    protected $fillable = [
        'id',
        'subject',
        'body',
        'bodyPreview',
        'from',
        'sender',
        'toRecipients',
        'ccRecipients',
        'bccRecipients',
        'isRead',
        'receivedDateTime',
        'sentDateTime',
        'internetMessageId',
        'conversationId',
        'hasAttachments'
    ];

    // Convertir atributos JSON a array
    protected $casts = [
        'from' => 'array',
        'sender' => 'array',
        'toRecipients' => 'array',
        'ccRecipients' => 'array',
        'bccRecipients' => 'array',
        'hasAttachments' => 'boolean',
    ];
}
