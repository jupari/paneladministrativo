<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $cotizacion->num_documento }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .info-section {
            margin: 20px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
        }
        .table th {
            background-color: #f0f0f0;
        }

        .items-table td.number {
            text-align: center;
            width: 8%;
        }

        .items-table td.amount {
            text-align: right;
            width: 15%;
        }

        .items-table td.quantity {
            text-align: center;
            width: 10%;
        }

        .items-table td.description {
            width: 52%;
        }

        .no-items {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        /* Totales */
        .totals-section {
            float: right;
            width: 40%;
            margin-top: 20px;
        }

        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .total-row:last-child {
            margin-bottom: 0;
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            margin-top: 8px;
        }

        .total-label {
            display: table-cell;
            width: 60%;
            text-align: left;
        }

        .total-value {
            display: table-cell;
            width: 40%;
            text-align: right;
        }

        /* Observaciones */
        .observations {
            clear: both;
            margin-top: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        .observations-header {
            background-color: #f0f7ff;
            padding: 8px 15px;
            font-weight: bold;
            color: #0066cc;
            border-bottom: 1px solid #ddd;
        }

        .observations-body {
            padding: 15px;
            min-height: 60px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">
                    @if($cotizacion->tercero && $cotizacion->tercero->company)
                        {{ $cotizacion->tercero->company->name ?? 'EMPRESA' }}
                    @else
                        EMPRESA
                    @endif
                </div>
                <div class="company-info">
                    @if($cotizacion->tercero && $cotizacion->tercero->company)
                        {{ $cotizacion->tercero->company->nit ?? 'NIT: N/A' }}<br>
                        {{ $cotizacion->tercero->company->address ?? 'Dirección: N/A' }}<br>
                        {{ $cotizacion->tercero->company->phone ?? 'Teléfono: N/A' }}<br>
                        {{ $cotizacion->tercero->company->email ?? 'Email: N/A' }}
                    @else
                        NIT: N/A<br>
                        Dirección: N/A<br>
                        Teléfono: N/A<br>
                        Email: N/A
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="document-title">COTIZACIÓN</div>
                <div class="document-number">No. {{ $cotizacion->num_documento ?? 'Sin número' }}</div>
                <div class="document-date">
                    Fecha: {{ $cotizacion->fecha ? \Carbon\Carbon::parse($cotizacion->fecha)->format('d/m/Y') : date('d/m/Y') }}
                </div>
            </div>
        </div>

        <!-- Información del cliente -->
        <div class="client-info">
            <div class="client-info-header">
                DATOS DEL CLIENTE
            </div>
            <div class="client-info-body">
                @if($cotizacion->tercero)
                    <div class="info-row">
                        <div class="info-label">Cliente:</div>
                        <div class="info-value">
                            {{ $cotizacion->tercero->nombres ?? '' }} {{ $cotizacion->tercero->apellidos ?? '' }}
                            {{ $cotizacion->tercero->nombre_establecimiento ?? '' }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Identificación:</div>
                        <div class="info-value">{{ $cotizacion->tercero->identificacion ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Teléfono:</div>
                        <div class="info-value">{{ $cotizacion->tercero->telefono ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dirección:</div>
                        <div class="info-value">{{ $cotizacion->tercero->direccion ?? 'N/A' }}</div>
                    </div>
                    @if($cotizacion->terceroSucursal)
                    <div class="info-row">
                        <div class="info-label">Sucursal:</div>
                        <div class="info-value">{{ $cotizacion->terceroSucursal->nombre ?? 'N/A' }}</div>
                    </div>
                    @endif
                    @if($cotizacion->terceroContacto)
                    <div class="info-row">
                        <div class="info-label">Contacto:</div>
                        <div class="info-value">{{ $cotizacion->terceroContacto->nombre ?? 'N/A' }}</div>
                    </div>
                    @endif
                @else
                    <div class="info-row">
                        <div class="info-label">Cliente:</div>
                        <div class="info-value">No hay información del cliente disponible</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Productos/Servicios -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Descripción</th>
                        <th>Cant.</th>
                        <th>Precio Unit.</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if($cotizacion->productos && $cotizacion->productos->count() > 0)
                        @foreach($cotizacion->productos as $index => $producto)
                        <tr>
                            <td class="number">{{ $index + 1 }}</td>
                            <td class="description">{{ $producto->nombre ?? ($producto->producto->nombre ?? 'Producto sin nombre') }}</td>
                            <td class="quantity">{{ $producto->cantidad ?? 1 }}</td>
                            <td class="amount">${{ number_format($producto->valor_unitario ?? 0, 2) }}</td>
                            <td class="amount">${{ number_format(($producto->cantidad ?? 1) * ($producto->valor_unitario ?? 0), 2) }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="no-items">No se han agregado productos a esta cotización</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="totals-section">
            <div class="totals-header">
                RESUMEN
            </div>
            <div class="totals-body">
                <div class="total-row">
                    <div class="total-label">Subtotal:</div>
                    <div class="total-value">${{ number_format($cotizacion->subtotal ?? 0, 2) }}</div>
                </div>
                @if($cotizacion->descuento && $cotizacion->descuento > 0)
                <div class="total-row">
                    <div class="total-label">Descuento:</div>
                    <div class="total-value">-${{ number_format($cotizacion->descuento ?? 0, 2) }}</div>
                </div>
                @endif
                @if($cotizacion->iva && $cotizacion->iva > 0)
                <div class="total-row">
                    <div class="total-label">IVA:</div>
                    <div class="total-value">${{ number_format($cotizacion->iva ?? 0, 2) }}</div>
                </div>
                @endif
                <div class="total-row">
                    <div class="total-label">TOTAL:</div>
                    <div class="total-value">${{ number_format($cotizacion->total ?? 0, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Observaciones -->
        @if($cotizacion->observacion)
        <div class="observations">
            <div class="observations-header">
                OBSERVACIONES
            </div>
            <div class="observations-body">
                {{ $cotizacion->observacion }}
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Esta cotización tiene una validez de 30 días a partir de la fecha de emisión.</p>
            <p>Documento generado el {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
