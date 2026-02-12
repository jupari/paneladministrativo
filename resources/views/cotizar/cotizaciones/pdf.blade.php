<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $cotizacion->num_documento ?? 'Sin número' }}</title>

    <style>
        @page {
            margin: 120px 20px 90px 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #000;
        }

        .header {
            border: 2px solid #000;
            padding: 8px;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .header-left {
            float: left;
            width: 60%;
        }

        .logo {
            width: 240px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 6px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #2E8BC0;
        }

        .company-tagline {
            font-size: 9px;
            color: #555;
            margin-bottom: 6px;
        }

        .company-details {
            font-size: 7px;
            line-height: 1.4;
        }

        .header-right {
            float: right;
            width: 38%;
            font-size: 8px;
            line-height: 1.3;
            text-align: left;
        }

        .client-project-section {
            border: 1px solid #000;
            margin-bottom: 8px;
        }

        .client-project-row {
            overflow: hidden;
        }

        .client-project-label,
        .client-project-value {
            float: left;
            padding: 4px 6px;
            font-size: 8px;
            border-bottom: 1px solid #000;
        }

        .client-project-label {
            width: 15%;
            font-weight: bold;
            background-color: #f0f0f0;
            border-right: 1px solid #000;
        }

        .client-project-value {
            width: 35%;
            border-right: 1px solid #000;
        }

        .intro-text {
            margin: 10px 0;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table {
            border: 1px solid #000;
            margin-bottom: 8px;
        }

        .products-table th {
            background-color: #d0d0d0;
            border: 1px solid #000;
            padding: 4px;
            font-size: 7px;
            text-align: center;
        }

        .products-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 7px;
        }

        .right { text-align: right; }
        .center { text-align: center; }

        .subtotal-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .totals-table {
            width: 40%;
            float: right;
            border: 1px solid #000;
            margin-top: 8px;
        }

        .totals-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 8px;
        }

        .totals-label {
            font-weight: bold;
            background-color: #f0f0f0;
            text-align: right;
        }

        .total-final {
            background-color: #d0d0d0;
            font-weight: bold;
        }

        .observations-section {
            clear: both;
            margin-top: 12px;
            border: 1px solid #000;
        }

        .observations-header {
            background-color: #d0d0d0;
            padding: 4px;
            font-size: 8px;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }

        .observations-content {
            padding: 6px;
            font-size: 7px;
        }

        .footer-section {
            margin-top: 12px;
            border: 1px solid #000;
            overflow: hidden;
        }

        .footer-left {
            float: left;
            width: 65%;
            padding: 8px;
            border-right: 1px solid #000;
        }

        .footer-right {
            float: right;
            width: 35%;
            padding: 8px;
        }

        .footer-item {
            font-size: 8px;
            margin-bottom: 6px;
        }

        .footer-label {
            font-weight: bold;
            display: inline-block;
            width: 110px;
        }
    </style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <div class="header-left">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="logo" alt="Logo">
        @endif

        <div class="company-name">Minduval</div>
        <div class="company-tagline">Diseño y fabricación de montajes industriales</div>
        <div class="company-details">
            NIT: {{ $cotizacion->tercero->company->nit ?? '900.000.000-0' }}<br>
            CEL: 319 640 2798<br>
            EMAIL: gerencia@minduval.com.co<br>
            www.minduval.com
        </div>
    </div>

    <div class="header-right">
        <strong>REALIZADO POR:</strong><br>
        Ing. César Enrique Henríquez<br>
        Cali - Colombia<br><br>

        <strong>VERSIÓN:</strong> #1<br>
        <strong>FECHA:</strong>
        {{ \Carbon\Carbon::parse($cotizacion->fecha ?? now())->format('d/m/Y') }}
    </div>
</div>

{{-- CLIENTE --}}
<div class="client-project-section">
    <div class="client-project-row">
        <div class="client-project-label">CLIENTE</div>
        <div class="client-project-value">
            {{ $cotizacion->tercero->nombre_establecimiento ?? 'No especificado' }}
        </div>

        <div class="client-project-label">CONTACTO</div>
        <div class="client-project-value">
            {{ $cotizacion->terceroContacto->nombre ?? 'Sin contacto' }}
        </div>
    </div>
</div>

{{-- INTRO --}}
<div class="intro-text">
    De acuerdo a su solicitud presentamos la siguiente oferta:
</div>

{{-- PRODUCTOS --}}
<table class="products-table">
    <thead>
        <tr>
            <th>ITEM</th>
            <th>CONCEPTO</th>
            <th>UND</th>
            <th>CANT</th>
            <th>VR UNIDAD</th>
            <th>VR TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @php $subtotal = 0; @endphp
        @foreach($cotizacion->productos as $i => $producto)
            @php
                $total = ($producto->cantidad ?? 1) * ($producto->valor_unitario ?? 0);
                $subtotal += $total;
            @endphp
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $producto->nombre }}</td>
                <td class="center">UND</td>
                <td class="center">{{ $producto->cantidad }}</td>
                <td class="right">${{ number_format($producto->valor_unitario, 2) }}</td>
                <td class="right">${{ number_format($total, 2) }}</td>
            </tr>
        @endforeach

        <tr class="subtotal-row">
            <td colspan="5" class="right">SUBTOTAL</td>
            <td class="right">${{ number_format($subtotal, 2) }}</td>
        </tr>
    </tbody>
</table>

{{-- TOTALES --}}
<table class="totals-table">
    <tr>
        <td class="totals-label">SUBTOTAL</td>
        <td class="right">${{ number_format($subtotal, 2) }}</td>
    </tr>
    <tr>
        <td class="totals-label">IVA 19%</td>
        <td class="right">${{ number_format($subtotal * 0.19, 2) }}</td>
    </tr>
    <tr class="total-final">
        <td class="totals-label">TOTAL</td>
        <td class="right">${{ number_format($subtotal * 1.19, 2) }}</td>
    </tr>
</table>

{{-- OBSERVACIONES --}}
<div class="observations-section">
    <div class="observations-header">OBSERVACIONES</div>
    <div class="observations-content">
        {!! nl2br(e($cotizacion->observacion ?? 'Sin observaciones')) !!}
    </div>
</div>

{{-- FOOTER --}}
<div class="footer-section">
    <div class="footer-left">
        <div class="footer-item"><span class="footer-label">ENTREGA:</span> 3 meses</div>
        <div class="footer-item"><span class="footer-label">GARANTÍA:</span> Según pólizas</div>
        <div class="footer-item"><span class="footer-label">PAGO:</span> Por acordar</div>
    </div>

    <div class="footer-right">
        <strong>ELABORADO POR:</strong><br>
        Administrador
    </div>
</div>

</body>
</html>
