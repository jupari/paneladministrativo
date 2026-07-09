@extends('pdf.layouts.base')

@section('title', 'Cotización ' . ($cotizacion->num_documento ?? ''))

@section('content')

<p style="margin-top: 60px; font-size: 10px;">
    De acuerdo a su solicitud presentamos la siguiente oferta:
</p>

{{-- CAPITULACIONES E ITEMS --}}
<table class="border mt-10">
    <thead>
        <tr class="gray center">
            <th width="6%">ITEM</th>
            <th width="46%">CONCEPTO</th>
            <th width="8%">UND</th>
            <th width="8%">CANT</th>
            <th width="16%">VR UNIDAD</th>
            <th width="16%">VR TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cotizacion->items->sortBy('orden') as $indexCap => $capitulacion)
            {{-- Fila de CAPITULACIÓN en azul --}}
            <tr class="item-parent" style="background-color:#e3f2fd;">
                <td colspan="6" style="color:#1e88e5; font-weight:bold; padding:8px;">
                    {{-- CAPITULACIÓN {{ $indexCap + 1 }}. {{ $capitulacion->nombre }} --}}
                    {{ $capitulacion->nombre }}
                </td>
            </tr>

            {{-- Items dentro de la capitulación --}}
            @foreach($capitulacion->subitems->sortBy('orden') as $indexItem => $item)
                @php
                    $cantidadTotal = $item->productos->sum('cantidad');
                    $valorTotal    = $item->productos->sum(fn ($p) => $p->valor_total + $p->listas->sum('subtotal'));
                    $unidadMedida  = $item->productos->first()?->unidad_medida ?? optional($item->unidadMedida)->nombre ?? '—';
                    $vrUnitario    = $cantidadTotal > 0 ? $valorTotal / $cantidadTotal : 0;
                @endphp
                <tr>
                    <td class="center" style="padding-left:10px;"><strong>{{ $indexCap + 1 }}.{{ $indexItem + 1 }}</strong></td>
                    <td>
                        <strong>{{ $item->nombre }}</strong>
                        @if($item->observacion)
                            <br><span style="font-size:9px; color:#666;">{{ $item->observacion }}</span>
                        @endif
                    </td>
                    <td class="center">{{ $unidadMedida }}</td>
                    <td class="center">{{ number_format($cantidadTotal, 2) }}</td>
                    <td class="right">${{ number_format($vrUnitario, 2) }}</td>
                    <td class="right">${{ number_format($valorTotal, 2) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

{{-- TOTALES --}}
@php
    $subtotalDB    = (float) ($cotizacion->subtotal ?? 0);
    $descuentoDB   = (float) ($cotizacion->descuento ?? 0);
    $impuestoDB    = (float) ($cotizacion->total_impuesto ?? 0);
    $viaticosDB    = (float) ($cotizacion->viaticos ?? 0);
    $totalDB       = (float) ($cotizacion->total ?? 0);

    // Clasificar conceptos por tipo para visualización
    $conceptosPorTipo = ['descuentos' => [], 'impuestos' => [], 'retenciones' => []];
    foreach ($cotizacion->conceptos ?? [] as $cc) {
        $tipo = strtoupper(optional($cc->concepto)->tipo ?? '');
        if (\App\Models\Concepto::esDescuento($tipo)) {
            $conceptosPorTipo['descuentos'][] = $cc;
        } elseif (\App\Models\Concepto::esImpuesto($tipo)) {
            $conceptosPorTipo['impuestos'][] = $cc;
        } elseif (\App\Models\Concepto::esRetencion($tipo)) {
            $conceptosPorTipo['retenciones'][] = $cc;
        }
    }
@endphp
<table width="100%" class="mt-10">
    <tr>
        <td width="55%"></td>
        <td width="45%">
            <table class="border">
                <tr>
                    <td class="gray bold right">SUBTOTAL</td>
                    <td class="right" style="white-space:nowrap;">${{ number_format($subtotalDB, 2) }}</td>
                </tr>

                {{-- Descuentos --}}
                @foreach($conceptosPorTipo['descuentos'] as $cc)
                <tr>
                    <td class="bold right" style="color:#b71c1c;">
                        {{ optional($cc->concepto)->nombre ?? 'Descuento' }}
                        @if($cc->porcentaje > 0)({{ number_format($cc->porcentaje, 2) }}%)@endif
                    </td>
                    <td class="right" style="color:#b71c1c;white-space:nowrap;">-${{ number_format($cc->valor, 2) }}</td>
                </tr>
                @endforeach

                {{-- Impuestos --}}
                @foreach($conceptosPorTipo['impuestos'] as $cc)
                <tr>
                    <td class="bold right">
                        {{ optional($cc->concepto)->nombre ?? 'Impuesto' }}
                        @if($cc->porcentaje > 0)({{ number_format($cc->porcentaje, 2) }}%)@endif
                    </td>
                    <td class="right" style="white-space:nowrap;">${{ number_format($cc->valor, 2) }}</td>
                </tr>
                @endforeach

                {{-- Retenciones --}}
                @foreach($conceptosPorTipo['retenciones'] as $cc)
                <tr>
                    <td class="bold right" style="color:#4a148c;">
                        {{ optional($cc->concepto)->nombre ?? 'Retención' }}
                        @if($cc->porcentaje > 0)({{ number_format($cc->porcentaje, 2) }}%)@endif
                    </td>
                    <td class="right" style="color:#4a148c;white-space:nowrap;">-${{ number_format($cc->valor, 2) }}</td>
                </tr>
                @endforeach

                {{-- Viáticos --}}
                @if($viaticosDB > 0)
                <tr>
                    <td class="bold right">VIÁTICOS</td>
                    <td class="right" style="white-space:nowrap;">${{ number_format($viaticosDB, 2) }}</td>
                </tr>
                @endif

                <tr class="gray bold">
                    <td class="right">TOTAL</td>
                    <td class="right" style="white-space:nowrap;">${{ number_format($totalDB, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- OBSERVACIONES --}}
<table class="border mt-15" width="100%" cellspacing="0" cellpadding="0">
    <tr class="gray">
        <td class="bold" style="padding:4px 6px;">OBSERVACIONES</td>
    </tr>

    <tr>
        <td style="
            padding:6px 8px;
            font-size:9px;
            line-height:1.2;
            vertical-align:top;
        ">
            {{-- Observación principal --}}
            {!! nl2br(e($cotizacion->observacion ?? 'Sin observaciones')) !!}
            {{-- Observaciones adicionales --}}
            @if(!empty($cotizacion->observaciones))
                @foreach($cotizacion->observaciones as $obs)
                    <!--<br>-->
                    <p style="margin-top:0px;margin-bottom:0px">{!! nl2br(e($obs->active==1?$obs->observacion->texto:'')) !!}</p>
                @endforeach
            @endif
        </td>
    </tr>
</table>


@endsection
