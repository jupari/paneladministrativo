<?php

namespace App\Mail;

use App\Models\Cotizacion;
use App\Services\CotizacionPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CotizacionEnviada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Cotizacion $cotizacion,
        protected readonly CotizacionPdfService $pdfService,
    ) {}

    public function envelope(): Envelope
    {
        $numero  = $this->cotizacion->num_documento ?? 'S/N';
        $empresa = config('app.name', 'Panel Administrativo');

        return new Envelope(
            subject: "Cotización {$numero} – {$empresa}",
        );
    }

    public function content(): Content
    {
        $linkAprobacion = url('/cotizacion/' . $this->cotizacion->token_aprobacion);

        return new Content(
            view: 'emails.cotizacion-enviada',
            with: [
                'cotizacion'       => $this->cotizacion,
                'linkAprobacion'   => $linkAprobacion,
            ],
        );
    }

    public function attachments(): array
    {
        $pdfContent  = $this->pdfService->buildContent($this->cotizacion);
        $filename    = $this->pdfService->filename($this->cotizacion);

        return [
            Attachment::fromData(fn () => $pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
