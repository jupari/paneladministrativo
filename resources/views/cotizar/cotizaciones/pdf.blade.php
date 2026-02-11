<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $cotizacion->num_documento ?? 'Sin número' }}</title>
    <style>
        @page { margin: 15mm; size: A4; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #000;
        }

        .header {
            border: 2px solid #000;
            padding: 8px;
            margin-bottom: 8px;
            overflow: visible;
            min-height: 90px;
            position: relative;
        }

        .header-left {
            float: left;
            width: 60%;
            padding-right: 10px;
            overflow: visible;
            position: relative;
        }

        .logo {
            float: left;
            width: 250px;
            height: 80px;
            margin-right: 15px;
            object-fit: contain;
        }

        .company-info {
            margin-left: 255px;
            padding-top: 5px;
        }

        .header-right {
            float: right;
            width: 38%;
            font-size: 8px;
            line-height: 1.2;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #2E8BC0;
            margin-bottom: 3px;
        }

        .company-tagline {
            font-size: 9px;
            color: #666;
            margin-bottom: 8px;
        }

        .company-details {
            font-size: 7px;
            line-height: 1.3;
        }

        .address-title {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 8px;
        }

        .client-project-section {
            border: 1px solid #000;
            margin-bottom: 8px;
        }

        .client-project-row {
            overflow: hidden;
        }

        .client-project-cell {
            float: left;
            padding: 4px 6px;
            font-size: 8px;
            border-bottom: 1px solid #000;
        }

        .client-project-label {
            font-weight: bold;
            background-color: #f0f0f0;
            width: 15%;
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

        .products-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin: 8px 0;
        }

        .products-table th {
            background-color: #d0d0d0;
            border: 1px solid #000;
            padding: 4px 3px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
        }

        .products-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 7px;
            vertical-align: top;
        }

        .col-item { width: 6%; text-align: center; }
        .col-concept { width: 46%; }
        .col-unit { width: 8%; text-align: center; }
        .col-quantity { width: 8%; text-align: center; }
        .col-unit-price { width: 16%; text-align: right; }
        .col-total-price { width: 16%; text-align: right; }

        .subtotal-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .totals-section {
            float: right;
            width: 40%;
            margin-top: 10px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .totals-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 8px;
        }

        .totals-label {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: right;
            width: 60%;
        }

        .totals-value {
            text-align: right;
            width: 40%;
        }

        .total-final {
            font-weight: bold;
            background-color: #d0d0d0;
        }

        .observations-section {
            clear: both;
            margin-top: 15px;
            border: 1px solid #000;
        }

        .observations-header {
            background-color: #d0d0d0;
            padding: 3px 5px;
            font-weight: bold;
            font-size: 8px;
            border-bottom: 1px solid #000;
        }

        .observations-content {
            padding: 6px;
            font-size: 7px;
            line-height: 1.3;
        }

        .footer-section {
            margin-top: 15px;
            border: 1px solid #000;
            overflow: hidden;
            position: relative;
        }

        .footer-left {
            float: left;
            width: 65%;
            padding: 8px;
        }

        .footer-right {
            float: right;
            width: 35%;
            padding: 8px;
        }

        .footer-divider {
            position: absolute;
            top: 0;
            left: 60%;
            bottom: 0;
            width: 1px;
            background-color: #000;
        }

        .footer-item {
            margin-bottom: 6px;
            font-size: 8px;
        }

        .footer-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }

        .elaborated-label {
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 4px;
        }

        .no-products {
            text-align: center;
            padding: 15px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @php
            //dd($cotizacion);
                $logoPath = public_path('storage/companies/logos/logo-minduval.png');
                $logoBase64 = '';
                if (file_exists($logoPath)) {
                    $logoData = file_get_contents($logoPath);
                    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                }
                @endphp
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Minduval Logo" class="logo">
            @endif
            <div class="company-info">
                <div class="company-name">Minduval</div>
                <div class="company-tagline">Diseño y fabricación de montajes Industriales</div>
                <div class="company-details">
                    NIT: {{ $cotizacion->tercero && $cotizacion->tercero->company ? $cotizacion->tercero->company->nit ?? '900.000.000-0' : '900.000.000-0' }}<br>
                    CEL: 319 640 2798<br>
                    EMAIL: gerencia@minduval.com.co<br>
                    www.minduval.com
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="address-title">REALIZADO POR:</div>
            Ing. César Enrique Henríquez<br>
            CRA 6 A # 51-53 BR PORVENIR<br>
            CALI - COLOMBIA<br><br>
            <strong>VERSIÓN: #1</strong><br><br>
            <strong>FECHA:</strong><br>
            {{ $cotizacion->fecha ? \Carbon\Carbon::parse($cotizacion->fecha)->format('d/m/Y') : date('d/m/Y') }}
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="client-project-section">
        <div class="client-project-row">
            <div class="client-project-cell client-project-label">CLIENTE:</div>
            <div class="client-project-cell client-project-value">
                @if($cotizacion->tercero)
                    {{ $cotizacion->tercero->nombre_establecimiento ?: trim(($cotizacion->tercero->nombres ?? '') . ' ' . ($cotizacion->tercero->apellidos ?? '')) ?: 'Cliente no especificado' }}
                @else
                    Cliente no especificado
                @endif
            </div>
            <div class="client-project-cell client-project-label">CONTACTO:</div>
            <div class="client-project-cell client-project-value">
                @if($cotizacion->terceroContacto)
                    {{ $cotizacion->terceroContacto->nombre ?? ($cotizacion->tercero->telefono ?? 'Sin contacto') }}
                @else
                    {{ $cotizacion->tercero->telefono ?? 'Sin contacto' }}
                @endif
            </div>
            <div style="clear: both;"></div>
        </div>
        <div class="client-project-row">
            <div class="client-project-cell client-project-label">PROYECTO:</div>
            <div class="client-project-cell client-project-value" style="width: 85%;">
                {{ $cotizacion->proyecto ?? 'Consultoría en infraestructura' }}
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    <div class="intro-text">
        De acuerdo a su solicitud presentamos la siguiente oferta
    </div>

    <table class="products-table">
        <thead>
            <tr>
                <th class="col-item">ITEM</th>
                <th class="col-concept">CONCEPTO</th>
                <th class="col-unit">UND</th>
                <th class="col-quantity">CANT</th>
                <th class="col-unit-price">VR UNIDAD</th>
                <th class="col-total-price">VR TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @if($cotizacion->productos && $cotizacion->productos->count() > 0)
                @php $subtotal = 0; @endphp
                @foreach($cotizacion->productos as $index => $producto)
                    @php
                        $total_item = ($producto->cantidad ?? 1) * ($producto->valor_unitario ?? 0);
                        $subtotal += $total_item;
                    @endphp
                    <tr>
                        <td class="col-item">{{ $index + 1 }}</td>
                        <td class="col-concept">
                            {{ $producto->nombre ?? ($producto->producto->nombre ?? 'Servicio profesional') }}
                        </td>
                        <td class="col-unit">PC</td>
                        <td class="col-quantity">{{ number_format($producto->cantidad ?? 1, 0) }}</td>
                        <td class="col-unit-price">${{ number_format($producto->valor_unitario ?? 0, 2) }}</td>
                        <td class="col-total-price">${{ number_format($total_item, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="5" style="text-align: right; padding-right: 8px;"><strong>SUBTOTAL</strong></td>
                    <td class="col-total-price"><strong>${{ number_format($subtotal, 2) }}</strong></td>
                </tr>
            @else
                <tr>
                    <td colspan="6" class="no-products">
                        No se han agregado productos o servicios a esta cotización
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="totals-label">SUBTOTAL</td>
                <td class="totals-value">${{ number_format($cotizacion->subtotal ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="totals-label">DESCUENTO 0 %</td>
                <td class="totals-value">$0.00</td>
            </tr>
            <tr>
                <td class="totals-label">IVA 19 %</td>
                <td class="totals-value">${{ number_format(($cotizacion->subtotal ?? 0) * 0.19, 2) }}</td>
            </tr>
            <tr class="total-final">
                <td class="totals-label">TOTAL</td>
                <td class="totals-value">${{ number_format(($cotizacion->subtotal ?? 0) * 1.19, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="observations-section">
        <div class="observations-header">OBSERVACIONES:</div>
        <div class="observations-content">
            {!! nl2br(e($cotizacion->observacion ?? 'Sin observaciones adicionales.')) !!}
        </div>
    </div>

    <div class="footer-section">
        <div class="footer-divider"></div>
        <div class="footer-left">
            <div class="footer-item">
                <span class="footer-label">TIEMPO DE ENTREGA:</span>
                <span>3 Meses después de la firma del contrato</span>
            </div>
            <div class="footer-item">
                <span class="footer-label">SITIO DE ENTREGA:</span>
                <span>CRA 8 # 8-20</span>
            </div>
            <div class="footer-item">
                <span class="footer-label">DURACIÓN DE LA OFERTA:</span>
                <span>30 días</span>
            </div>
            <div class="footer-item">
                <span class="footer-label">GARANTÍA:</span>
                <span>Según pólizas</span>
            </div>
            <div class="footer-item">
                <span class="footer-label">FORMA DE PAGO:</span>
                <span>Pendiente definir por acordar</span>
            </div>
        </div>
        <div class="footer-right">
            <div class="elaborated-label">ELABORADO POR:</div>
            <div>Administrador</div>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
