@extends('pdf.layouts.base')

@section('title', 'Cotización ' . ($cotizacion->num_documento ?? ''))

@section('content')

<p class="mt-10">
    De acuerdo a su solicitud presentamos la siguiente oferta:
</p>

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

        <tr class="gray bold">
            <td colspan="5" class="right">SUBTOTAL</td>
            <td class="right">${{ number_format($subtotal, 2) }}</td>
        </tr>
    </tbody>
</table>

<table class="border mt-10" width="40%" align="right">
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

<table class="border mt-15">
    <tr class="gray">
        <td class="bold">OBSERVACIONES</td>
    </tr>
    <tr>
        <td>
            {!! nl2br(e($cotizacion->observacion ?? 'Sin observaciones')) !!}
        </td>
    </tr>
</table>

@endsection
