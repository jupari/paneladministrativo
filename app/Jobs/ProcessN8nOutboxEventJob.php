<?php

namespace App\Jobs;

use App\Models\IntegrationOutboxEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProcessN8nOutboxEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $eventId)
    {
    }

    public function handle(): void
    {
        $event = DB::transaction(function () {
            /** @var IntegrationOutboxEvent|null $row */
            $row = IntegrationOutboxEvent::query()
                ->lockForUpdate()
                ->find($this->eventId);

            if (!$row) {
                return null;
            }

            if ($row->status === 'SENT') {
                return null;
            }

            if ($row->next_retry_at && $row->next_retry_at->isFuture()) {
                return null;
            }

            $row->status = 'PROCESSING';
            $row->last_error = null;
            $row->save();

            return $row;
        });

        if (!$event) {
            return;
        }

        $url = (string) config('services.n8n.webhook_url');
        if ($url === '') {
            $this->markFailed($event, 'N8N webhook URL no configurada.');
            return;
        }

        $payloadJson = json_encode($event->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $headers = [];

        $token = (string) config('services.n8n.token');
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $secret = (string) config('services.n8n.signing_secret');
        if ($secret !== '' && $payloadJson !== false) {
            $headers['X-Signature'] = hash_hmac('sha256', $payloadJson, $secret);
        }

        $timeout = (int) config('services.n8n.timeout_seconds', 10);

        try {
            $response = Http::withHeaders($headers)
                ->acceptJson()
                ->contentType('application/json')
                ->timeout(max(1, $timeout))
                ->post($url, $event->payload ?? []);

            if ($response->successful()) {
                $event->update([
                    'status' => 'SENT',
                    'sent_at' => now(),
                    'last_error' => null,
                ]);
                return;
            }

            $this->markFailed($event, 'HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 1000));
        } catch (\Throwable $e) {
            $this->markFailed($event, mb_substr($e->getMessage(), 0, 1000));
        }
    }

    private function markFailed(IntegrationOutboxEvent $event, string $error): void
    {
        $retries = ((int) $event->retries) + 1;
        $delayMinutes = min(60, 2 ** min($retries, 8)); // 2,4,8...60

        $event->update([
            'status' => 'FAILED',
            'retries' => $retries,
            'next_retry_at' => Carbon::now()->addMinutes($delayMinutes),
            'last_error' => $error,
        ]);
    }
}

