<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $cotizacion->num_documento ?? 'Sin número' }}</title>
    <style>
        @page {
            margin: 20mm 15mm;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
        }

        .document-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .company-subtitle {
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .company-details {
            font-size: 9px;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .document-title-section {
            border: 2px solid #000;
            padding: 8px;
            margin: 15px auto;
            width: 200px;
        }

        .document-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        .document-number {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-top: 3px;
        }

        .date-location {
            text-align: right;
            margin: 15px 0;
            font-size: 10px;
        }

        .client-section {
            margin: 25px 0;
        }

        .client-label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .client-details {
            border: 1px solid #000;
            padding: 12px;
            background-color: #fafafa;
        }

        .client-row {
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .client-row:last-child {
            margin-bottom: 0;
        }

        .client-field {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }

        .client-value {
            display: inline;
        }

        .products-section {
            margin: 25px 0;
        }

        .products-label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .products-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px 5px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .products-table td {
            border: 1px solid #000;
            padding: 6px 5px;
            font-size: 9px;
            vertical-align: top;
        }

        .col-item {
            width: 8%;
            text-align: center;
        }

        .col-description {
            width: 52%;
            text-align: left;
        }

        .col-quantity {
            width: 10%;
            text-align: center;
        }

        .col-price {
            width: 15%;
            text-align: right;
        }

        .col-total {
            width: 15%;
            text-align: right;
        }

        .no-products-row {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }

        .totals-section {
            margin-top: 20px;
            float: right;
            width: 250px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .totals-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10px;
        }

        .totals-label {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 60%;
        }

        .totals-value {
            text-align: right;
            width: 40%;
        }

        .total-final {
            font-weight: bold;
            background-color: #e0e0e0;
        }

        .observations-section {
            clear: both;
            margin-top: 40px;
        }

        .observations-label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .observations-content {
            border: 1px solid #000;
            padding: 15px;
            min-height: 60px;
            background-color: #fafafa;
        }

        .footer-section {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .signature-section {
            margin-top: 40px;
            overflow: hidden;
        }

        .signature-box {
            width: 45%;
            float: left;
            text-align: center;
        }

        .signature-box:last-child {
            float: right;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header with company info -->
    <div class="document-header">
        <div class="company-name">
            @if($cotizacion->tercero && $cotizacion->tercero->company)
                {{ $cotizacion->tercero->company->name ?? 'NOMBRE DE LA EMPRESA' }}
            @else
                NOMBRE DE LA EMPRESA
            @endif
        </div>
        <div class="company-subtitle">
            Servicios Profesionales
        </div>
        <div class="company-details">
            @if($cotizacion->tercero && $cotizacion->tercero->company)
                NIT: {{ $cotizacion->tercero->company->nit ?? '000.000.000-0' }}<br>
                {{ $cotizacion->tercero->company->address ?? 'Dirección de la empresa' }}<br>
                Teléfono: {{ $cotizacion->tercero->company->phone ?? '(000) 000-0000' }}<br>
                E-mail: {{ $cotizacion->tercero->company->email ?? 'email@empresa.com' }}
            @else
                NIT: 000.000.000-0<br>
                Dirección de la empresa<br>
                Teléfono: (000) 000-0000<br>
                E-mail: email@empresa.com
            @endif
        </div>

        <div class="document-title-section">
            <div class="document-title">COTIZACIÓN</div>
            <div class="document-number">No. {{ $cotizacion->num_documento ?? '00001' }}</div>
        </div>
    </div>

    <!-- Date and location -->
    <div class="date-location">
        <strong>{{ $cotizacion->tercero->ciudad ?? 'Ciudad' }}, {{ $cotizacion->fecha ? \Carbon\Carbon::parse($cotizacion->fecha)->format('d \d\e F \d\e Y') : date('d \d\e F \d\e Y') }}</strong>
    </div>

    <!-- Client information -->
    <div class="client-section">
        <div class="client-label">SEÑOR(ES):</div>
        <div class="client-details">
            @if($cotizacion->tercero)
                <div class="client-row">
                    <span class="client-field">Empresa:</span>
                    <span class="client-value">
                        {{ $cotizacion->tercero->nombre_establecimiento ?: (trim(($cotizacion->tercero->nombres ?? '') . ' ' . ($cotizacion->tercero->apellidos ?? '')) ?: 'Cliente') }}
                    </span>
                </div>
                <div class="client-row">
                    <span class="client-field">NIT/CC:</span>
                    <span class="client-value">{{ $cotizacion->tercero->identificacion ?? 'N/A' }}</span>
                </div>
                <div class="client-row">
                    <span class="client-field">Dirección:</span>
                    <span class="client-value">{{ $cotizacion->tercero->direccion ?? 'N/A' }}</span>
                </div>
                <div class="client-row">
                    <span class="client-field">Teléfono:</span>
                    <span class="client-value">{{ $cotizacion->tercero->telefono ?? 'N/A' }}</span>
                </div>
                <div class="client-row">
                    <span class="client-field">E-mail:</span>
                    <span class="client-value">{{ $cotizacion->tercero->email ?? 'N/A' }}</span>
                </div>
            @else
                <div class="client-row">
                    <span class="client-field">Empresa:</span>
                    <span class="client-value">Cliente no especificado</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Products/Services section -->
    <div class="products-section">
        <div class="products-label">Estimado cliente, nos permitimos cotizar los siguientes servicios:</div>

        <table class="products-table">
            <thead>
                <tr>
                    <th class="col-item">ITEM</th>
                    <th class="col-description">DESCRIPCIÓN</th>
                    <th class="col-quantity">CANT</th>
                    <th class="col-price">VLR UNIT</th>
                    <th class="col-total">VLR TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @if($cotizacion->productos && $cotizacion->productos->count() > 0)
                    @foreach($cotizacion->productos as $index => $producto)
                    <tr>
                        <td class="col-item">{{ $index + 1 }}</td>
                        <td class="col-description">
                            {{ $producto->nombre ?? ($producto->producto->nombre ?? 'Servicio profesional') }}
                            @if($producto->descripcion)
                                <br>{{ $producto->descripcion }}
                            @endif
                        </td>
                        <td class="col-quantity">{{ number_format($producto->cantidad ?? 1, 0) }}</td>
                        <td class="col-price">${{ number_format($producto->valor_unitario ?? 0, 0) }}</td>
                        <td class="col-total">${{ number_format(($producto->cantidad ?? 1) * ($producto->valor_unitario ?? 0), 0) }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="no-products-row">
                            No se han agregado productos o servicios a esta cotización
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Totals section -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="totals-label">SUBTOTAL:</td>
                <td class="totals-value">${{ number_format($cotizacion->subtotal ?? 0, 0) }}</td>
            </tr>
            @if($cotizacion->descuento && $cotizacion->descuento > 0)
            <tr>
                <td class="totals-label">DESCUENTO:</td>
                <td class="totals-value">-${{ number_format($cotizacion->descuento, 0) }}</td>
            </tr>
            @endif
            @if($cotizacion->iva && $cotizacion->iva > 0)
            <tr>
                <td class="totals-label">IVA (19%):</td>
                <td class="totals-value">${{ number_format($cotizacion->iva, 0) }}</td>
            </tr>
            @endif
            <tr class="total-final">
                <td class="totals-label">TOTAL:</td>
                <td class="totals-value">${{ number_format($cotizacion->total ?? 0, 0) }}</td>
            </tr>
        </table>
    </div>

    <!-- Observations -->
    @if($cotizacion->observacion)
    <div class="observations-section">
        <div class="observations-label">OBSERVACIONES:</div>
        <div class="observations-content">
            {{ $cotizacion->observacion }}
        </div>
    </div>
    @endif

    <!-- Additional terms -->
    <div class="observations-section">
        <div class="observations-label">CONDICIONES:</div>
        <div class="observations-content">
            • Cotización válida por 30 días.<br>
            • Los precios no incluyen IVA a menos que se especifique lo contrario.<br>
            • Forma de pago: A convenir según términos comerciales.<br>
            • Esta cotización está sujeta a disponibilidad de recursos y cronograma.
        </div>
    </div>

    <!-- Signature section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">ELABORADO POR</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">APROBADO POR</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Footer -->
    <div class="footer-section">
        <p>Esta cotización ha sido generada automáticamente el {{ date('d/m/Y \a \l\a\s H:i') }}</p>
    </div>
</body>
</html>
