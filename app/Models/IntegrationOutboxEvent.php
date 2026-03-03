<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationOutboxEvent extends Model
{
    protected $table = 'integration_outbox_events';

    protected $fillable = [
        'company_id',
        'event_type',
        'event_key',
        'payload',
        'status',
        'retries',
        'next_retry_at',
        'sent_at',
        'last_error',
    ];

    protected $casts = [
        'payload' => 'array',
        'next_retry_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}

