@extends('pdf.layouts.base')

@section('title', 'Cotización ' . ($cotizacion->num_documento ?? ''))

@section('content')

<p style="margin-top: 60px; font-size: 10px;">
    De acuerdo a su solicitud presentamos la siguiente oferta:
</p>

@php
    $subtotal = 0;
    $contador = 1;

    $items = $cotizacion->productos->groupBy(fn ($p) =>
        optional($p->cotizacionItem)->id ?? 'sin-item'
    );
@endphp

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

@foreach($items as $itemId => $productosItem)

    {{-- 🔵 ITEM PADRE --}}
    <tr class="item-parent">
        <td colspan="6" style="color:#1e88e5;font-weight:bold;">
            {{ optional($productosItem->first()->cotizacionItem)->nombre ?? 'ITEM GENERAL' }}
        </td>
    </tr>

    @php
        $subitems = $productosItem->groupBy(fn ($p) =>
            optional($p->cotizacionSubItem)->id ?? 'sin-subitem'
        );
    @endphp

    @foreach($subitems as $subitemId => $productosSub)

        {{-- 🟦 SUBITEM --}}
        @if($subitemId !== 'sin-subitem')
        <tr class="item-child">
            <td colspan="6" style="padding-left:10px;font-weight:bold;">
                {{ optional($productosSub->first()->cotizacionSubItem)->nombre }}
            </td>
        </tr>
        @endif

        {{-- 📦 PRODUCTOS --}}
        @foreach($productosSub as $producto)

            @php
                $total = ($producto->cantidad ?? 1) * ($producto->valor_unitario ?? 0);
                $subtotal += $total;
            @endphp

            <tr>
                <td class="center"><strong>{{ $producto->codigo }}</strong></td>
                <td style="padding-left:{{ $subitemId !== 'sin-subitem' ? '20px' : '10px' }}">
                    {{ $producto->nombre }}
                </td>
                <td class="center">{{ $producto->unidad_medida }}</td>
                <td class="center">{{ number_format($producto->cantidad, 0) }}</td>
                <td class="right">${{ number_format($producto->valor_unitario, 2) }}</td>
                <td class="right">${{ number_format($total, 2) }}</td>
            </tr>

        @endforeach

    @endforeach

@endforeach

        <tr class="gray bold">
            <td colspan="5" class="right">SUBTOTAL</td>
            <td class="right">${{ number_format($subtotal, 2) }}</td>
        </tr>

    </tbody>
</table>

{{-- TOTALES --}}
<table width="100%" class="mt-10">
    <tr>
        <td width="60%"></td>
        <td width="40%">
            <table class="border">
                <tr>
                    <td class="gray bold right">SUBTOTAL</td>
                    <td class="right">${{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="gray bold right">IVA 19%</td>
                    <td class="right">${{ number_format($subtotal * 0.19, 2) }}</td>
                </tr>
                <tr class="gray bold">
                    <td class="right">TOTAL</td>
                    <td class="right">${{ number_format($subtotal * 1.19, 2) }}</td>
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
