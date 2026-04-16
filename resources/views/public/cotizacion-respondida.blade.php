<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta registrada</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,.1); padding: 48px 40px; text-align: center; max-width: 480px; width: 100%; }
        .icon { font-size: 64px; margin-bottom: 20px; }
        h1 { font-size: 22px; margin: 0 0 12px; color: #1e3a5f; }
        p { font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 10px; }
        .detail { font-size: 13px; color: #888; margin-top: 18px; }
        .badge { display: inline-block; padding: 5px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; color: #fff; margin-top: 10px; }
        .badge-aprobado { background: #28a745; }
        .badge-rechazado { background: #dc3545; }
    </style>
</head>
<body>
<div class="card">
    @if(isset($accion) && $accion === 'aprobar')
        <div class="icon">🎉</div>
        <h1>¡Cotización aprobada!</h1>
        <p>{{ $mensaje }}</p>
        <span class="badge badge-aprobado">APROBADO</span>
    @elseif(isset($accion) && $accion === 'rechazar')
        <div class="icon">📋</div>
        <h1>Respuesta registrada</h1>
        <p>{{ $mensaje }}</p>
        <span class="badge badge-rechazado">RECHAZADO</span>
    @else
        <div class="icon">ℹ️</div>
        <h1>Información</h1>
        <p>{{ $mensaje }}</p>
    @endif

    @if(isset($cotizacion))
    <div class="detail">
        Cotización <strong>{{ $cotizacion->num_documento ?? 'S/N' }}</strong><br>
        {{ config('app.name', 'Panel Administrativo') }}
    </div>
    @endif
</div>
</body>
</html>
