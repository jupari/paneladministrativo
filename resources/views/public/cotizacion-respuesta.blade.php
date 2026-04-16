<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización – Responder</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; color: #333; }
        .page { max-width: 860px; margin: 30px auto; padding: 0 16px 60px; }

        /* ── HEADER ─────────────────────────────────────────── */
        .header { background: #1e3a5f; color: #fff; border-radius: 8px 8px 0 0; padding: 24px 32px; display: flex; align-items: center; gap: 20px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header .badge { background: rgba(255,255,255,.2); border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600; white-space: nowrap; }

        /* ── CARD ────────────────────────────────────────────── */
        .card { background: #fff; border-radius: 0 0 8px 8px; box-shadow: 0 2px 8px rgba(0,0,0,.08); padding: 28px 32px; }

        /* ── ESTADO ESPECIAL (respondida / expirada / inválido) */
        .estado-box { text-align: center; padding: 48px 24px; }
        .estado-box .icon { font-size: 56px; margin-bottom: 16px; }
        .estado-box h2 { font-size: 22px; margin-bottom: 8px; }
        .estado-box p { color: #666; font-size: 14px; }

        /* ── INFO GRID ───────────────────────────────────────── */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; margin-bottom: 24px; font-size: 13px; }
        .info-grid .label { color: #888; }
        .info-grid .value { font-weight: 600; }

        /* ── TABLA ───────────────────────────────────────────── */
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; color: #1e3a5f; letter-spacing: .5px; border-bottom: 2px solid #1e3a5f; padding-bottom: 6px; margin: 24px 0 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead tr { background: #1e3a5f; color: #fff; }
        thead th { padding: 9px 10px; text-align: left; }
        thead th.right { text-align: right; }
        tbody tr:nth-child(even) { background: #f7f9fc; }
        tbody td { padding: 8px 10px; vertical-align: top; }
        tbody td.right { text-align: right; white-space: nowrap; }
        .item-row td { background: #dde9f5; font-weight: 700; color: #1e3a5f; }
        .subitem-row td { background: #eef4fb; font-style: italic; padding-left: 18px; }

        /* ── TOTALES ─────────────────────────────────────────── */
        .totals-wrap { display: flex; justify-content: flex-end; margin-top: 10px; }
        .totals-table { width: 300px; font-size: 13px; border-collapse: collapse; }
        .totals-table td { padding: 6px 10px; }
        .totals-table td:first-child { color: #555; }
        .totals-table td:last-child { text-align: right; font-weight: 600; white-space: nowrap; }
        .totals-table .total-final td { background: #1e3a5f; color: #fff; font-size: 15px; border-radius: 4px; }
        .descuento td { color: #b71c1c; }
        .retencion td { color: #4a148c; }

        /* ── OBSERVACIONES ───────────────────────────────────── */
        .obs-box { background: #f9fbfd; border: 1px solid #dde4ec; border-radius: 6px; padding: 14px 18px; font-size: 13px; line-height: 1.6; color: #444; white-space: pre-line; }

        /* ── CONDICIONES ─────────────────────────────────────── */
        .cond-list { font-size: 13px; }
        .cond-list dt { font-weight: 700; color: #1e3a5f; margin-top: 8px; }
        .cond-list dd { margin: 2px 0 0 16px; color: #444; }

        /* ── BOTONES DE RESPUESTA ────────────────────────────── */
        .actions { margin-top: 36px; padding-top: 24px; border-top: 2px solid #e8ecf0; }
        .actions h3 { font-size: 16px; margin-bottom: 18px; }
        .btn-aprobar { background: #28a745; color: #fff; border: none; padding: 13px 32px; border-radius: 6px; font-size: 15px; font-weight: 700; cursor: pointer; margin-right: 12px; }
        .btn-rechazar { background: #dc3545; color: #fff; border: none; padding: 13px 32px; border-radius: 6px; font-size: 15px; font-weight: 700; cursor: pointer; }
        .btn-aprobar:hover { background: #218838; }
        .btn-rechazar:hover { background: #c82333; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; }
        .form-group input, .form-group textarea { width: 100%; border: 1px solid #cdd4dc; border-radius: 5px; padding: 9px 12px; font-size: 13px; font-family: inherit; }
        .form-group textarea { min-height: 90px; resize: vertical; }
        #panel-rechazar { display: none; margin-top: 18px; background: #fff5f5; border: 1px solid #f5c6cb; border-radius: 6px; padding: 16px 20px; }
        #panel-aprobar { display: none; margin-top: 18px; background: #f0fff4; border: 1px solid #c3e6cb; border-radius: 6px; padding: 16px 20px; }
        .error-msg { color: #dc3545; font-size: 12px; margin-top: 4px; display: none; }
    </style>
</head>
<body>

<div class="page">

@if($estado === 'invalido')
    <div class="card" style="border-radius:8px;">
        <div class="estado-box">
            <div class="icon">🔒</div>
            <h2>Enlace no válido</h2>
            <p>El enlace que utilizó no es válido o ya no existe.<br>Por favor contacte al proveedor para solicitar un nuevo enlace.</p>
        </div>
    </div>

@elseif($estado === 'respondida')
    <div class="header"><h1>Cotización {{ $cotizacion->num_documento ?? '' }}</h1></div>
    <div class="card">
        <div class="estado-box">
            <div class="icon">✅</div>
            <h2>Cotización ya respondida</h2>
            <p>
                Esta cotización fue respondida el
                <strong>{{ $cotizacion->fecha_respuesta?->format('d/m/Y H:i') }}</strong>
                por <strong>{{ $cotizacion->respondido_por }}</strong>.
            </p>
            @if($cotizacion->motivo_rechazo)
                <p style="color:#dc3545;">Motivo de rechazo: {{ $cotizacion->motivo_rechazo }}</p>
            @endif
        </div>
    </div>

@elseif($estado === 'expirada')
    <div class="header"><h1>Cotización {{ $cotizacion->num_documento ?? '' }}</h1></div>
    <div class="card">
        <div class="estado-box">
            <div class="icon">⏰</div>
            <h2>Cotización expirada</h2>
            <p>El plazo para responder esta cotización ha vencido.<br>Por favor contacte al proveedor para solicitar una cotización actualizada.</p>
        </div>
    </div>

@elseif($estado === 'pendiente')

    {{-- HEADER --}}
    <div class="header">
        <div>
            <h1>Cotización {{ $cotizacion->num_documento ?? 'S/N' }} – V{{ $cotizacion->version }}</h1>
            <div style="font-size:13px;margin-top:4px;opacity:.8;">
                @if($cotizacion->proyecto) Proyecto: {{ $cotizacion->proyecto }} &nbsp;|&nbsp; @endif
                Fecha: {{ $cotizacion->fecha?->format('d/m/Y') }}
                @if($cotizacion->fecha_vencimiento) &nbsp;|&nbsp; Válida hasta: {{ $cotizacion->fecha_vencimiento->format('d/m/Y') }} @endif
            </div>
        </div>
        <div class="badge" style="background:{{ $cotizacion->estado?->color ?? '#17a2b8' }};">
            {{ $cotizacion->estado?->estado ?? 'Enviado' }}
        </div>
    </div>

    <div class="card">

        {{-- INFO CLIENTE --}}
        <div class="section-title">Información del cliente</div>
        <div class="info-grid">
            <span class="label">Cliente</span>
            <span class="value">
                @if($cotizacion->tercero)
                    {{ $cotizacion->tercero->nombre_establecimiento ?? trim($cotizacion->tercero->nombres . ' ' . $cotizacion->tercero->apellidos) }}
                @endif
            </span>

            @if($cotizacion->terceroSucursal)
            <span class="label">Sede</span>
            <span class="value">{{ $cotizacion->terceroSucursal->nombre_sucursal }}</span>
            @endif

            @if($cotizacion->terceroContacto)
            <span class="label">Contacto</span>
            <span class="value">{{ $cotizacion->terceroContacto->nombres }} {{ $cotizacion->terceroContacto->apellidos }}</span>
            @endif

            <span class="label">Elaborado por</span>
            <span class="value">{{ $cotizacion->usuario?->name ?? 'Administrador' }}</span>
        </div>

        {{-- PRODUCTOS --}}
        <div class="section-title">Detalle de la oferta</div>
        <table>
            <thead>
                <tr>
                    <th style="width:8%">Cód.</th>
                    <th>Concepto</th>
                    <th style="width:7%">Und</th>
                    <th class="right" style="width:7%">Cant</th>
                    <th class="right" style="width:13%">Vr. Unitario</th>
                    <th class="right" style="width:13%">Vr. Total</th>
                </tr>
            </thead>
            <tbody>
            @php
                $productosPorItem = $cotizacion->productos->groupBy(fn($p) => optional($p->cotizacionItem)->id ?? 'general');
            @endphp
            @foreach($productosPorItem as $itemId => $productosItem)
                <tr class="item-row">
                    <td colspan="6">
                        {{ optional($productosItem->first()->cotizacionItem)->nombre ?? 'ITEM GENERAL' }}
                    </td>
                </tr>
                @php $subitems = $productosItem->groupBy(fn($p) => optional($p->cotizacionSubItem)->id ?? 'sin-sub'); @endphp
                @foreach($subitems as $subId => $productosSub)
                    @if($subId !== 'sin-sub')
                    <tr class="subitem-row">
                        <td colspan="6">{{ optional($productosSub->first()->cotizacionSubItem)->nombre }}</td>
                    </tr>
                    @endif
                    @foreach($productosSub as $producto)
                    <tr>
                        <td>{{ $producto->codigo }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->unidad_medida }}</td>
                        <td class="right">{{ number_format($producto->cantidad ?? 1, 0) }}</td>
                        <td class="right">${{ number_format($producto->valor_unitario ?? 0, 2) }}</td>
                        <td class="right">${{ number_format($producto->valor_total ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                @endforeach
            @endforeach
            </tbody>
        </table>

        {{-- TOTALES --}}
        @php
            $conceptosPorTipo = ['descuentos'=>[], 'impuestos'=>[], 'retenciones'=>[]];
            foreach($cotizacion->conceptos ?? [] as $cc){
                $tipo = strtoupper(optional($cc->concepto)->tipo ?? '');
                if(in_array($tipo,['DESCUENTO','DISCOUNT','DES','DESC'])) $conceptosPorTipo['descuentos'][] = $cc;
                elseif(in_array($tipo,['IMPUESTO','IVA','TAX','IMP'])) $conceptosPorTipo['impuestos'][] = $cc;
                elseif(in_array($tipo,['RETENCION','RETENTION','RET','RETE'])) $conceptosPorTipo['retenciones'][] = $cc;
            }
        @endphp
        <div class="totals-wrap">
            <table class="totals-table">
                <tr><td>Subtotal</td><td>${{ number_format($cotizacion->subtotal, 2) }}</td></tr>
                @foreach($conceptosPorTipo['descuentos'] as $cc)
                <tr class="descuento"><td>{{ optional($cc->concepto)->nombre ?? 'Descuento' }}@if($cc->porcentaje>0) ({{ number_format($cc->porcentaje,2) }}%)@endif</td><td>-${{ number_format($cc->valor, 2) }}</td></tr>
                @endforeach
                @foreach($conceptosPorTipo['impuestos'] as $cc)
                <tr><td>{{ optional($cc->concepto)->nombre ?? 'Impuesto' }}@if($cc->porcentaje>0) ({{ number_format($cc->porcentaje,2) }}%)@endif</td><td>${{ number_format($cc->valor, 2) }}</td></tr>
                @endforeach
                @foreach($conceptosPorTipo['retenciones'] as $cc)
                <tr class="retencion"><td>{{ optional($cc->concepto)->nombre ?? 'Retención' }}@if($cc->porcentaje>0) ({{ number_format($cc->porcentaje,2) }}%)@endif</td><td>-${{ number_format($cc->valor, 2) }}</td></tr>
                @endforeach
                @if($cotizacion->viaticos > 0)
                <tr><td>Viáticos</td><td>${{ number_format($cotizacion->viaticos, 2) }}</td></tr>
                @endif
                <tr class="total-final"><td>TOTAL</td><td>${{ number_format($cotizacion->total, 2) }}</td></tr>
            </table>
        </div>

        {{-- OBSERVACIONES --}}
        @if($cotizacion->observacion || $cotizacion->observaciones?->isNotEmpty())
        <div class="section-title">Observaciones</div>
        <div class="obs-box">
            {{ $cotizacion->observacion ?? '' }}
            @foreach($cotizacion->observaciones ?? [] as $obs)
                @if($obs->active && optional($obs->observacion)->texto)
                    {{ "\n" . $obs->observacion->texto }}
                @endif
            @endforeach
        </div>
        @endif

        {{-- CONDICIONES COMERCIALES --}}
        @php $cc = $cotizacion->condicionesComerciales?->first(); @endphp
        @if($cc)
        <div class="section-title">Condiciones comerciales</div>
        <dl class="cond-list">
            @if($cc->tiempo_entrega)<dt>Tiempo de entrega</dt><dd>{{ $cc->tiempo_entrega }}</dd>@endif
            @if($cc->lugar_obra)<dt>Sitio de entrega</dt><dd>{{ $cc->lugar_obra }}</dd>@endif
            @if($cc->duracion_oferta)<dt>Duración de la oferta</dt><dd>{{ $cc->duracion_oferta }}</dd>@endif
            @if($cc->garantia)<dt>Garantía</dt><dd>{{ $cc->garantia }}</dd>@endif
            @if($cc->forma_pago)<dt>Forma de pago</dt><dd>{{ $cc->forma_pago }}</dd>@endif
        </dl>
        @endif

        {{-- FORMULARIO DE RESPUESTA --}}
        <div class="actions">
            <h3>¿Qué desea hacer con esta cotización?</h3>

            <button class="btn-aprobar" onclick="mostrarPanelAprobar()">✔ Aprobar cotización</button>
            <button class="btn-rechazar" onclick="mostrarPanelRechazar()">✘ Rechazar cotización</button>

            {{-- Panel Aprobar --}}
            <div id="panel-aprobar">
                <h4 style="margin:0 0 12px;color:#155724;">Confirmar aprobación</h4>
                <form method="POST" action="{{ url('/cotizacion/' . $cotizacion->token_aprobacion . '/responder') }}" id="form-aprobar">
                    @csrf
                    <input type="hidden" name="accion" value="aprobar">
                    <div class="form-group">
                        <label>Su nombre completo *</label>
                        <input type="text" name="respondido_por" required placeholder="Nombre de quien aprueba" maxlength="200">
                        <div class="error-msg" id="err-nombre-ap">Este campo es obligatorio.</div>
                    </div>
                    <button type="submit" class="btn-aprobar">Confirmar aprobación</button>
                    <button type="button" onclick="ocultarPaneles()" style="margin-left:10px;background:none;border:none;color:#666;cursor:pointer;font-size:14px;">Cancelar</button>
                </form>
            </div>

            {{-- Panel Rechazar --}}
            <div id="panel-rechazar">
                <h4 style="margin:0 0 12px;color:#721c24;">Indicar motivo de rechazo</h4>
                <form method="POST" action="{{ url('/cotizacion/' . $cotizacion->token_aprobacion . '/responder') }}" id="form-rechazar">
                    @csrf
                    <input type="hidden" name="accion" value="rechazar">
                    <div class="form-group">
                        <label>Su nombre completo *</label>
                        <input type="text" name="respondido_por" required placeholder="Nombre de quien rechaza" maxlength="200">
                    </div>
                    <div class="form-group">
                        <label>Motivo del rechazo *</label>
                        <textarea name="motivo_rechazo" required placeholder="Por favor indique el motivo por el cual rechaza esta cotización..." maxlength="1000"></textarea>
                        <div class="error-msg" id="err-motivo">El motivo es obligatorio para rechazar.</div>
                    </div>
                    <button type="submit" class="btn-rechazar">Confirmar rechazo</button>
                    <button type="button" onclick="ocultarPaneles()" style="margin-left:10px;background:none;border:none;color:#666;cursor:pointer;font-size:14px;">Cancelar</button>
                </form>
            </div>
        </div>

    </div>{{-- /card --}}

@endif

</div>{{-- /page --}}

<script>
function mostrarPanelAprobar() {
    document.getElementById('panel-rechazar').style.display = 'none';
    document.getElementById('panel-aprobar').style.display = 'block';
    document.getElementById('panel-aprobar').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function mostrarPanelRechazar() {
    document.getElementById('panel-aprobar').style.display = 'none';
    document.getElementById('panel-rechazar').style.display = 'block';
    document.getElementById('panel-rechazar').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function ocultarPaneles() {
    document.getElementById('panel-aprobar').style.display = 'none';
    document.getElementById('panel-rechazar').style.display = 'none';
}
</script>

</body>
</html>
