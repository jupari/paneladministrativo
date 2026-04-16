<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $cotizacion->num_documento ?? '' }}</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; padding:0; color:#333; }
        .wrapper { max-width:620px; margin:30px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.1); }
        .header { background:#1e3a5f; padding:28px 36px; text-align:center; }
        .header img { max-height:60px; max-width:200px; }
        .header h1 { color:#fff; margin:12px 0 0; font-size:20px; font-weight:600; }
        .body { padding:32px 36px; }
        .saludo { font-size:16px; margin-bottom:12px; }
        .intro { font-size:14px; color:#555; line-height:1.6; margin-bottom:24px; }
        .resumen { background:#f9fbfd; border:1px solid #dde4ec; border-radius:6px; padding:18px 22px; margin-bottom:28px; }
        .resumen h3 { margin:0 0 14px; font-size:14px; text-transform:uppercase; color:#1e3a5f; letter-spacing:.5px; }
        .resumen table { width:100%; border-collapse:collapse; font-size:13px; }
        .resumen td { padding:6px 0; }
        .resumen td:first-child { color:#666; width:45%; }
        .resumen td:last-child { font-weight:600; color:#1e3a5f; }
        .total-row td { border-top:1px solid #dde4ec; padding-top:10px; font-size:15px; }
        .cta { text-align:center; margin:28px 0; }
        .cta a { background:#1e3a5f; color:#fff; text-decoration:none; padding:14px 36px; border-radius:6px; font-size:15px; font-weight:600; display:inline-block; }
        .cta a:hover { background:#274e7d; }
        .aviso { font-size:12px; color:#888; line-height:1.5; margin-bottom:20px; }
        .footer { background:#f4f6f8; padding:18px 36px; text-align:center; font-size:11px; color:#999; border-top:1px solid #e8ecf0; }
        .badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; color:#fff; background:#17a2b8; vertical-align:middle; }
    </style>
</head>
<body>

<div class="wrapper">

    {{-- HEADER --}}
    <div class="header">
        <h1>Cotización {{ $cotizacion->num_documento ?? 'S/N' }}</h1>
    </div>

    {{-- BODY --}}
    <div class="body">

        <p class="saludo">
            Estimado/a
            @if($cotizacion->terceroContacto)
                <strong>{{ $cotizacion->terceroContacto->nombres }} {{ $cotizacion->terceroContacto->apellidos }}</strong>,
            @elseif($cotizacion->tercero)
                <strong>{{ $cotizacion->tercero->nombre_establecimiento ?? $cotizacion->tercero->nombres }}</strong>,
            @else
                cliente,
            @endif
        </p>

        <p class="intro">
            Nos complace presentarle la siguiente cotización.
            Puede revisarla en detalle y darnos su respuesta directamente desde el botón al final de este mensaje.
            El PDF del documento queda adjunto a este correo para su referencia.
        </p>

        {{-- RESUMEN --}}
        <div class="resumen">
            <h3>Resumen del documento</h3>
            <table>
                <tr>
                    <td>Número de cotización</td>
                    <td>{{ $cotizacion->num_documento ?? 'S/N' }}</td>
                </tr>
                <tr>
                    <td>Versión</td>
                    <td>{{ $cotizacion->version ?? 1 }}</td>
                </tr>
                @if($cotizacion->proyecto)
                <tr>
                    <td>Proyecto</td>
                    <td>{{ $cotizacion->proyecto }}</td>
                </tr>
                @endif
                @if($cotizacion->fecha)
                <tr>
                    <td>Fecha</td>
                    <td>{{ \Carbon\Carbon::parse($cotizacion->fecha)->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if($cotizacion->fecha_vencimiento)
                <tr>
                    <td>Válida hasta</td>
                    <td>{{ \Carbon\Carbon::parse($cotizacion->fecha_vencimiento)->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if($cotizacion->descuento > 0)
                <tr>
                    <td>Descuento</td>
                    <td>-${{ number_format($cotizacion->descuento, 2) }}</td>
                </tr>
                @endif
                @if($cotizacion->total_impuesto > 0)
                <tr>
                    <td>Impuestos</td>
                    <td>${{ number_format($cotizacion->total_impuesto, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td>${{ number_format($cotizacion->total, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- CTA --}}
        <div class="cta">
            <a href="{{ $linkAprobacion }}">Ver y responder cotización</a>
        </div>

        <p class="aviso">
            Si el botón no funciona, copie y pegue este enlace en su navegador:<br>
            <span style="color:#1e3a5f; word-break:break-all;">{{ $linkAprobacion }}</span>
        </p>

        @if($cotizacion->fecha_vencimiento)
        <p class="aviso">
            ⏰ Esta oferta es válida hasta el
            <strong>{{ \Carbon\Carbon::parse($cotizacion->fecha_vencimiento)->format('d/m/Y') }}</strong>.
        </p>
        @endif

        <p class="aviso">
            Si tiene alguna pregunta, no dude en contactarnos.<br>
            Este correo fue generado automáticamente. Por favor no responda directamente a este mensaje.
        </p>

    </div>

    {{-- FOOTER --}}
    <div class="footer">
        {{ config('app.name', 'Panel Administrativo') }} &bull;
        Cotización {{ $cotizacion->num_documento ?? '' }} &bull;
        {{ now()->format('Y') }}
    </div>

</div>

</body>
</html>
