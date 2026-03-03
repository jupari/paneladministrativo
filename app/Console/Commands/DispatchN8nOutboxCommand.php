<?php

namespace App\Console\Commands;

use App\Jobs\ProcessN8nOutboxEventJob;
use App\Models\IntegrationOutboxEvent;
use Illuminate\Console\Command;

class DispatchN8nOutboxCommand extends Command
{
    protected $signature = 'integrations:n8n-outbox {--limit=100 : Cantidad maxima de eventos por corrida}';

    protected $description = 'Despacha eventos pendientes/fallidos del outbox hacia n8n.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $events = IntegrationOutboxEvent::query()
            ->whereIn('status', ['PENDING', 'FAILED'])
            ->where(function ($q) {
                $q->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get(['id']);

        if ($events->isEmpty()) {
            $this->line('No hay eventos por procesar.');
            return self::SUCCESS;
        }

        foreach ($events as $event) {
            ProcessN8nOutboxEventJob::dispatch((int) $event->id);
        }

        $this->info('Eventos despachados: ' . $events->count());
        return self::SUCCESS;
    }
}

